<?php
// order_status.php
// Halaman "terima kasih" setelah pembayaran. Menampilkan status pesanan,
// dan sekaligus mengecek ulang ke Midtrans kalau notifikasi webhook belum masuk.

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/midtrans.php';

function h($str) {
    return htmlspecialchars((string) $str, ENT_QUOTES, 'UTF-8');
}

$orderCode = isset($_GET['order']) ? trim((string) $_GET['order']) : '';
$order = null;

if ($orderCode !== '') {
    $db = get_db();
    $stmt = $db->prepare(
        "SELECT o.*, p.name AS package_name, p.category AS package_category
         FROM orders o JOIN packages p ON p.id = o.package_id
         WHERE o.order_code = ? LIMIT 1"
    );
    $stmt->execute([$orderCode]);
    $order = $stmt->fetch();

    // Kalau masih 'pending' dan pakai Midtrans, coba cek langsung ke Midtrans
    // (fallback jika webhook belum sampai). iPaymu tidak dicek ulang di sini karena
    // statusnya mengandalkan notifikasi dari ipaymu_notification.php.
    if ($order && $order['status'] === 'pending' && $order['payment_gateway'] === 'midtrans') {
        $check = midtrans_get_status($orderCode);
        if (is_array($check) && !empty($check['transaction_status'])) {
            $mapped = midtrans_map_status($check['transaction_status'], $check['fraud_status'] ?? '');
            if ($mapped !== $order['status']) {
                $upd = $db->prepare("UPDATE orders SET status = ?, payment_type = ? WHERE order_code = ?");
                $upd->execute([$mapped, $check['payment_type'] ?? null, $orderCode]);
                $order['status'] = $mapped;
            }
        }
    }
}

$labels = [
    'pending'   => ['Menunggu Pembayaran', '#7a5410', '#fff7e8'],
    'paid'      => ['Pembayaran Berhasil', '#1c6b40', '#e7f7ee'],
    'failed'    => ['Pembayaran Gagal', '#8a2620', '#fdecec'],
    'expired'   => ['Pembayaran Kedaluwarsa', '#8a2620', '#fdecec'],
    'cancelled' => ['Pesanan Dibatalkan', '#8a2620', '#fdecec'],
];
$statusInfo = $order ? ($labels[$order['status']] ?? ['Status Tidak Diketahui', '#5B6072', '#EDEFF4']) : null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Status Pesanan | Aff Digital</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root{ --ink:#12172B; --paper:#EDEFF4; --card:#FFFFFF; --muted:#5B6072; --line:rgba(18,23,43,0.12); --radius:14px; }
  *{box-sizing:border-box;}
  body{margin:0;background:var(--paper);color:var(--ink);font-family:'Inter',sans-serif;line-height:1.6;}
  h1,h2{font-family:'Space Grotesk',sans-serif;margin:0;}
  a{color:var(--ink);}
  .wrap{max-width:560px;margin:0 auto;padding:64px 24px;text-align:center;}
  .card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:40px 32px;}
  .badge{display:inline-block;padding:8px 18px;border-radius:999px;font-weight:700;font-size:14px;margin-bottom:20px;}
  .row{display:flex;justify-content:space-between;font-size:14.5px;padding:10px 0;border-bottom:1px solid var(--line);text-align:left;}
  .row:last-child{border-bottom:none;}
  .row span:first-child{color:var(--muted);}
  .back{display:inline-block;margin-top:26px;font-size:14.5px;text-decoration:underline;}
</style>
</head>
<body>
<div class="wrap">
  <div class="card">
    <?php if (!$order): ?>
      <h1 style="font-size:22px;margin-bottom:10px;">Pesanan tidak ditemukan</h1>
      <p style="color:var(--muted);">Kode pesanan tidak valid atau sudah tidak ada.</p>
    <?php else: ?>
      <?php if (isset($_GET['demo'])): ?>
        <div style="background:#fff7e8;border:1px dashed #d8a94a;color:#7a5410;padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:13.5px;text-align:left;">
          <strong>ℹ Mode Simulasi Pengujian:</strong> Pesanan berhasil dibuat di database. Untuk transaksi riil, pastikan API Key gateway di <code>config.php</code> sudah aktif.
        </div>
      <?php endif; ?>
      <div class="badge" style="color:<?php echo $statusInfo[1]; ?>;background:<?php echo $statusInfo[2]; ?>;"><?php echo h($statusInfo[0]); ?></div>
      <h1 style="font-size:22px;margin-bottom:24px;">
        <?php echo $order['status'] === 'paid' ? 'Terima kasih, ' . h($order['customer_name']) . '!' : 'Detail Pesanan Anda'; ?>
      </h1>
      <div class="row"><span>Kode Pesanan</span><span><?php echo h($order['order_code']); ?></span></div>
      <div class="row"><span>Paket</span><span><?php echo h($order['package_name']); ?> (<?php echo $order['package_category'] === 'website' ? 'Website' : 'Foto & Video'; ?>)</span></div>
      <div class="row"><span>Metode Gateway</span><span><?php echo $order['payment_gateway'] === 'ipaymu' ? 'iPaymu' : 'Midtrans'; ?></span></div>
      <div class="row"><span>Total</span><span><?php echo h(format_rupiah($order['amount'])); ?></span></div>
      <?php if ($order['payment_type']): ?>
        <div class="row"><span>Metode Bayar</span><span><?php echo h(strtoupper($order['payment_type'])); ?></span></div>
      <?php endif; ?>
      <?php if ($order['status'] === 'paid'): ?>
        <p style="margin-top:20px;color:var(--muted);font-size:14.5px;">Tim kami akan menghubungi Anda lewat WhatsApp/email untuk memulai proses pengerjaan.</p>
      <?php elseif ($order['status'] === 'pending'): ?>
        <p style="margin-top:20px;color:var(--muted);font-size:14.5px;">Selesaikan pembayaran Anda. Halaman ini akan otomatis memperbarui status setelah pembayaran diterima.</p>
      <?php endif; ?>
    <?php endif; ?>
    <a class="back" href="index.php">&larr; Kembali ke beranda</a>
  </div>
</div>
</body>
</html>
