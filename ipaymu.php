<?php
// ipaymu.php
// Fungsi-fungsi untuk komunikasi dengan iPaymu Payment Redirect API v2 lewat cURL langsung
// (tanpa Composer/SDK — cocok untuk shared hosting).
// Referensi resmi: https://ipaymu.com/api-collection

require_once __DIR__ . '/config.php';

function ipaymu_payment_url() {
    return IPAYMU_IS_PRODUCTION
        ? 'https://my.ipaymu.com/api/v2/payment'
        : 'https://sandbox.ipaymu.com/api/v2/payment';
}

// Signature iPaymu: HMAC-SHA256(StringToSign, ApiKey)
// StringToSign = HTTPMETHOD:VA:lowercase(sha256(requestBodyJson)):ApiKey
function ipaymu_generate_signature($method, $requestBodyJson) {
    $bodyHash = strtolower(hash('sha256', $requestBodyJson));
    $stringToSign = strtoupper($method) . ':' . IPAYMU_VA . ':' . $bodyHash . ':' . IPAYMU_API_KEY;
    return hash_hmac('sha256', $stringToSign, IPAYMU_API_KEY);
}

// Membuat transaksi Payment Redirect. $params mengikuti format iPaymu (product[], qty[], price[], dst).
// Mengembalikan array Data (berisi 'Url' & 'SessionID') atau ['error' => '...'].
function ipaymu_create_transaction(array $params) {
    if (IPAYMU_API_KEY === 'GANTI_DENGAN_API_KEY_IPAYMU') {
        return ['error' => 'API Key iPaymu belum diisi di admin/config.php'];
    }

    $params['account'] = IPAYMU_VA;
    $body = json_encode($params);
    $signature = ipaymu_generate_signature('POST', $body);
    $timestamp = date('YmdHis');

    $ch = curl_init(ipaymu_payment_url());
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json',
            'Content-Type: application/json',
            'va: ' . IPAYMU_VA,
            'signature: ' . $signature,
            'timestamp: ' . $timestamp,
        ],
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_TIMEOUT    => 25,
    ]);
    $response = curl_exec($ch);
    $curlErr  = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        return ['error' => 'Tidak bisa menghubungi iPaymu: ' . $curlErr];
    }

    $data = json_decode($response, true);
    if ($httpCode >= 400 || empty($data['Data']['Url'])) {
        $msg = $data['Message'] ?? 'Gagal membuat transaksi pembayaran iPaymu.';
        return ['error' => $msg];
    }
    return $data['Data'];
}

// Menerjemahkan status notifikasi iPaymu menjadi status internal kita.
// iPaymu mengirim: status = 'pending' | 'berhasil' | 'gagal' (status_code 0/1/2)
function ipaymu_map_status($status) {
    switch (strtolower((string) $status)) {
        case 'berhasil':
            return 'paid';
        case 'gagal':
            return 'failed';
        case 'pending':
        default:
            return 'pending';
    }
}
