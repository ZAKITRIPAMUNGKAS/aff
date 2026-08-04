<?php
// admin/packages.php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';

$db = get_db();
$msg = '';
$err = '';

// --- Handle actions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'toggle') {
        $id = (int)$_POST['id'];
        $db->prepare("UPDATE packages SET is_active = NOT is_active WHERE id=?")->execute([$id]);
        $msg = 'Status paket diperbarui.';

    } elseif ($action === 'save') {
        $id       = (int)($_POST['id'] ?? 0);
        $category = in_array($_POST['category'] ?? '', ['website','foto_video']) ? $_POST['category'] : 'website';
        $name     = trim(strip_tags($_POST['name']     ?? ''));
        $tagline  = trim(strip_tags($_POST['tagline']  ?? ''));
        $price    = (int)preg_replace('/\D/', '', $_POST['price'] ?? '0');
        $features = trim($_POST['features'] ?? '');
        $sort     = (int)($_POST['sort_order'] ?? 0);
        $active   = isset($_POST['is_active']) ? 1 : 0;

        if (!$name || $price <= 0) {
            $err = 'Nama dan harga wajib diisi.';
        } elseif ($id) {
            $db->prepare("UPDATE packages SET category=?,name=?,tagline=?,price=?,features=?,sort_order=?,is_active=? WHERE id=?")
               ->execute([$category, $name, $tagline, $price, $features, $sort, $active, $id]);
            $msg = "Paket #{$id} berhasil diperbarui.";
        } else {
            $db->prepare("INSERT INTO packages (category,name,tagline,price,features,sort_order,is_active) VALUES (?,?,?,?,?,?,?)")
               ->execute([$category, $name, $tagline, $price, $features, $sort, $active]);
            $msg = 'Paket baru berhasil ditambahkan.';
        }
    }
}

$packages = $db->query("SELECT * FROM packages ORDER BY category, sort_order, id")->fetchAll();
$editId   = (int)($_GET['edit'] ?? 0);
$editPkg  = null;
if ($editId) {
    foreach ($packages as $p) { if ($p['id'] === $editId) { $editPkg = $p; break; } }
}
$addNew = isset($_GET['new']);

