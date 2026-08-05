<?php
// admin/orders.php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';

$db = get_db();

// --- Handle status update ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['update_status'])) {
    $newStatus = $_POST['new_status'] ?? '';
    $orderId   = (int)($_POST['order_id'] ?? 0);
    $allowed   = ['pending','paid','failed','expired','cancelled'];
    if ($orderId && in_array($newStatus, $allowed)) {
        $db->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$newStatus, $orderId]);
    }
    header('Location: orders.php?' . http_build_query(array_intersect_key($_GET, ['status'=>1,'gateway'=>1,'q'=>1,'page'=>1])));
    exit;
}

// --- Filters ---
$filterStatus  = $_GET['status']  ?? '';
$filterGateway = $_GET['gateway'] ?? '';
$search        = trim($_GET['q']  ?? '');
$page          = max(1, (int)($_GET['page'] ?? 1));
$perPage       = 20;

$where  = [];
$params = [];
if ($filterStatus)  { $where[] = 'o.status = ?';           $params[] = $filterStatus; }
if ($filterGateway) { $where[] = 'o.payment_gateway = ?';  $params[] = $filterGateway; }
if ($search) {
    $where[] = '(o.order_code LIKE ? OR o.customer_name LIKE ? OR o.customer_email LIKE ?)';
    $params  = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
}
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$totalRows = (int)$db->prepare("SELECT COUNT(*) FROM orders o $whereSQL")->execute($params) ? 0 : 0;
$countStmt = $db->prepare("SELECT COUNT(*) FROM orders o $whereSQL");
$countStmt->execute($params);
$totalRows = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalRows / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$stmt = $db->prepare("
    SELECT o.*, p.name as package_name
    FROM orders o JOIN packages p ON p.id = o.package_id
    $whereSQL ORDER BY o.created_at DESC LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$orders = $stmt->fetchAll();

function h2($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function badge2($status) {
    $map = [
        'paid'      => ['#d1fae5','#065f46','Lunas'],
        'pending'   => ['#fef3c7','#92400e','Pending'],
        'failed'    => ['#fee2e2','#991b1b','Gagal'],
        'expired'   => ['#f3f4f6','#374151','Kedaluwarsa'],
        'cancelled' => ['#f3f4f6','#374151','Dibatalkan'],
    ];
    $s = $map[$status] ?? ['#f3f4f6','#374151', ucfirst($status)];
    return "<span style='background:{$s[0]};color:{$s[1]};padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;display:inline-block'>{$s[2]}</span>";
}

$queryBase = ['status' => $filterStatus, 'gateway' => $filterGateway, 'q' => $search];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Pesanan — Admin Aff Digital</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
:root { --bg:#0d1117; --sidebar:#161b22; --surface:#1c2128; --surface2:#21262d; --teal:#3fa9a0; --teal-glow:rgba(63,169,160,0.2); --ink:#e6edf3; --muted:#8b949e; --border:rgba(255,255,255,0.07); --sidebar-w:240px; }
body { font-family:'Inter',sans-serif; background:var(--bg); color:var(--ink); display:flex; min-height:100vh; }
.sidebar { width:var(--sidebar-w); background:var(--sidebar); border-right:1px solid var(--border); display:flex; flex-direction:column; position:fixed; top:0; left:0; bottom:0; z-index:100; }
.sidebar-brand { padding:24px 20px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:12px; }
.brand-icon { width:36px;height:36px;background:linear-gradient(135deg,var(--teal),#2d8a82);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0; }
.brand-name { font-size:15px; font-weight:800; }
.brand-sub { font-size:11px; color:var(--muted); margin-top:1px; }
.sidebar-nav { padding:16px 12px; flex:1; }
.nav-label { font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--muted);padding:0 8px;margin:16px 0 6px; }
.nav-link { display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;color:var(--muted);text-decoration:none;font-size:14px;font-weight:500;transition:all 0.2s;margin-bottom:2px; }
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
.main { margin-left:var(--sidebar-w); flex:1; display:flex; flex-direction:column; min-width:0; }
.topbar { padding:20px 28px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; background:var(--sidebar); position:sticky;top:0;z-index:50; }
.topbar h1 { font-size:18px; font-weight:700; }
.topbar-right { font-size:13px; color:var(--muted); }
.content { padding:28px; }

/* RESPONSIVE */
@media (max-width: 1023px) {
  body { flex-direction: column; }
  .sidebar { position: static; width: 100%; border-right: none; border-bottom: 1px solid var(--border); }
  .sidebar-nav { display: flex; flex-wrap: wrap; gap: 4px; padding: 12px; }
  .nav-link { margin-bottom: 0; padding: 8px 12px; }
  .nav-label { width: 100%; margin: 8px 0 4px; }
  .main { margin-left: 0; }
  .topbar { padding: 16px; flex-direction: column; align-items: flex-start; gap: 8px; }
  .content { padding: 16px; overflow-x: auto; }
  .table-card { overflow-x: auto; }
}
.filters { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px; align-items:center; }
.filters input, .filters select { background:var(--surface); border:1px solid var(--border); color:var(--ink); padding:9px 14px; border-radius:8px; font-size:13.5px; font-family:'Inter',sans-serif; outline:none; transition:border-color 0.2s; }
.filters input:focus, .filters select:focus { border-color:var(--teal); }
.filters select option { background:var(--surface2); }
.filters button { background:var(--teal); color:#fff; border:none; padding:9px 18px; border-radius:8px; font-family:'Inter',sans-serif; font-size:13.5px; font-weight:600; cursor:pointer; }
.filters .clear { background:transparent; border:1px solid var(--border); color:var(--muted); }
.count { font-size:13px; color:var(--muted); margin-bottom:16px; }
.table-wrap { background:var(--surface); border:1px solid var(--border); border-radius:14px; overflow:auto; }
table { width:100%; border-collapse:collapse; min-width:900px; }
th { padding:12px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:var(--muted); text-align:left; border-bottom:1px solid var(--border); white-space:nowrap; }
td { padding:13px 16px; font-size:13.5px; border-bottom:1px solid rgba(255,255,255,0.04); vertical-align:middle; }
tr:last-child td { border-bottom:none; }
tr:hover td { background:rgba(255,255,255,0.02); }
.order-code { font-family:monospace; font-size:12px; color:var(--muted); }
.amount { font-weight:700; }
.status-form { display:inline-flex; gap:6px; align-items:center; }
.status-form select { background:var(--surface2); border:1px solid var(--border); color:var(--ink); padding:5px 8px; border-radius:6px; font-size:12px; cursor:pointer; }
.status-form button { background:var(--teal); color:#fff; border:none; padding:5px 10px; border-radius:6px; font-size:12px; cursor:pointer; font-weight:600; }
.pagination { display:flex; gap:6px; margin-top:20px; flex-wrap:wrap; align-items:center; }
.pagination a, .pagination span { padding:7px 13px; border-radius:8px; font-size:13px; font-weight:600; text-decoration:none; }
.pagination a { background:var(--surface); border:1px solid var(--border); color:var(--muted); transition:all 0.2s; }
.pagination a:hover { border-color:var(--teal); color:var(--teal); }
.pagination span { background:var(--teal); color:#fff; }
.empty { text-align:center; padding:48px; color:var(--muted); }
</style>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon">⚡</div>
    <div><div class="brand-name">Aff Digital</div><div class="brand-sub">Admin Panel</div></div>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-label">Menu</div>
    <a class="nav-link" href="dashboard.php"><span class="icon">📊</span> Dashboard</a>
    <a class="nav-link active" href="orders.php"><span class="icon">🧾</span> Pesanan</a>
    <a class="nav-link" href="packages.php"><span class="icon">📦</span> Paket Layanan</a>
    <a class="nav-link" href="portfolios.php"><span class="icon">🖼️</span> Portofolio</a>
    <div class="nav-label">Website</div>
    <a class="nav-link" href="../index.php" target="_blank"><span class="icon">🌐</span> Lihat Website</a>
  </nav>
  <div class="sidebar-footer">
    <div class="user-box">
      <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['admin_user'] ?? 'A', 0, 1)); ?></div>
      <div><div class="user-name"><?php echo h2($_SESSION['admin_user'] ?? 'Admin'); ?></div><div class="user-role">Administrator</div></div>
    </div>
    <a class="logout-btn" href="../logout.php">Logout →</a>
  </div>
</aside>

<div class="main">
  <div class="topbar"><h1>🧾 Manajemen Pesanan</h1></div>
  <div class="content">

    <!-- FILTERS -->
    <form class="filters" method="GET">
      <input type="text" name="q" placeholder="🔍 Cari nama, email, kode..." value="<?php echo h2($search); ?>">
      <select name="status">
        <option value="">Semua Status</option>
        <?php foreach (['pending','paid','failed','expired','cancelled'] as $s): ?>
          <option value="<?php echo $s; ?>" <?php if($filterStatus===$s) echo 'selected'; ?>><?php echo ucfirst($s); ?></option>
        <?php endforeach; ?>
      </select>
      <select name="gateway">
        <option value="">Semua Gateway</option>
        <option value="midtrans" <?php if($filterGateway==='midtrans') echo 'selected'; ?>>Midtrans</option>
        <option value="ipaymu"   <?php if($filterGateway==='ipaymu')   echo 'selected'; ?>>iPaymu</option>
      </select>
      <button type="submit">Filter</button>
      <?php if ($filterStatus || $filterGateway || $search): ?>
        <a href="orders.php" class="filters clear" style="display:inline-block;padding:9px 14px;border-radius:8px;text-decoration:none;border:1px solid var(--border);color:var(--muted);font-size:13.5px;">✕ Reset</a>
      <?php endif; ?>
    </form>

    <div class="count">Menampilkan <?php echo count($orders); ?> dari <?php echo $totalRows; ?> pesanan</div>

    <!-- TABLE -->
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Kode</th><th>Pelanggan</th><th>Paket</th><th>Gateway</th>
            <th>Total</th><th>Status</th><th>Tanggal</th><th>Ubah Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
          <tr>
            <td><span class="order-code"><?php echo h2($o['order_code']); ?></span></td>
            <td>
              <div style="font-weight:600"><?php echo h2($o['customer_name']); ?></div>
              <div style="font-size:12px;color:var(--muted)"><?php echo h2($o['customer_email']); ?></div>
              <div style="font-size:12px;color:var(--muted)"><?php echo h2($o['customer_phone']); ?></div>
            </td>
            <td><?php echo h2($o['package_name']); ?></td>
            <td style="text-transform:capitalize"><?php echo h2($o['payment_gateway']); ?></td>
            <td class="amount">Rp<?php echo number_format((float)$o['amount'],0,',','.'); ?></td>
            <td><?php echo badge2($o['status']); ?></td>
            <td style="color:var(--muted);font-size:12px"><?php echo date('d M Y', strtotime($o['created_at'])); ?><br><?php echo date('H:i', strtotime($o['created_at'])); ?></td>
            <td>
              <form class="status-form" method="POST">
                <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                <select name="new_status">
                  <?php foreach (['pending','paid','failed','expired','cancelled'] as $s): ?>
                    <option value="<?php echo $s; ?>" <?php if($o['status']===$s) echo 'selected'; ?>><?php echo ucfirst($s); ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" name="update_status" value="1">✓</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($orders)): ?>
          <tr><td colspan="8" class="empty">Tidak ada pesanan ditemukan.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- PAGINATION -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <?php for ($i = 1; $i <= $totalPages; $i++):
        $qStr = http_build_query(array_merge($queryBase, ['page' => $i]));
      ?>
        <?php if ($i === $page): ?>
          <span><?php echo $i; ?></span>
        <?php else: ?>
          <a href="orders.php?<?php echo $qStr; ?>"><?php echo $i; ?></a>
        <?php endif; ?>
      <?php endfor; ?>
    </div>
    <?php endif; ?>

  </div>
</div>
</body>
</html>
