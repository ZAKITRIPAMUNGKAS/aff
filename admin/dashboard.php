<?php
// admin/dashboard.php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';

$db = get_db();

// --- Stats ---
$stats = [];
$r = $db->query("SELECT COUNT(*) FROM orders"); $stats['total'] = (int)$r->fetchColumn();
$r = $db->query("SELECT COUNT(*) FROM orders WHERE status='paid'"); $stats['paid'] = (int)$r->fetchColumn();
$r = $db->query("SELECT COUNT(*) FROM orders WHERE status='pending'"); $stats['pending'] = (int)$r->fetchColumn();
$r = $db->query("SELECT COALESCE(SUM(amount),0) FROM orders WHERE status='paid' AND MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())");
$stats['revenue_month'] = (float)$r->fetchColumn();
$r = $db->query("SELECT COALESCE(SUM(amount),0) FROM orders WHERE status='paid' AND DATE(created_at)=CURDATE()");
$stats['revenue_today'] = (float)$r->fetchColumn();

// --- Revenue 7 days ---
$chart = $db->query("
    SELECT DATE(created_at) as day, COALESCE(SUM(amount),0) as total
    FROM orders WHERE status='paid' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at) ORDER BY day ASC
")->fetchAll();
$chartMap = [];
foreach ($chart as $row) $chartMap[$row['day']] = (float)$row['total'];
$chartDays = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-{$i} day"));
    $chartDays[] = ['date' => $d, 'label' => date('d/m', strtotime($d)), 'val' => $chartMap[$d] ?? 0];
}
$chartMax = max(array_column($chartDays, 'val')) ?: 1;

