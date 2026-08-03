<?php
// faq.php — Aff Digital
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
<title>Pertanyaan Umum (FAQ) | Aff Digital</title>
<meta name="description" content="Jawaban atas pertanyaan yang sering diajukan mengenai pembuatan website, sistem custom, foto & video, serta pembayaran di Aff Digital.">
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
  .badge-tag{display:inline-block;background:rgba(63,169,160,0.12);color:#1f5c57;border:1px solid rgba(63,169,160,0.3);padding:4px 12px;border-radius:999px;font-size:12px;font-weight:600;margin-bottom:16px;text-transform:uppercase;letter-spacing:0.06em;}
  h1{font-size:32px;line-height:1.2;}
  p.lead{color:var(--muted);font-size:16px;margin-bottom:32px;border-bottom:1px solid var(--line);padding-bottom:24px;}
  
  .faq-item{border:1px solid var(--line);border-radius:12px;padding:20px 24px;margin-bottom:16px;background:#fff;transition:border-color 0.2s, box-shadow 0.2s;}
  .faq-item:hover{border-color:var(--teal);box-shadow:0 6px 18px rgba(18,23,43,0.05);}
  .faq-item h3{font-size:17.5px;color:var(--ink);margin-bottom:8px;display:flex;align-items:center;gap:10px;}
  .faq-item h3::before{content:"Q.";color:var(--amber);font-weight:700;}
  .faq-item p{color:#4a5062;font-size:15px;margin:0;}
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
    <span class="badge-tag">Pusat Bantuan</span>
    <h1>Pertanyaan Umum (FAQ)</h1>
    <p class="lead">Temukan jawaban atas pertanyaan yang paling sering diajukan calon klien seputar pengerjaan proyek, pembayaran, dan layanan di Aff Digital.</p>

    <div class="faq-item">
      <h3>Berapa lama proses pengerjaan pembuatan website?</h3>
      <p>Rata-rata pengerjaan paket Basic &amp; Pro adalah 3 hingga 7 hari kerja setelah seluruh materi (konten/gambar) lengkap. Untuk sistem custom (POS/HR/Gudang), durasi menyesuaikan kompleksitas alur bisnis.</p>
    </div>

    <div class="faq-item">
      <h3>Metode pembayaran apa saja yang didukung?</h3>
      <p>Pembayaran diproses otomatis &amp; aman via Payment Gateway <b>Midtrans</b> dan <b>iPaymu</b>. Mendukung Transfer Bank (BCA, Mandiri, BRI, BNI), QRIS, E-Wallet (GoPay, ShopeePay), Kartu Kredit/Debit, dan gerai peritel (Alfamart/Indomaret).</p>
    </div>

    <div class="faq-item">
      <h3>Apakah saya mendapatkan garansi setelah website selesai?</h3>
      <p>Ya! Setiap pemesanan website mendapatkan pendampingan &amp; garansi pemeliharaan (30 hingga 60 hari tergantung paket) untuk perbaikan kendala teknis atau perbaikan bug secara gratis.</p>
    </div>

    <div class="faq-item">
      <h3>Bagaimana jika saya butuh sistem custom khusus untuk bisnis saya?</h3>
      <p>Anda dapat berkonsultasi gratis dengan tim kami via form kontak atau WhatsApp. Kami dapat membangun sistem Kasir/POS, Absensi HR, Manajemen Stok Gudang, Portal Sekolah, atau sistem internal khusus sesuai kebutuhan Anda.</p>
    </div>

    <div class="faq-item">
      <h3>Apakah ada biaya langganan bulanan tersembunyi?</h3>
      <p>Tidak ada biaya tersembunyi. Biaya paket sudah mencakup pengerjaan dan setup awal. Untuk perpanjangan domain &amp; hosting tahunan berikutnya akan diinfokan secara transparan.</p>
    </div>
  </div>
</div>

<footer>
  <div class="wrap" style="padding:0;">
    &copy; 2026 Aff Digital. Semua Hak Dilindungi. | <a href="syarat-ketentuan.php" style="color:var(--teal);">Syarat &amp; Ketentuan</a> | <a href="refund-policy.php" style="color:var(--teal);">Kebijakan Pengembalian Dana</a>
  </div>
</footer>

</body>
</html>
