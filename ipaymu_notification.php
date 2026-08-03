<?php
// ipaymu_notification.php
// Webhook: iPaymu mengirim notifikasi status pembayaran ke sini secara server-to-server
// lewat POST (bukan JSON, tapi form-urlencoded biasa).
// Daftarkan URL ini sebagai "notifyUrl" — di kode kita, ini sudah otomatis dikirim
// setiap kali checkout.php membuat transaksi iPaymu, jadi TIDAK perlu didaftarkan manual
// di dashboard iPaymu.
//
// CATATAN KEAMANAN: notifikasi iPaymu (berbeda dari Midtrans) tidak menyertakan signature
// untuk diverifikasi. Sebagai lapisan pengaman tambahan, kita hanya menerima notifikasi
// untuk order yang sudah ada di database kita (dicocokkan lewat reference_id) dan yang
// masih berstatus 'pending' — order yang sudah 'paid' tidak akan ditimpa lagi.

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/ipaymu.php';

header('Content-Type: application/json');

$trxId        = $_POST['trx_id'] ?? '';
$referenceId  = $_POST['reference_id'] ?? '';
$status       = $_POST['status'] ?? '';
$statusCode   = $_POST['status_code'] ?? '';
$sid          = $_POST['sid'] ?? '';

if ($referenceId === '') {
    http_response_code(400);
    echo json_encode(['message' => 'Payload tidak valid']);
    exit;
}

$db = get_db();
$stmt = $db->prepare("SELECT * FROM orders WHERE order_code = ? AND payment_gateway = 'ipaymu' LIMIT 1");
$stmt->execute([$referenceId]);
$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    echo json_encode(['message' => 'Order tidak ditemukan']);
    exit;
}

// Jangan timpa order yang sudah final (paid/failed/expired/cancelled).
if ($order['status'] === 'pending') {
    $newStatus = ipaymu_map_status($status);
    $upd = $db->prepare(
        "UPDATE orders SET status = ?, payment_type = 'ipaymu', midtrans_transaction_id = ? WHERE order_code = ?"
    );
    $upd->execute([$newStatus, $trxId, $referenceId]);
}

http_response_code(200);
echo json_encode(['message' => 'OK']);
