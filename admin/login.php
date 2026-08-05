<?php
// admin/login.php
require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Sudah login → langsung ke dashboard
if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php'); exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';

    if ($user === ADMIN_USERNAME && password_verify($pass, ADMIN_PASSWORD_HASH)) {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user']      = $user;
        header('Location: dashboard.php'); exit;
    } else {
        $error = 'Username atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — Aff Digital</title>
<!-- Favicons -->
<link rel="icon" type="image/jpeg" href="../assets/images/logo.jpg">
<link rel="apple-touch-icon" href="../assets/images/logo.jpg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --bg: #0d1117;
    --surface: #161b22;
    --surface2: #21262d;
    --teal: #3fa9a0;
    --teal-glow: rgba(63,169,160,0.25);
    --ink: #e6edf3;
    --muted: #8b949e;
    --border: rgba(255,255,255,0.08);
    --err: #f85149;
  }
  body {
    font-family: 'Inter', sans-serif;
    background: var(--bg);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    position: relative;
    overflow: hidden;
  }
  body::before {
    content: '';
    position: absolute;
    width: 600px; height: 600px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(63,169,160,0.12) 0%, transparent 70%);
    top: -200px; left: -200px;
    pointer-events: none;
  }
  body::after {
    content: '';
    position: absolute;
    width: 400px; height: 400px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(63,169,160,0.08) 0%, transparent 70%);
    bottom: -100px; right: -100px;
    pointer-events: none;
  }
  .card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 48px 40px;
    width: 100%;
    max-width: 420px;
    position: relative;
    backdrop-filter: blur(20px);
    box-shadow: 0 32px 64px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.05);
    animation: fadeUp 0.5s cubic-bezier(0.16,1,0.3,1) both;
  }
  @keyframes fadeUp {
    from { opacity:0; transform:translateY(24px); }
    to   { opacity:1; transform:translateY(0); }
  }
  .logo {
    text-align: center;
    margin-bottom: 32px;
  }
  .logo-icon {
    width: 56px; height: 56px;
    background: linear-gradient(135deg, var(--teal), #2d8a82);
    border-radius: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    margin-bottom: 14px;
    box-shadow: 0 8px 24px var(--teal-glow);
  }
  .logo h1 { font-size: 22px; font-weight: 800; color: var(--ink); letter-spacing: -0.5px; }
  .logo p  { font-size: 13px; color: var(--muted); margin-top: 4px; }
  .field { margin-bottom: 18px; }
  .field label { display: block; font-size: 13px; font-weight: 600; color: var(--muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
  .field input {
    width: 100%;
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 13px 16px;
    font-family: 'Inter', sans-serif;
    font-size: 15px;
    color: var(--ink);
    transition: border-color 0.2s, box-shadow 0.2s;
    outline: none;
  }
  .field input:focus {
    border-color: var(--teal);
    box-shadow: 0 0 0 3px var(--teal-glow);
  }
  .field input::placeholder { color: var(--muted); }
  .btn {
    width: 100%;
    background: linear-gradient(135deg, var(--teal), #2d8a82);
    color: #fff;
    border: none;
    padding: 14px;
    border-radius: 10px;
    font-family: 'Inter', sans-serif;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    margin-top: 8px;
    transition: transform 0.2s, box-shadow 0.2s, opacity 0.2s;
    position: relative;
    overflow: hidden;
  }
  .btn::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(60deg,transparent,rgba(255,255,255,0.2),transparent);
    transform: translateX(-100%);
    transition: transform 0.5s ease;
  }
  .btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px var(--teal-glow); }
  .btn:hover::after { transform: translateX(100%); }
  .error {
    background: rgba(248,81,73,0.12);
    border: 1px solid rgba(248,81,73,0.3);
    color: var(--err);
    padding: 12px 16px;
    border-radius: 10px;
    font-size: 13.5px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .back { display: block; text-align: center; margin-top: 24px; font-size: 13px; color: var(--muted); text-decoration: none; }
  .back:hover { color: var(--teal); }
</style>
</head>
<body>
<div class="card">
  <div class="logo">
    <img src="../assets/images/logo.jpg" alt="Aff Digital" style="height:48px; width:auto; border-radius:12px; object-fit:cover; margin-bottom:8px;">
    <h1>Aff Digital</h1>
    <p>Panel Admin</p>
  </div>

  <?php if ($error): ?>
    <div class="error">⚠️ <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
  <?php endif; ?>

  <form method="POST" autocomplete="off">
    <div class="field">
      <label for="username">Username</label>
      <input id="username" name="username" type="text" placeholder="admin" required
             value="<?php echo htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
             autocomplete="username">
    </div>
    <div class="field">
      <label for="password">Password</label>
      <input id="password" name="password" type="password" placeholder="••••••••" required
             autocomplete="current-password">
    </div>
    <button class="btn" type="submit">Masuk ke Dashboard</button>
  </form>

  <a class="back" href="../index.php">← Kembali ke website</a>
</div>
</body>
</html>
