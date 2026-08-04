<?php
// admin/portfolios.php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';

$db = get_db();
$msg = '';
$err = '';

$uploadDir = __DIR__ . '/../assets/uploads/portfolios/';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}

// Static predefined categories
$staticCategories = [
    'Website',
    'Sistem',
    'Foto Produk',
    'Video Promosi',
    'Desain Grafis',
    'Lainnya'
];

// --- Handle Form Submissions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $db->prepare("UPDATE portfolios SET is_active = NOT is_active WHERE id=?")->execute([$id]);
            $msg = 'Status portofolio berhasil diperbarui.';
        }

    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $stmt = $db->prepare("SELECT media_url, images_json FROM portfolios WHERE id=?");
            $stmt->execute([$id]);
            $item = $stmt->fetch();
            if ($item) {
                // Hapus file fisik di folder uploads
                $filesToDelete = [];
                if ($item['media_url'] && strpos($item['media_url'], 'assets/uploads/portfolios/') === 0) {
                    $filesToDelete[] = $item['media_url'];
                }
                if (!empty($item['images_json'])) {
                    $imgs = json_decode($item['images_json'], true);
                    if (is_array($imgs)) {
                        foreach ($imgs as $imgUrl) {
                            if (strpos($imgUrl, 'assets/uploads/portfolios/') === 0) {
                                $filesToDelete[] = $imgUrl;
                            }
                        }
                    }
                }
                foreach (array_unique($filesToDelete) as $relPath) {
                    $fullPath = __DIR__ . '/../' . $relPath;
                    if (file_exists($fullPath)) {
                        @unlink($fullPath);
                    }
                }
                $db->prepare("DELETE FROM portfolios WHERE id=?")->execute([$id]);
                $msg = "Portofolio #{$id} berhasil dihapus.";
            }
        }

    } elseif ($action === 'save') {
        $id            = (int)($_POST['id'] ?? 0);
        $title         = trim(strip_tags($_POST['title'] ?? ''));
        $categoryLabel = in_array($_POST['category_label'] ?? '', $staticCategories) ? $_POST['category_label'] : 'Lainnya';
        $description   = trim(strip_tags($_POST['description'] ?? ''));
        $mediaType     = in_array($_POST['media_type'] ?? '', ['image', 'video']) ? $_POST['media_type'] : 'image';
        $mediaUrl      = trim($_POST['media_url_existing'] ?? '');
        $imagesJson    = trim($_POST['images_json_existing'] ?? '[]');
        $sortOrder     = (int)($_POST['sort_order'] ?? 0);
        $isActive      = isset($_POST['is_active']) ? 1 : 0;

        $existingImages = json_decode($imagesJson, true);
        if (!is_array($existingImages)) $existingImages = [];

        // Handling Multi-File Uploads (Max 10 files)
        $uploadedFiles = [];
        if (isset($_FILES['media_files']) && !empty($_FILES['media_files']['name'][0])) {
            $totalFiles = count($_FILES['media_files']['name']);
            if ($totalFiles > 10) {
                $err = 'Maksimal upload 10 file sekaligus.';
            } else {
                $allowedImg = ['jpg', 'jpeg', 'png', 'webp'];
                $allowedVid = ['mp4', 'webm', 'ogg', 'mov'];
                $allowed    = array_merge($allowedImg, $allowedVid);

                for ($i = 0; $i < min($totalFiles, 10); $i++) {
                    if ($_FILES['media_files']['error'][$i] === UPLOAD_ERR_OK) {
                        $fileTmp  = $_FILES['media_files']['tmp_name'][$i];
                        $fileName = $_FILES['media_files']['name'][$i];
                        $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                        if (in_array($fileExt, $allowed)) {
                            if (in_array($fileExt, $allowedVid)) {
                                $mediaType = 'video';
                            }
                            $safeName = time() . '_' . $i . '_' . preg_replace('/[^a-zA-Z0-9\._-]/', '_', $fileName);
                            $targetFile = $uploadDir . $safeName;

                            if (move_uploaded_file($fileTmp, $targetFile)) {
                                $uploadedFiles[] = 'assets/uploads/portfolios/' . $safeName;
                            }
                        }
                    }
                }
            }
        }

        if ($uploadedFiles) {
            $existingImages = $uploadedFiles;
            $mediaUrl       = $uploadedFiles[0]; // Set file pertama sebagai cover/media utama
        }

        if (!$title) {
            $err = 'Judul portofolio wajib diisi.';
        } elseif (!$mediaUrl && empty($existingImages)) {
            $err = 'File gambar/video wajib diunggah.';
        }

        if (!$err) {
            $finalImagesJson = json_encode(array_slice(array_values($existingImages), 0, 10));

            if ($id) {
                $stmt = $db->prepare("
                    UPDATE portfolios
                    SET title=?, category_label=?, description=?, media_type=?, media_url=?, images_json=?, sort_order=?, is_active=?
                    WHERE id=?
                ");
                $stmt->execute([$title, $categoryLabel, $description, $mediaType, $mediaUrl, $finalImagesJson, $sortOrder, $isActive, $id]);
                $msg = "Portofolio #{$id} berhasil diperbarui.";
            } else {
                $stmt = $db->prepare("
                    INSERT INTO portfolios (title, category_label, description, media_type, media_url, images_json, sort_order, is_active)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$title, $categoryLabel, $description, $mediaType, $mediaUrl, $finalImagesJson, $sortOrder, $isActive]);
                $msg = 'Portofolio baru berhasil ditambahkan.';
            }
        }
    }
}