// --- Recent orders ---
$recent = $db->query("
    SELECT o.*, p.name as package_name, p.category as package_category
    FROM orders o JOIN packages p ON p.id = o.package_id
    ORDER BY o.created_at DESC LIMIT 15
")->fetchAll();

function badge($status) {
    $map = [
        'paid'      => ['#d1fae5','#065f46','Lunas'],
        'pending'   => ['#fef3c7','#92400e','Pending'],
        'failed'    => ['#fee2e2','#991b1b','Gagal'],
        'expired'   => ['#f3f4f6','#374151','Kedaluwarsa'],
        'cancelled' => ['#f3f4f6','#374151','Dibatalkan'],
    ];
    $s = $map[$status] ?? ['#f3f4f6','#374151', ucfirst($status)];
    return "<span style='background:{$s[0]};color:{$s[1]};padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;'>{$s[2]}</span>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard — Admin Aff Digital</title>
<!-- Favicons -->
<link rel="icon" type="image/jpeg" href="../assets/images/logo.jpg">
<link rel="shortcut icon" type="image/jpeg" href="../favicon.ico">
<link rel="apple-touch-icon" href="../assets/images/logo.jpg">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
:root {
  --bg:#0d1117; --sidebar:#161b22; --surface:#1c2128; --surface2:#21262d;
  --teal:#3fa9a0; --teal-glow:rgba(63,169,160,0.2);
  --ink:#e6edf3; --muted:#8b949e; --border:rgba(255,255,255,0.07);
  --sidebar-w:240px;
}
body { font-family:'Inter',sans-serif; background:var(--bg); color:var(--ink); display:flex; min-height:100vh; }

/* SIDEBAR */
.sidebar {
  width:var(--sidebar-w); background:var(--sidebar); border-right:1px solid var(--border);
  display:flex; flex-direction:column; position:fixed; top:0; left:0; bottom:0; z-index:100;
}
.sidebar-brand {
  padding:24px 20px; border-bottom:1px solid var(--border);
  display:flex; align-items:center; gap:12px;
}
.brand-icon { width:36px;height:36px;background:linear-gradient(135deg,var(--teal),#2d8a82);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0; }
.brand-name { font-size:15px; font-weight:800; letter-spacing:-0.3px; }
.brand-sub  { font-size:11px; color:var(--muted); margin-top:1px; }
.sidebar-nav { padding:16px 12px; flex:1; }
.nav-label { font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--muted);padding:0 8px;margin:16px 0 6px; }
.nav-link {
  display:flex; align-items:center; gap:10px; padding:10px 12px; border-radius:8px;
  color:var(--muted); text-decoration:none; font-size:14px; font-weight:500;
  transition:all 0.2s; margin-bottom:2px;
}
.nav-link:hover { background:var(--surface2); color:var(--ink); }
.nav-link.active { background:var(--teal-glow); color:var(--teal); }
.nav-link .icon { width:18px; text-align:center; font-size:16px; }
.sidebar-footer { padding:16px; border-top:1px solid var(--border); }
.user-box { display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;background:var(--surface2); }
.user-avatar { width:32px;height:32px;background:linear-gradient(135deg,var(--teal),#2d8a82);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:#fff;flex-shrink:0; }
.user-name { font-size:13px;font-weight:600; }
.user-role { font-size:11px;color:var(--muted); }
.logout-btn { display:block;margin-top:10px;text-align:center;padding:8px;background:rgba(248,81,73,0.1);border:1px solid rgba(248,81,73,0.2);color:#f85149;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;transition:all 0.2s; }
.logout-btn:hover { background:rgba(248,81,73,0.2); }

/* MAIN */
.main { margin-left:var(--sidebar-w); flex:1; display:flex; flex-direction:column; }
.topbar { padding:20px 28px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; background:var(--sidebar); position:sticky;top:0;z-index:50; }
.topbar h1 { font-size:18px; font-weight:700; }
.topbar-right { font-size:13px; color:var(--muted); }
.content { padding:28px; }

/* RESPONSIVE */
@media (max-width: 1023px) {
  body { flex-direction: column; overflow-x: hidden; max-width: 100vw; }
  .sidebar { position: static; width: 100%; border-right: none; border-bottom: 1px solid var(--border); box-sizing: border-box; }
  .sidebar-nav { display: flex; flex-wrap: wrap; gap: 4px; padding: 12px; }
  .nav-link { margin-bottom: 0; padding: 8px 12px; }
  .nav-label { width: 100%; margin: 8px 0 4px; }
  .main { margin-left: 0; }
  .topbar { padding: 16px; flex-direction: column; align-items: flex-start; gap: 8px; }
  .content { padding: 16px; }
}

/* STATS */
.stats-grid { display:grid; grid-template-columns:repeat(2, 1fr); gap:16px; margin-bottom:28px; }
@media (min-width: 640px) { .stats-grid { grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); } }
.stat-card {
  background:var(--surface); border:1px solid var(--border); border-radius:14px;
  padding:16px; position:relative; overflow:hidden;
  transition:transform 0.2s, box-shadow 0.2s;
}
@media (min-width: 640px) { .stat-card { padding:20px; } }
.stat-card:hover { transform:translateY(-3px); box-shadow:0 12px 32px rgba(0,0,0,0.3); }
.stat-card::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; background:var(--accent,var(--teal)); }
.stat-label { font-size:12px; color:var(--muted); font-weight:600; text-transform:uppercase; letter-spacing:0.5px; }
.stat-value { font-size:28px; font-weight:800; margin:8px 0 4px; letter-spacing:-1px; }
.stat-sub   { font-size:12px; color:var(--muted); }
.stat-icon  { position:absolute; right:16px; top:16px; font-size:28px; opacity:0.15; }

/* CHART */
.chart-card { background:var(--surface); border:1px solid var(--border); border-radius:14px; padding:24px; margin-bottom:28px; }
.chart-title { font-size:15px; font-weight:700; margin-bottom:20px; }
.chart-bars { display:flex; align-items:flex-end; gap:8px; height:120px; }
.chart-col { flex:1; display:flex; flex-direction:column; align-items:center; gap:6px; height:100%; }
.bar-wrap { flex:1; width:100%; display:flex; align-items:flex-end; }
.bar {
  width:100%; border-radius:6px 6px 0 0;
  background:linear-gradient(to top, var(--teal), rgba(63,169,160,0.5));
  min-height:4px;
  transition:height 0.6s cubic-bezier(0.16,1,0.3,1);
  position:relative;
}
.bar:hover::after { content:attr(data-val); position:absolute; top:-28px;left:50%;transform:translateX(-50%);background:var(--surface2);color:var(--ink);padding:3px 8px;border-radius:6px;font-size:11px;white-space:nowrap; }
.bar-label { font-size:11px; color:var(--muted); }

/* TABLE */
.table-card { background:var(--surface); border:1px solid var(--border); border-radius:14px; overflow:hidden; }
.table-header { padding:18px 20px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
.table-title { font-size:15px; font-weight:700; }
.view-all { font-size:13px; color:var(--teal); text-decoration:none; font-weight:600; }
.view-all:hover { text-decoration:underline; }
table { width:100%; border-collapse:collapse; }
th { padding:12px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:var(--muted); text-align:left; border-bottom:1px solid var(--border); }
td { padding:13px 16px; font-size:13.5px; border-bottom:1px solid rgba(255,255,255,0.04); }
tr:last-child td { border-bottom:none; }
tr:hover td { background:rgba(255,255,255,0.02); }
.order-code { font-family:monospace; font-size:12px; color:var(--muted); }
.amount { font-weight:700; font-size:13px; }
</style>
</head>
<body>
<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon">⚡</div>
    <div>
      <div class="brand-name">Aff Digital</div>
      <div class="brand-sub">Admin Panel</div>
    </div>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-label">Menu</div>
    <a class="nav-link active" href="dashboard.php"><span class="icon">📊</span> Dashboard</a>
    <a class="nav-link" href="orders.php"><span class="icon">🧾</span> Pesanan</a>
    <a class="nav-link" href="packages.php"><span class="icon">📦</span> Paket Layanan</a>
    <a class="nav-link" href="portfolios.php"><span class="icon">🖼️</span> Portofolio</a>
    <div class="nav-label">Website</div>
    <a class="nav-link" href="../index.php" target="_blank"><span class="icon">🌐</span> Lihat Website</a>
  </nav>
  <div class="sidebar-footer">
    <div class="user-box">
      <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['admin_user'] ?? 'A', 0, 1)); ?></div>
      <div>
        <div class="user-name"><?php echo htmlspecialchars($_SESSION['admin_user'] ?? 'Admin', ENT_QUOTES); ?></div>
        <div class="user-role">Administrator</div>
      </div>
    </div>
    <a class="logout-btn" href="../logout.php">Logout →</a>
  </div>
</aside>

<!-- MAIN -->
<div class="main">
  <div class="topbar">
    <h1>Dashboard</h1>
    <span class="topbar-right"><?php echo date('l, d F Y'); ?></span>
  </div>
  <div class="content">

    <!-- STATS -->
    <div class="stats-grid">
      <div class="stat-card" style="--accent:#3fa9a0">
        <span class="stat-icon">🧾</span>
        <div class="stat-label">Total Pesanan</div>
        <div class="stat-value"><?php echo $stats['total']; ?></div>
        <div class="stat-sub">Semua status</div>
      </div>
      <div class="stat-card" style="--accent:#34d399">
        <span class="stat-icon">✅</span>
        <div class="stat-label">Pesanan Lunas</div>
        <div class="stat-value"><?php echo $stats['paid']; ?></div>
        <div class="stat-sub">Status paid</div>
      </div>
      <div class="stat-card" style="--accent:#fbbf24">
        <span class="stat-icon">⏳</span>
        <div class="stat-label">Menunggu Bayar</div>
        <div class="stat-value"><?php echo $stats['pending']; ?></div>
        <div class="stat-sub">Status pending</div>
      </div>
      <div class="stat-card" style="--accent:#818cf8">
        <span class="stat-icon">💰</span>
        <div class="stat-label">Pendapatan Bulan Ini</div>
        <div class="stat-value" style="font-size:20px;">Rp<?php echo number_format($stats['revenue_month'],0,',','.'); ?></div>
        <div class="stat-sub">Hari ini: Rp<?php echo number_format($stats['revenue_today'],0,',','.'); ?></div>
      </div>
    </div>

    <!-- CHART -->
    <div class="chart-card">
      <div class="chart-title">💹 Revenue 7 Hari Terakhir</div>
      <div class="chart-bars">
        <?php foreach ($chartDays as $day):
          $pct = $chartMax > 0 ? ($day['val'] / $chartMax * 100) : 0;
          $valFmt = 'Rp'.number_format($day['val'],0,',','.');
        ?>
        <div class="chart-col">
          <div class="bar-wrap">
            <div class="bar" style="height:<?php echo max(4, $pct); ?>%" data-val="<?php echo $valFmt; ?>"></div>
          </div>
          <div class="bar-label"><?php echo $day['label']; ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- RECENT ORDERS TABLE -->
    <div class="table-card">
      <div class="table-header">
        <span class="table-title">🧾 Pesanan Terbaru</span>
        <a class="view-all" href="orders.php">Lihat semua →</a>
      </div>
      <table>
        <thead>
          <tr>
            <th>Kode Pesanan</th>
            <th>Pelanggan</th>
            <th>Paket</th>
            <th>Gateway</th>
            <th>Total</th>
            <th>Status</th>
            <th>Tanggal</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recent as $o): ?>
          <tr>
            <td><span class="order-code"><?php echo htmlspecialchars($o['order_code'], ENT_QUOTES); ?></span></td>
            <td>
              <div style="font-weight:600"><?php echo htmlspecialchars($o['customer_name'], ENT_QUOTES); ?></div>
              <div style="font-size:12px;color:var(--muted)"><?php echo htmlspecialchars($o['customer_email'], ENT_QUOTES); ?></div>
            </td>
            <td><?php echo htmlspecialchars($o['package_name'], ENT_QUOTES); ?></td>
            <td style="text-transform:capitalize"><?php echo htmlspecialchars($o['payment_gateway'], ENT_QUOTES); ?></td>
            <td class="amount">Rp<?php echo number_format((float)$o['amount'],0,',','.'); ?></td>
            <td><?php echo badge($o['status']); ?></td>
            <td style="color:var(--muted);font-size:12px"><?php echo date('d M Y H:i', strtotime($o['created_at'])); ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($recent)): ?>
          <tr><td colspan="7" style="text-align:center;padding:32px;color:var(--muted)">Belum ada pesanan.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>
</body>
</html>