function h3($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Paket Layanan — Admin Aff Digital</title>
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
.main { margin-left:var(--sidebar-w); flex:1; display:flex; flex-direction:column; }
.topbar { padding:20px 28px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; background:var(--sidebar); position:sticky;top:0;z-index:50; }
.topbar h1 { font-size:18px; font-weight:700; }
.add-btn { background:var(--teal); color:#fff; text-decoration:none; padding:9px 18px; border-radius:8px; font-size:13.5px; font-weight:600; transition:opacity 0.2s; }
.add-btn:hover { opacity:0.85; }
.content { padding:28px; }
.alert { padding:12px 16px; border-radius:10px; font-size:13.5px; margin-bottom:20px; }
.alert-ok  { background:rgba(52,211,153,0.1); border:1px solid rgba(52,211,153,0.3); color:#34d399; }
.alert-err { background:rgba(248,81,73,0.1); border:1px solid rgba(248,81,73,0.3); color:#f85149; }
.grid { display:grid; grid-template-columns:1fr 380px; gap:24px; align-items:start; }
/* TABLE */
.table-wrap { background:var(--surface); border:1px solid var(--border); border-radius:14px; overflow:auto; }
table { width:100%; border-collapse:collapse; min-width:600px; }
th { padding:12px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:var(--muted); text-align:left; border-bottom:1px solid var(--border); }
td { padding:13px 16px; font-size:13.5px; border-bottom:1px solid rgba(255,255,255,0.04); vertical-align:middle; }
tr:last-child td { border-bottom:none; }
tr:hover td { background:rgba(255,255,255,0.02); }
.cat-badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
.cat-website   { background:rgba(99,102,241,0.15); color:#818cf8; }
.cat-foto_video{ background:rgba(251,191,36,0.15); color:#fbbf24; }
.active-dot { display:inline-block; width:8px; height:8px; border-radius:50%; margin-right:6px; }
.dot-on  { background:#34d399; }
.dot-off { background:#6b7280; }
.action-btns { display:flex; gap:6px; }
.btn-edit   { background:var(--teal-glow); color:var(--teal); border:1px solid rgba(63,169,160,0.3); padding:5px 12px; border-radius:6px; font-size:12px; font-weight:600; text-decoration:none; transition:all 0.2s; }
.btn-edit:hover { background:var(--teal); color:#fff; }
.btn-toggle { background:rgba(255,255,255,0.05); color:var(--muted); border:1px solid var(--border); padding:5px 12px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; font-family:'Inter',sans-serif; transition:all 0.2s; }
.btn-toggle:hover { border-color:var(--muted); color:var(--ink); }
/* FORM PANEL */
.form-panel { background:var(--surface); border:1px solid var(--border); border-radius:14px; padding:24px; position:sticky; top:80px; }
.form-panel h2 { font-size:15px; font-weight:700; margin-bottom:20px; padding-bottom:14px; border-bottom:1px solid var(--border); }
.field { margin-bottom:16px; }
.field label { display:block; font-size:12px; font-weight:600; color:var(--muted); margin-bottom:7px; text-transform:uppercase; letter-spacing:0.4px; }
.field input, .field select, .field textarea {
  width:100%; background:var(--surface2); border:1px solid var(--border); color:var(--ink);
  padding:10px 13px; border-radius:8px; font-family:'Inter',sans-serif; font-size:14px;
  outline:none; transition:border-color 0.2s;
}
.field input:focus, .field select:focus, .field textarea:focus { border-color:var(--teal); }
.field textarea { min-height:100px; resize:vertical; }
.field select option { background:var(--surface2); }
.field-row { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
.check-label { display:flex; align-items:center; gap:8px; font-size:14px; color:var(--ink); cursor:pointer; }
.check-label input { width:auto; accent-color:var(--teal); }
.save-btn { width:100%; background:linear-gradient(135deg,var(--teal),#2d8a82); color:#fff; border:none; padding:12px; border-radius:8px; font-family:'Inter',sans-serif; font-size:14px; font-weight:700; cursor:pointer; margin-top:8px; transition:opacity 0.2s; }
.save-btn:hover { opacity:0.9; }
.cancel-btn { display:block; text-align:center; margin-top:10px; font-size:13px; color:var(--muted); text-decoration:none; }
.cancel-btn:hover { color:var(--ink); }
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
    <a class="nav-link" href="orders.php"><span class="icon">🧾</span> Pesanan</a>
    <a class="nav-link active" href="packages.php"><span class="icon">📦</span> Paket Layanan</a>
    <a class="nav-link" href="portfolios.php"><span class="icon">🖼️</span> Portofolio</a>
    <div class="nav-label">Website</div>
    <a class="nav-link" href="../index.php" target="_blank"><span class="icon">🌐</span> Lihat Website</a>
  </nav>
  <div class="sidebar-footer">
    <div class="user-box">
      <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['admin_user'] ?? 'A', 0, 1)); ?></div>
      <div><div class="user-name"><?php echo h3($_SESSION['admin_user'] ?? 'Admin'); ?></div><div class="user-role">Administrator</div></div>
    </div>
    <a class="logout-btn" href="../logout.php">Logout →</a>
  </div>
</aside>

<div class="main">
  <div class="topbar">
    <h1>📦 Paket Layanan</h1>
    <a class="add-btn" href="packages.php?new">+ Tambah Paket</a>
  </div>
  <div class="content">

    <?php if ($msg): ?><div class="alert alert-ok">✅ <?php echo h3($msg); ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-err">⚠️ <?php echo h3($err); ?></div><?php endif; ?>

    <div class="grid">
      <!-- TABLE -->
      <div class="table-wrap">
        <table>
          <thead><tr><th>#</th><th>Kategori</th><th>Nama</th><th>Harga</th><th>Status</th><th>Aksi</th></tr></thead>
          <tbody>
            <?php foreach ($packages as $p): ?>
            <tr>
              <td style="color:var(--muted)"><?php echo $p['id']; ?></td>
              <td><span class="cat-badge cat-<?php echo h3($p['category']); ?>"><?php echo $p['category'] === 'website' ? 'Website' : 'Foto & Video'; ?></span></td>
              <td>
                <div style="font-weight:600"><?php echo h3($p['name']); ?></div>
                <div style="font-size:12px;color:var(--muted)"><?php echo h3($p['tagline']); ?></div>
              </td>
              <td style="font-weight:700">Rp<?php echo number_format((float)$p['price'],0,',','.'); ?></td>
              <td>
                <span class="active-dot <?php echo $p['is_active'] ? 'dot-on' : 'dot-off'; ?>"></span>
                <?php echo $p['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
              </td>
              <td>
                <div class="action-btns">
                  <a class="btn-edit" href="packages.php?edit=<?php echo $p['id']; ?>">✏ Edit</a>
                  <form method="POST" style="display:inline">
                    <input type="hidden" name="action" value="toggle">
                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                    <button class="btn-toggle" type="submit"><?php echo $p['is_active'] ? '⊘ Nonaktifkan' : '✓ Aktifkan'; ?></button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- FORM PANEL -->
      <?php if ($editPkg || $addNew): ?>
      <div class="form-panel">
        <h2><?php echo $editPkg ? '✏ Edit Paket #' . $editPkg['id'] : '➕ Tambah Paket Baru'; ?></h2>
        <form method="POST">
          <input type="hidden" name="action" value="save">
          <input type="hidden" name="id" value="<?php echo $editPkg ? $editPkg['id'] : '0'; ?>">
          <div class="field">
            <label>Kategori</label>
            <select name="category">
              <option value="website"     <?php if(($editPkg['category']??'website')==='website')     echo 'selected';?>>Website</option>
              <option value="foto_video"  <?php if(($editPkg['category']??'')==='foto_video')         echo 'selected';?>>Foto & Video</option>
            </select>
          </div>
          <div class="field-row">
            <div class="field">
              <label>Nama Paket</label>
              <input type="text" name="name" required maxlength="100" value="<?php echo h3($editPkg['name']??''); ?>" placeholder="Basic, Pro, Custom...">
            </div>
            <div class="field">
              <label>Sort Order</label>
              <input type="number" name="sort_order" min="0" max="99" value="<?php echo (int)($editPkg['sort_order']??0); ?>">
            </div>
          </div>
          <div class="field">
            <label>Tagline</label>
            <input type="text" name="tagline" maxlength="160" value="<?php echo h3($editPkg['tagline']??''); ?>" placeholder="Deskripsi singkat...">
          </div>
          <div class="field">
            <label>Harga (Rp)</label>
            <input type="number" name="price" required min="0" step="1000" value="<?php echo (int)($editPkg['price']??0); ?>" placeholder="1500000">
          </div>
          <div class="field">
            <label>Fitur (satu per baris)</label>
            <textarea name="features" placeholder="Fitur 1&#10;Fitur 2&#10;Fitur 3"><?php echo h3($editPkg['features']??''); ?></textarea>
          </div>
          <div class="field">
            <label class="check-label">
              <input type="checkbox" name="is_active" value="1" <?php if(($editPkg['is_active']??1)) echo 'checked';?>>
              Paket Aktif (tampil di website)
            </label>
          </div>
          <button class="save-btn" type="submit">💾 Simpan Paket</button>
        </form>
        <a class="cancel-btn" href="packages.php">← Batal</a>
      </div>
      <?php else: ?>
      <div class="form-panel" style="text-align:center;padding:40px">
        <div style="font-size:40px;margin-bottom:12px">📦</div>
        <div style="font-weight:600;margin-bottom:8px">Pilih paket untuk diedit</div>
        <div style="font-size:13px;color:var(--muted)">Klik tombol Edit pada tabel di sebelah kiri, atau tambah paket baru.</div>
        <a class="add-btn" href="packages.php?new" style="display:inline-block;margin-top:20px;text-decoration:none;">+ Tambah Paket Baru</a>
      </div>
      <?php endif; ?>
    </div>

  </div>
</div>
</body>
</html>
