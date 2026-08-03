<?php
// admin/generate_hash.php
// Alat SEKALI PAKAI untuk membuat hash password admin.
// Cara pakai: buka halaman ini di browser, isi password yang Anda inginkan, lalu klik "Buat Hash".
// Salin hasil hash ke admin/config.php (ganti nilai ADMIN_PASSWORD_HASH).
// Setelah selesai, HAPUS FILE INI dari server (lewat File Manager) agar tidak disalahgunakan orang lain.

$hash = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['password'])) {
    $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Buat Hash Password Admin — Aff Digital</title>
<style>
  body{ font-family: Arial, sans-serif; background:#EDEFF4; color:#12172B; display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; padding:20px;}
  .card{ background:#fff; border-radius:14px; padding:32px; max-width:520px; width:100%; box-shadow:0 6px 24px rgba(18,23,43,0.08); }
  h1{ font-size:20px; margin:0 0 8px; }
  p{ color:#5B6072; font-size:14px; line-height:1.6; }
  input[type=password]{ width:100%; padding:11px 14px; border-radius:8px; border:1px solid #d7dae2; margin:14px 0; font-size:14px; box-sizing:border-box; }
  button{ background:#12172B; color:#fff; border:none; padding:11px 20px; border-radius:8px; font-weight:700; cursor:pointer; }
  .result{ background:#fff7e8; border:1px dashed #d8a94a; padding:14px; border-radius:10px; margin-top:20px; word-break:break-all; font-family:monospace; font-size:13px; }
  .warn{ background:#fdecec; border:1px solid #d9534f; color:#8a2620; padding:12px 14px; border-radius:8px; margin-top:20px; font-size:13px; }
</style>
</head>
<body>
  <div class="card">
    <h1>Buat Hash Password Admin</h1>
    <p>Masukkan password yang ingin Anda pakai untuk login ke halaman admin. Alat ini akan membuat kode hash yang perlu ditempel ke file <code>admin/config.php</code>.</p>
    <form method="POST">
      <input type="password" name="password" placeholder="Ketik password admin baru" required>
      <button type="submit">Buat Hash</button>
    </form>
    <?php if ($hash): ?>
      <p><strong>Hash Anda:</strong></p>
      <div class="result"><?php echo htmlspecialchars($hash, ENT_QUOTES, 'UTF-8'); ?></div>
      <p>Salin teks di atas, buka <code>admin/config.php</code> lewat File Manager, lalu ganti nilai <code>ADMIN_PASSWORD_HASH</code> dengan teks tersebut.</p>
      <div class="warn">Setelah selesai, hapus file <code>admin/generate_hash.php</code> ini dari server agar tidak bisa dipakai orang lain untuk mengganti password Anda.</div>
    <?php endif; ?>
  </div>
</body>
</html>
