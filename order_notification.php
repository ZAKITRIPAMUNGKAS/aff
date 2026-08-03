<?php
// order_notification.php
// Webhook: Midtrans mengirim notifikasi status pembayaran ke sini secara server-to-server.
// Daftarkan URL ini di dashboard Midtrans > Settings > Configuration > Payment Notification URL,
// contoh: https://domainanda.com/order_notification.php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/midtrans.php';

header('Content-Type: application/json');

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);

if (!is_array($payload) || empty($payload['order_id'])) {
    http_response_code(400);
    echo json_encode(['message' => 'Payload tidak valid']);
    exit;
}

$orderId      = $payload['order_id'];
$statusCode   = $payload['status_code'] ?? '';
$grossAmount  = $payload['gross_amount'] ?? '';
$signatureKey = $payload['signature_key'] ?? '';

if (!midtrans_verify_signature($orderId, $statusCode, $grossAmount, $signatureKey)) {
    http_response_code(403);
    echo json_encode(['message' => 'Signature tidak valid']);
    exit;
}

$db = get_db();
$stmt = $db->prepare("SELECT * FROM orders WHERE order_code = ? LIMIT 1");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    echo json_encode(['message' => 'Order tidak ditemukan']);
    exit;
}

$transactionStatus = $payload['transaction_status'] ?? '';
$fraudStatus        = $payload['fraud_status'] ?? '';
$newStatus           = midtrans_map_status($transactionStatus, $fraudStatus);

$upd = $db->prepare(
    "UPDATE orders
     SET status = ?, payment_type = ?, midtrans_transaction_id = ?, raw_notification = ?
     WHERE order_code = ?"
);
$upd->execute([
    $newStatus,
    $payload['payment_type'] ?? null,
    $payload['transaction_id'] ?? null,
    $raw,
    $orderId,
]);

http_response_code(200);
echo json_encode(['message' => 'OK']);