// Fetch all portfolios ORDER BY id DESC (terbaru paling atas)
$portfolios = [];
try {
    $portfolios = $db->query("SELECT * FROM portfolios ORDER BY id DESC")->fetchAll();
} catch (PDOException $e) {
    $portfolios = [];
}

// Edit Item Logic
$editId   = (int)($_GET['edit'] ?? 0);
$editItem = null;
if ($editId) {
    foreach ($portfolios as $p) {
        if ($p['id'] === $editId) {
            $editItem = $p;
            break;
        }
    }
}
$addNew = isset($_GET['new']);

function h4($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Portofolio — Admin Aff Digital</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
:root {
  --bg:#0d1117; --sidebar:#161b22; --surface:#1c2128; --surface2:#21262d;
  --teal:#3fa9a0; --teal-glow:rgba(63,169,160,0.2); --amber:#e8a33d;
  --ink:#e6edf3; --muted:#8b949e; --border:rgba(255,255,255,0.07); --sidebar-w:240px;
}
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
.grid { display:grid; grid-template-columns:1fr 420px; gap:24px; align-items:start; }
@media (max-width:1150px) { .grid { grid-template-columns:1fr; } }
.table-wrap { background:var(--surface); border:1px solid var(--border); border-radius:14px; overflow:auto; }
table { width:100%; border-collapse:collapse; min-width:650px; }
th { padding:12px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:var(--muted); text-align:left; border-bottom:1px solid var(--border); }
td { padding:13px 16px; font-size:13.5px; border-bottom:1px solid rgba(255,255,255,0.04); vertical-align:middle; }
tr:last-child td { border-bottom:none; }
tr:hover td { background:rgba(255,255,255,0.02); }
.thumb-preview { width:56px; height:56px; border-radius:8px; object-fit:cover; background:#0f1623; border:1px solid var(--border); display:block; }
.media-badge { display:inline-block; padding:3px 8px; border-radius:6px; font-size:11px; font-weight:700; text-transform:uppercase; }
.badge-image { background:rgba(59,130,246,0.15); color:#60a5fa; }
.badge-video { background:rgba(236,72,153,0.15); color:#f472b6; }
.active-dot { display:inline-block; width:8px; height:8px; border-radius:50%; margin-right:6px; }
.dot-on  { background:#34d399; }
.dot-off { background:#6b7280; }
.action-btns { display:flex; gap:6px; flex-wrap:wrap; }
.btn-edit   { background:var(--teal-glow); color:var(--teal); border:1px solid rgba(63,169,160,0.3); padding:5px 10px; border-radius:6px; font-size:12px; font-weight:600; text-decoration:none; transition:all 0.2s; }
.btn-edit:hover { background:var(--teal); color:#fff; }
.btn-toggle { background:rgba(255,255,255,0.05); color:var(--muted); border:1px solid var(--border); padding:5px 10px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; font-family:'Inter',sans-serif; transition:all 0.2s; }
.btn-toggle:hover { border-color:var(--muted); color:var(--ink); }
.btn-delete { background:rgba(248,81,73,0.1); color:#f85149; border:1px solid rgba(248,81,73,0.2); padding:5px 10px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; font-family:'Inter',sans-serif; transition:all 0.2s; }
.btn-delete:hover { background:#f85149; color:#fff; }

.gallery-preview { display:flex; gap:6px; flex-wrap:wrap; margin-top:8px; }
.gallery-thumb { width:40px; height:40px; border-radius:6px; object-fit:cover; border:1px solid var(--border); }

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
.field textarea { min-height:80px; resize:vertical; }
.field-row { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
.check-label { display:flex; align-items:center; gap:8px; font-size:14px; color:var(--ink); cursor:pointer; }
.check-label input { width:auto; accent-color:var(--teal); }
.file-note { font-size:12px; color:var(--muted); margin-top:5px; line-height:1.4; }
.save-btn { width:100%; background:linear-gradient(135deg,var(--teal),#2d8a82); color:#fff; border:none; padding:12px; border-radius:8px; font-family:'Inter',sans-serif; font-size:14px; font-weight:700; cursor:pointer; margin-top:8px; transition:opacity 0.2s; }
.save-btn:hover { opacity:0.9; }
.cancel-btn { display:block; text-align:center; margin-top:10px; font-size:13px; color:var(--muted); text-decoration:none; }
.cancel-btn:hover { color:var(--ink); }
</style>
</head>
<body>
<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon">⚡</div>
    <div><div class="brand-name">Aff Digital</div><div class="brand-sub">Admin Panel</div></div>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-label">Menu</div>
    <a class="nav-link" href="dashboard.php"><span class="icon">📊</span> Dashboard</a>
    <a class="nav-link" href="orders.php"><span class="icon">🧾</span> Pesanan</a>
    <a class="nav-link" href="packages.php"><span class="icon">📦</span> Paket Layanan</a>
    <a class="nav-link active" href="portfolios.php"><span class="icon">🖼️</span> Portofolio</a>
    <div class="nav-label">Website</div>
    <a class="nav-link" href="../index.php" target="_blank"><span class="icon">🌐</span> Lihat Website</a>
  </nav>
  <div class="sidebar-footer">
    <div class="user-box">
      <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['admin_user'] ?? 'A', 0, 1)); ?></div>
      <div><div class="user-name"><?php echo h4($_SESSION['admin_user'] ?? 'Admin'); ?></div><div class="user-role">Administrator</div></div>
    </div>
    <a class="logout-btn" href="../logout.php">Logout →</a>
  </div>
</aside>

<!-- MAIN CONTENT -->
<div class="main">
  <div class="topbar">
    <h1>🖼️ Manajemen Portofolio</h1>
    <a class="add-btn" href="portfolios.php?new">+ Tambah Portofolio</a>
  </div>
  <div class="content">

    <?php if ($msg): ?><div class="alert alert-ok">✅ <?php echo h4($msg); ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-err">⚠️ <?php echo h4($err); ?></div><?php endif; ?>

    <div class="grid">
      <!-- TABLE -->
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Preview</th>
              <th>Judul & Deskripsi</th>
              <th>Kategori</th>
              <th>Tipe</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($portfolios as $p):
              $imgList = !empty($p['images_json']) ? json_decode($p['images_json'], true) : [];
              if (!is_array($imgList)) $imgList = [];
              $imgCount = count($imgList);
            ?>
            <tr>
              <td>
                <?php if ($p['media_type'] === 'video'): ?>
                  <div class="thumb-preview" style="display:flex;align-items:center;justify-content:center;background:#1e1e2e;color:#f472b6;font-size:20px;">🎬</div>
                <?php else: ?>
                  <img src="../<?php echo h4($p['media_url']); ?>" alt="" class="thumb-preview" onerror="this.src='https://placehold.co/100?text=Image'">
                <?php endif; ?>
              </td>
              <td>
                <div style="font-weight:700"><?php echo h4($p['title']); ?></div>
                <div style="font-size:12px;color:var(--muted);max-width:240px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo h4($p['description']); ?></div>
                <?php if ($imgCount > 1): ?>
                  <div style="font-size:11px;color:var(--teal);margin-top:4px;font-weight:600;">🖼️ <?php echo $imgCount; ?> Gambar terlampir</div>
                <?php endif; ?>
              </td>
              <td><span style="font-size:12px;background:var(--surface2);padding:3px 10px;border-radius:6px;color:var(--ink);font-weight:600"><?php echo h4($p['category_label']); ?></span></td>
              <td><span class="media-badge badge-<?php echo h4($p['media_type']); ?>"><?php echo strtoupper($p['media_type']); ?></span></td>
              <td>
                <span class="active-dot <?php echo $p['is_active'] ? 'dot-on' : 'dot-off'; ?>"></span>
                <?php echo $p['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
              </td>
              <td>
                <div class="action-btns">
                  <a class="btn-edit" href="portfolios.php?edit=<?php echo $p['id']; ?>">✏ Edit</a>
                  <form method="POST" style="display:inline">
                    <input type="hidden" name="action" value="toggle">
                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                    <button class="btn-toggle" type="submit"><?php echo $p['is_active'] ? '⊘' : '✓'; ?></button>
                  </form>
                  <form method="POST" style="display:inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus portofolio ini?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                    <button class="btn-delete" type="submit">🗑</button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($portfolios)): ?>
            <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--muted)">Belum ada portofolio. Klik "+ Tambah Portofolio" di atas.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- FORM PANEL -->
      <?php if ($editItem || $addNew): ?>
      <div class="form-panel">
        <h2><?php echo $editItem ? '✏ Edit Portofolio #' . $editItem['id'] : '➕ Tambah Portofolio Baru'; ?></h2>
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="action" value="save">
          <input type="hidden" name="id" value="<?php echo $editItem ? $editItem['id'] : '0'; ?>">
          <input type="hidden" name="media_url_existing" value="<?php echo h4($editItem['media_url'] ?? ''); ?>">
          <input type="hidden" name="images_json_existing" value="<?php echo h4($editItem['images_json'] ?? '[]'); ?>">

          <div class="field">
            <label>Judul Portofolio</label>
            <input type="text" name="title" required maxlength="150" value="<?php echo h4($editItem['title'] ?? ''); ?>" placeholder="Contoh: Website Profil Toko Online">
          </div>

          <div class="field">
            <label>Kategori (Statis)</label>
            <select name="category_label" required>
              <?php foreach ($staticCategories as $cat): ?>
                <option value="<?php echo $cat; ?>" <?php if(($editItem['category_label']??'')===$cat) echo 'selected';?>><?php echo $cat; ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="field">
            <label>Deskripsi Lengkap Proyek</label>
            <textarea name="description" placeholder="Penjelasan mengenai fitur, hasil pengerjaan, atau detail proyek..."><?php echo h4($editItem['description'] ?? ''); ?></textarea>
          </div>

          <div class="field">
            <label>Tipe Media Utama</label>
            <select name="media_type">
              <option value="image" <?php if(($editItem['media_type']??'image')==='image') echo 'selected';?>>Gambar / Galeri Foto</option>
              <option value="video" <?php if(($editItem['media_type']??'')==='video') echo 'selected';?>>Video Promosi</option>
            </select>
          </div>

          <div class="field">
            <label>Upload File Media (Maksimal 10 Gambar/Video)</label>
            <input type="file" name="media_files[]" accept="image/*,video/*" multiple>
            <div class="file-note">Anda dapat memilih hingga <strong>10 gambar/video sekaligus</strong>. Format: JPG, PNG, WEBP, MP4, WEBM.</div>
            
            <?php
            $currentGallery = !empty($editItem['images_json']) ? json_decode($editItem['images_json'], true) : [];
            if (!is_array($currentGallery) && !empty($editItem['media_url'])) {
                $currentGallery = [$editItem['media_url']];
            }
            if (!empty($currentGallery)):
            ?>
              <div style="font-size:12px;color:var(--muted);margin-top:10px;font-weight:600;">Media Terpasang (<?php echo count($currentGallery); ?> file):</div>
              <div class="gallery-preview">
                <?php foreach ($currentGallery as $gUrl): ?>
                  <img src="../<?php echo h4($gUrl); ?>" class="gallery-thumb" onerror="this.style.display='none'">
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <div class="field">
            <label class="check-label">
              <input type="checkbox" name="is_active" value="1" <?php if(($editItem['is_active'] ?? 1)) echo 'checked';?>>
              Tampilkan di Website
            </label>
          </div>

          <button class="save-btn" type="submit">💾 Simpan Portofolio</button>
        </form>
        <a class="cancel-btn" href="portfolios.php">← Batal</a>
      </div>
      <?php else: ?>
      <div class="form-panel" style="text-align:center;padding:40px">
        <div style="font-size:40px;margin-bottom:12px">🖼️</div>
        <div style="font-weight:600;margin-bottom:8px">Kelola Portofolio Website</div>
        <div style="font-size:13px;color:var(--muted)">Item otomatis diurutkan <strong>terbaru di atas</strong>. Anda dapat mengunggah hingga 10 gambar per proyek.</div>
        <a class="add-btn" href="portfolios.php?new" style="display:inline-block;margin-top:20px;text-decoration:none;">+ Tambah Portofolio Baru</a>
      </div>
      <?php endif; ?>
    </div>

  </div>
</div>
</body>
</html>
