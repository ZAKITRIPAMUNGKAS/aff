<?php
// refund-policy.php — Aff Digital
require_once __DIR__ . '/db.php';

function h($str) {
    return htmlspecialchars((string) $str, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kebijakan Pengembalian Dana (Refund Policy) | Aff Digital</title>
<meta name="description" content="Kebijakan pengembalian dana dan pembatalan pesanan di Aff Digital. Keterbukaan dan kepuasan pelanggan adalah prioritas kami.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
  :root{ --ink:#12172B; --paper:#EDEFF4; --card:#FFFFFF; --amber:#E8A33D; --teal:#3FA9A0; --muted:#5B6072; --line:rgba(18,23,43,0.12); --radius:14px; --maxw:920px; }
  *{box-sizing:border-box;}
  body{margin:0;background:var(--paper);color:var(--ink);font-family:'Inter',sans-serif;line-height:1.7;}
  h1,h2,h3{font-family:'Space Grotesk',sans-serif;margin:0 0 12px 0;}
  a{color:var(--teal);text-decoration:none;}
  a:hover{text-decoration:underline;}
  header{position:sticky;top:0;z-index:50;background:rgba(237,239,244,0.88);backdrop-filter:blur(12px);border-bottom:1px solid rgba(18,23,43,0.08);}
  .nav{max-width:1160px;margin:0 auto;padding:16px 28px;display:flex;align-items:center;justify-content:space-between;}
  .brand{display:flex;align-items:center;gap:10px;font-weight:700;font-size:19px;color:var(--ink);}
  .back-link{font-size:14px;color:var(--muted);font-weight:500;}
  .wrap{max-width:var(--maxw);margin:0 auto;padding:48px 24px 80px;}
  .card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:40px 36px;box-shadow:0 10px 30px rgba(18,23,43,0.04);}
  .badge-tag{display:inline-block;background:rgba(232,163,61,0.15);color:#a8621c;border:1px solid rgba(232,163,61,0.3);padding:4px 12px;border-radius:999px;font-size:12px;font-weight:600;margin-bottom:16px;text-transform:uppercase;letter-spacing:0.06em;}
  h1{font-size:32px;line-height:1.2;}
  p.lead{color:var(--muted);font-size:16px;margin-bottom:32px;border-bottom:1px solid var(--line);padding-bottom:24px;}
  .content-section{margin-bottom:32px;}
  .content-section h2{font-size:20px;color:var(--ink);border-left:3px solid var(--amber);padding-left:12px;margin-top:28px;}
  .content-section p, .content-section li{color:#3a4052;font-size:15px;}
  .content-section ul{padding-left:20px;margin:12px 0;}
  .content-section li{margin-bottom:8px;}
  footer{background:#0d1120;color:rgba(255,255,255,0.6);padding:32px 0;text-align:center;font-size:13.5px;margin-top:40px;}
</style>
</head>
<body>

<header>
  <div class="nav">
    <a href="index.php" class="brand">
      <svg viewBox="0 0 40 40" fill="none" width="28" height="28">
        <circle cx="20" cy="20" r="19" stroke="#12172B" stroke-width="2"/>
        <path d="M20 6 L20 20 L30 26" stroke="#E8A33D" stroke-width="2.4" stroke-linecap="round"/>
      </svg>
      Aff Digital
    </a>
    <a href="index.php" class="back-link">&larr; Kembali ke Beranda</a>
  </div>
</header>

<div class="wrap">
  <div class="card">
    <span class="badge-tag">Jaminan Kualitas</span>
    <h1>Kebijakan Pengembalian Dana (Refund Policy)</h1>
    <p class="lead">Terakhir diperbarui: 3 Agustus 2026. Di Aff Digital, kepuasan dan kepercayaan pelanggan adalah komitmen utama kami.</p>

    <div class="content-section">
      <h2>1. Kriteria Pengajuan Refund</h2>
      <p>Pengembalian dana (Refund) dapat diajukan oleh Klien apabila memenuhi salah satu kondisi berikut:</p>
      <ul>
        <li><b>Kegagalan Sistem / Pembayaran Ganda:</b> Terjadi kesalahan transaksi pembayaran ganda (double charge) atau error sistem pembayaran.</li>
        <li><b>Pembatalan Sebelum Pengerjaan Dimulai:</b> Pembatalan dilakukan dalam kurun waktu 24 jam setelah pembayaran diterima dan tim teknis belum memulai pengerjaan desain/pemrograman.</li>
        <li><b>Keterlambatan Signifikan dari Penyedia Jasa:</b> Pekerjaan tidak dapat diselesaikan melebihi batas waktu yang telah disepakati bersama tanpa pemberitahuan/alasan yang sah.</li>
      </ul>
    </div>

    <div class="content-section">
      <h2>2. Kondisi Tidak Berlaku Refund</h2>
      <p>Pengembalian dana tidak dapat diberikan pada kondisi berikut:</p>
      <ul>
        <li>Pengerjaan pengerjaan proyek telah berjalan lebih dari 50% atau telah disetujui desain awalnya oleh Klien.</li>
        <li>Klien membatalkan proyek secara sepihak karena alasan internal yang tidak berkaitan dengan kualitas hasil kerja Aff Digital.</li>
        <li>Produksi foto atau video yang telah selesai dilaksanakan di lokasi (shoot day complete).</li>
      </ul>
    </div>

    <div class="content-section">
      <h2>3. Prosedur Pengajuan Refund</h2>
      <ol style="padding-left:20px;color:#3a4052;font-size:15px;">
        <li style="margin-bottom:8px;">Hubungi Layanan Pelanggan kami melalui email ke <b>hello@affdigital.id</b> atau WhatsApp resmi dengan menyertakan Kode Pesanan (contoh: <code>AFF-2026xxxx-XXXXXX</code>).</li>
        <li style="margin-bottom:8px;">Lampirkan bukti transaksi pembayaran dan alasan pengajuan refund.</li>
        <li style="margin-bottom:8px;">Tim finance kami akan melakukan verifikasi data dalam waktu maksimal 1x24 jam kerja.</li>
      </ol>
    </div>

    <div class="content-section">
      <h2>4. Waktu &amp; Metode Pengembalian Dana</h2>
      <p>Setelah pengajuan refund disetujui:</p>
      <ul>
        <li><b>Midtrans / iPaymu (E-Wallet &amp; QRIS):</b> Dana akan dikembalikan otomatis ke saldo E-Wallet/Akun asal dalam 1-3 hari kerja.</li>
        <li><b>Transfer Bank:</b> Dana akan ditransfer kembali ke rekening bank Klien dalam 1-2 hari kerja.</li>
        <li>Pengembalian dana dilakukan 100% tanpa potongan biaya administrasi internal Aff Digital.</li>
      </ul>
    </div>
  </div>
</div>

<footer>
  <div class="wrap" style="padding:0;">
    &copy; 2026 Aff Digital. Semua Hak Dilindungi. | <a href="syarat-ketentuan.php" style="color:var(--teal);">Syarat &amp; Ketentuan</a> | <a href="faq.php" style="color:var(--teal);">FAQ</a>
  </div>
</footer>

</body>
</html>
