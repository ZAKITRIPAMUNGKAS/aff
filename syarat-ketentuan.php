<?php
// syarat-ketentuan.php — Aff Digital
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
<title>Syarat & Ketentuan | Aff Digital</title>
<meta name="description" content="Syarat dan ketentuan penggunaan layanan pembuatan website, sistem custom, serta foto & video profesional di Aff Digital.">
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
  .wrap{max-width:var(--maxw);margin:0 auto;padding:32px 16px 64px;}
  @media (min-width: 640px) { .wrap{padding:48px 24px 80px;} }
  .card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:24px 20px;box-shadow:0 10px 30px rgba(18,23,43,0.04);}
  @media (min-width: 640px) { .card{padding:40px 36px;} }
  .badge-tag{display:inline-block;background:rgba(63,169,160,0.12);color:#1f5c57;border:1px solid rgba(63,169,160,0.3);padding:4px 12px;border-radius:999px;font-size:12px;font-weight:600;margin-bottom:16px;text-transform:uppercase;letter-spacing:0.06em;}
  h1{font-size:24px;line-height:1.2;}
  @media (min-width: 640px) { h1{font-size:32px;} }
  p.lead{color:var(--muted);font-size:15px;margin-bottom:24px;border-bottom:1px solid var(--line);padding-bottom:20px;}
  @media (min-width: 640px) { p.lead{font-size:16px;margin-bottom:32px;padding-bottom:24px;} }
  .content-section{margin-bottom:24px;}
  @media (min-width: 640px) { .content-section{margin-bottom:32px;} }
  .content-section h2{font-size:18px;color:var(--ink);border-left:3px solid var(--teal);padding-left:12px;margin-top:24px;}
  @media (min-width: 640px) { .content-section h2{font-size:20px;margin-top:28px;} }
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
    <span class="badge-tag">Kebijakan Layanan</span>
    <h1>Syarat &amp; Ketentuan Penggunaan</h1>
    <p class="lead">Terakhir diperbarui: 3 Agustus 2026. Harap baca syarat dan ketentuan ini secara saksama sebelum memesan layanan pembuatan website, sistem aplikasi, atau foto &amp; video di Aff Digital.</p>

    <div class="content-section">
      <h2>1. Ketentuan Umum</h2>
      <p>Dengan melakukan pemesanan atau menggunakan jasa dari <b>Aff Digital</b>, Anda menyetujui untuk terikat oleh seluruh Syarat &amp; Ketentuan yang berlaku di bawah ini. Ketentuan ini berlaku bagi seluruh klien individu maupun entitas bisnis.</p>
    </div>

    <div class="content-section">
      <h2>2. Ruang Lingkup Layanan</h2>
      <p>Aff Digital menyediakan layanan digital yang meliputi:</p>
      <ul>
        <li>Pembuatan Website Company Profile, Landing Page, dan Toko Online (E-Commerce).</li>
        <li>Pengembangan Sistem Custom (POS/Kasir, Absensi HR, Manajemen Stok &amp; Inventori Gudang).</li>
        <li>Produksi Konten Visual (Foto Produk Studio, Video Promosi, dan Dokumentasi Event).</li>
      </ul>
    </div>

    <div class="content-section">
      <h2>3. Pembayaran &amp; Transaksi</h2>
      <ul>
        <li>Seluruh transaksi diproses menggunakan mata uang <b>Rupiah (IDR)</b> melalui Payment Gateway resmi (<b>Midtrans</b> dan <b>iPaymu</b>).</li>
        <li>Pembayaran dapat dilakukan melalui Transfer Bank (Virtual Account), QRIS, E-Wallet (GoPay, ShopeePay), Kartu Kredit/Debit, serta gerai peritel (Alfamart/Indomaret).</li>
        <li>Untuk proyek custom di luar paket standar, pembayaran dapat dilakukan secara bertahap (Down Payment / Pelunasan) sesuai kesepakatan tertulis.</li>
      </ul>
    </div>

    <div class="content-section">
      <h2>4. Hak Cipta &amp; Kepemilikan Aset</h2>
      <ul>
        <li>Setelah seluruh pembayaran lunas, hak akses penuh dan kepemilikan aset kode/desain website atau file hasil foto &amp; video diserahkan sepenuhnya kepada Klien.</li>
        <li>Aff Digital berhak menampilkan hasil karya yang telah dipublikasikan ke dalam portofolio penyedia jasa, kecuali jika ada kesepakatan kerahasiaan khusus (NDA).</li>
      </ul>
    </div>

    <div class="content-section">
      <h2>5. Tanggung Jawab Klien</h2>
      <p>Klien bertanggung jawab penuh atas keabsahan seluruh materi, materi gambar, teks, dan produk yang diserahkan kepada Aff Digital untuk ditampilkan di dalam website atau media promosi.</p>
    </div>

    <div class="content-section">
      <h2>6. Perubahan Ketentuan</h2>
      <p>Aff Digital berhak sewaktu-waktu memperbarui Syarat &amp; Ketentuan ini demi menyesuaikan dengan regulasi hukum dan kebijakan penyedia layanan pembayaran yang berlaku.</p>
    </div>
  </div>
</div>

<footer>
  <div class="wrap" style="padding:0;">
    &copy; 2026 Aff Digital. Semua Hak Dilindungi. | <a href="refund-policy.php" style="color:var(--teal);">Kebijakan Pengembalian Dana</a> | <a href="faq.php" style="color:var(--teal);">FAQ</a>
  </div>
</footer>

</body>
</html>
