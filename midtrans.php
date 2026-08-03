<?php
// midtrans.php
// Fungsi-fungsi untuk komunikasi dengan Midtrans Snap API lewat cURL langsung,
// jadi tidak perlu Composer/SDK — cocok untuk shared hosting.

require_once __DIR__ . '/config.php';

function midtrans_snap_url() {
    return MIDTRANS_IS_PRODUCTION
        ? 'https://app.midtrans.com/snap/v1/transactions'
        : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
}

function midtrans_status_url($orderId) {
    $base = MIDTRANS_IS_PRODUCTION
        ? 'https://api.midtrans.com'
        : 'https://api.sandbox.midtrans.com';
    return $base . '/v2/' . rawurlencode($orderId) . '/status';
}

// Membuat transaksi Snap baru. $params mengikuti format Midtrans Snap API.
// Mengembalikan array hasil (berisi 'token' & 'redirect_url') atau ['error' => '...'].
function midtrans_create_transaction(array $params) {
    if (MIDTRANS_SERVER_KEY === 'GANTI_DENGAN_SERVER_KEY_MIDTRANS') {
        return ['error' => 'Server Key Midtrans belum diisi di admin/config.php'];
    }

    $auth = base64_encode(MIDTRANS_SERVER_KEY . ':');
    $ch = curl_init(midtrans_snap_url());
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Basic ' . $auth,
        ],
        CURLOPT_POSTFIELDS => json_encode($params),
        CURLOPT_TIMEOUT    => 25,
    ]);
    $response = curl_exec($ch);
    $curlErr  = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        return ['error' => 'Tidak bisa menghubungi Midtrans: ' . $curlErr];
    }

    $data = json_decode($response, true);
    if ($httpCode >= 400 || !isset($data['token'])) {
        $msg = $data['error_messages'][0] ?? 'Gagal membuat transaksi pembayaran.';
        return ['error' => $msg];
    }
    return $data;
}

// Cek status transaksi langsung ke Midtrans (dipakai sebagai fallback jika notifikasi belum masuk).
function midtrans_get_status($orderId) {
    $auth = base64_encode(MIDTRANS_SERVER_KEY . ':');
    $ch = curl_init(midtrans_status_url($orderId));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json',
            'Authorization: Basic ' . $auth,
        ],
        CURLOPT_TIMEOUT => 20,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response ? json_decode($response, true) : null;
}

// Verifikasi signature_key yang dikirim Midtrans lewat notifikasi (webhook),
// supaya tidak ada yang bisa memalsukan status pembayaran.
function midtrans_verify_signature($orderId, $statusCode, $grossAmount, $signatureKey) {
    $expected = hash('sha512', $orderId . $statusCode . $grossAmount . MIDTRANS_SERVER_KEY);
    return hash_equals($expected, (string) $signatureKey);
}

// Menerjemahkan status Midtrans menjadi status internal kita.
function midtrans_map_status($transactionStatus, $fraudStatus) {
    switch ($transactionStatus) {
        case 'capture':
            return ($fraudStatus === 'accept') ? 'paid' : 'pending';
        case 'settlement':
            return 'paid';
        case 'pending':
            return 'pending';
        case 'deny':
        case 'cancel':
            return 'failed';
        case 'expire':
            return 'expired';
        default:
            return 'pending';
    }
}
