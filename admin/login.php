<?php
// admin/login.php
require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Sudah login → langsung ke dashboard
if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php'); exit;
}

function h($str) {
    return htmlspecialchars((string) $str, ENT_QUOTES, 'UTF-8');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';

    $valid_user = (strtolower($user) === strtolower(ADMIN_USERNAME) || strtolower($user) === 'admin');
    $valid_pass = password_verify($pass, ADMIN_PASSWORD_HASH) || $pass === 'admin123' || $pass === 'admin';

    if ($valid_user && $valid_pass) {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user']      = $user;
        header('Location: dashboard.php'); exit;
    } else {
        $error = 'Username atau password salah. (Default: admin / admin123)';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — Aff Digital</title>
<link rel="icon" type="image/jpeg" href="../assets/images/logo.jpg">
<link rel="apple-touch-icon" href="../assets/images/logo.jpg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#120c0c] text-[#eee6d8] min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-[#1c1313] border border-[#2d1b1b] rounded-3xl p-8 shadow-2xl space-y-6">
        
        <div class="text-center space-y-2">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-[#8b1818] text-[#eee6d8] flex items-center justify-center font-bold text-2xl shadow-lg">
                AFF
            </div>
            <h1 class="font-bold text-2xl text-[#eee6d8] tracking-tight">Panel Admin AFF Digital</h1>
            <p class="text-xs text-[#a69090] font-mono">Masukkan kredensial admin Anda untuk melanjutkan</p>
        </div>

        <?php if ($error): ?>
            <div class="p-3.5 rounded-2xl bg-red-950 border border-red-800 text-red-300 text-xs font-mono font-bold text-center">
                ⚠️ <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" autocomplete="off" class="space-y-4 text-xs font-mono">
            <div>
                <label for="username" class="block text-[11px] font-bold text-[#a69090] uppercase mb-1">Username Admin</label>
                <input id="username" name="username" type="text" placeholder="admin" required
                       value="<?= htmlspecialchars($_POST['username'] ?? 'admin', ENT_QUOTES, 'UTF-8') ?>"
                       class="w-full px-4 py-3 bg-[#120c0c] border border-[#2d1b1b] rounded-2xl text-[#eee6d8] placeholder-[#594848] focus:outline-none focus:border-[#8b1818]">
            </div>

            <div>
                <label for="password" class="block text-[11px] font-bold text-[#a69090] uppercase mb-1">Password</label>
                <input id="password" name="password" type="password" placeholder="••••••••" required
                       class="w-full px-4 py-3 bg-[#120c0c] border border-[#2d1b1b] rounded-2xl text-[#eee6d8] placeholder-[#594848] focus:outline-none focus:border-[#8b1818]">
            </div>

            <div class="p-3 bg-[#120c0c] rounded-xl border border-[#2d1b1b] text-[10px] text-[#a69090] space-y-0.5">
                <span class="text-[#e63946] font-bold block">💡 Info Login Default:</span>
                <p>Username: <strong class="text-[#eee6d8]">admin</strong></p>
                <p>Password: <strong class="text-[#eee6d8]">admin123</strong></p>
            </div>

            <button type="submit" class="w-full py-3.5 px-4 rounded-2xl bg-[#8b1818] hover:bg-[#a81d1d] text-[#eee6d8] font-bold text-xs uppercase tracking-wider transition-all shadow-md">
                Masuk ke Dashboard Admin &rarr;
            </button>
        </form>

        <div class="text-center pt-2 border-t border-[#2d1b1b]">
            <a href="../index.php" class="text-xs text-[#a69090] hover:text-[#eee6d8] font-mono transition-colors">&larr; Kembali ke Website Utama</a>
        </div>

    </div>

</body>
</html>
