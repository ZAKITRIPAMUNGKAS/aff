<?php
// checkout.php
// Menampilkan form pemesanan untuk satu paket, lalu membuat transaksi Midtrans Snap.

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/midtrans.php';
require_once __DIR__ . '/ipaymu.php';

function h($str) {
    return htmlspecialchars((string) $str, ENT_QUOTES, 'UTF-8');
}
function clean_text($value, $maxLength) {
    $value = trim(strip_tags((string) $value));
    return function_exists('mb_substr') ? mb_substr($value, 0, $maxLength) : substr($value, 0, $maxLength);
}

// Ambil package ID dari GET (pertama kali buka) atau POST (saat form disubmit)
$packageId = 0;
if (isset($_GET['package']) && (int)$_GET['package'] > 0) {
    $packageId = (int)$_GET['package'];
} elseif (isset($_POST['package']) && (int)$_POST['package'] > 0) {
    $packageId = (int)$_POST['package'];
}
$package   = $packageId ? get_package_by_id($packageId) : null;

if (!$package) {
    http_response_code(404);
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Paket tidak ditemukan</title></head><body style="font-family:sans-serif;max-width:560px;margin:80px auto;text-align:center;">';
    echo '<h2>Paket tidak ditemukan</h2><p>Paket yang Anda pilih tidak tersedia atau sudah tidak aktif.</p>';
    echo '<p><a href="index.php#harga">Kembali ke daftar paket</a></p></body></html>';
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Honeypot anti-spam (field hp_url_confirm harus selalu kosong; bot mengisinya)
    if (!empty($_POST['hp_url_confirm'])) {
        header('Location: index.php#harga');
        exit;
    }

    $name  = clean_text($_POST['name'] ?? '', 100);
    $email = clean_text($_POST['email'] ?? '', 150);
    $phone = clean_text($_POST['phone'] ?? '', 30);
    $notes = clean_text($_POST['notes'] ?? '', 500);
    $gateway = ($_POST['gateway'] ?? '') === 'ipaymu' ? 'ipaymu' : 'midtrans';

    if ($name === '') { $errors[] = 'Nama wajib diisi.'; }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Email tidak valid.'; }
    if ($phone === '') { $errors[] = 'Nomor WhatsApp wajib diisi.'; }

    if (empty($errors)) {
        $orderCode = 'AFF-' . date('Ymd-His') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        $amount    = (int) $package['price'];

        $db = get_db();
        $stmt = $db->prepare(
            "INSERT INTO orders (order_code, package_id, customer_name, customer_email, customer_phone, notes, amount, payment_gateway, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')"
        );
        $stmt->execute([$orderCode, $package['id'], $name, $email, $phone, $notes, $amount, $gateway]);

        $itemName = ($package['category'] === 'website' ? 'Website - ' : 'Foto & Video - ') . $package['name'];

        if ($gateway === 'ipaymu') {
            if (IPAYMU_API_KEY === 'GANTI_DENGAN_API_KEY_IPAYMU') {
                // Fallback Simulasi Demo jika API Key iPaymu belum diisi
                header('Location: order_status.php?order=' . urlencode($orderCode) . '&gateway=ipaymu&demo=1');
                exit;
            }

            $result = ipaymu_create_transaction([
                'product'     => [$itemName],
                'qty'         => ['1'],
                'price'       => [(string) $amount],
                'returnUrl'   => rtrim(SITE_URL, '/') . '/order_status.php?order=' . urlencode($orderCode),
                'cancelUrl'   => rtrim(SITE_URL, '/') . '/order_status.php?order=' . urlencode($orderCode),
                'notifyUrl'   => rtrim(SITE_URL, '/') . '/ipaymu_notification.php',
                'buyerName'   => $name,
                'buyerEmail'  => $email,
                'buyerPhone'  => $phone,
                'referenceId' => $orderCode,
            ]);

            if (isset($result['error'])) {
                $errors[] = 'Gagal membuat transaksi iPaymu: ' . $result['error'] . ' <br><a href="order_status.php?order=' . urlencode($orderCode) . '&demo=1" style="color:#d9534f;font-weight:bold;text-decoration:underline;">Lanjut ke Simulasi Pesanan Saja &rarr;</a>';
            } elseif (!empty($result['Url'])) {
                $upd = $db->prepare("UPDATE orders SET snap_token = ? WHERE order_code = ?");
                $upd->execute([$result['SessionID'] ?? null, $orderCode]);
                header('Location: ' . $result['Url']);
                exit;
            } else {
                $errors[] = 'Respons pembayaran iPaymu tidak valid. Silakan coba lagi.';
            }
        } else {
            $nameParts = preg_split('/\s+/', $name, 2);
            $params = [
                'transaction_details' => [
                    'order_id'     => $orderCode,
                    'gross_amount' => $amount,
                ],
                'customer_details' => [
                    'first_name' => $nameParts[0] ?? $name,
                    'last_name'  => $nameParts[1] ?? '',
                    'email'      => $email,
                    'phone'      => $phone,
                ],
                'item_details' => [[
                    'id'       => 'PKG-' . $package['id'],
                    'price'    => $amount,
                    'quantity' => 1,
                    'name'     => substr($itemName, 0, 50),
                ]],
                'callbacks' => [
                    'finish' => rtrim(SITE_URL, '/') . '/order_status.php?order=' . urlencode($orderCode),
                ],
            ];

            $result = midtrans_create_transaction($params);

            if (isset($result['error'])) {
                $errors[] = 'Gagal membuat transaksi Midtrans: ' . $result['error'] . ' <br><a href="order_status.php?order=' . urlencode($orderCode) . '&demo=1" style="color:#d9534f;font-weight:bold;text-decoration:underline;">Lanjut ke Simulasi Pesanan Saja &rarr;</a>';
            } elseif (!empty($result['redirect_url'])) {
                $upd = $db->prepare("UPDATE orders SET snap_token = ? WHERE order_code = ?");
                $upd->execute([$result['token'] ?? null, $orderCode]);
                header('Location: ' . $result['redirect_url']);
                exit;
            } else {
                $errors[] = 'Respons pembayaran Midtrans tidak valid. Silakan coba lagi.';
            }
        }
    }
}

$features = array_filter(array_map('trim', explode("\n", (string) $package['features'])));
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout — <?php echo h($package['name']); ?> | Aff Digital</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root{
    --ink:#0f1623;
    --paper:#f0f2f7;
    --card:#ffffff;
    --amber:#e8a33d;
    --teal:#2fb8ae;
    --teal-dark:#1f8a82;
    --muted:#6b7485;
    --line:rgba(15,22,35,0.10);
    --line-soft:rgba(15,22,35,0.06);
    --radius:20px;
    --shadow-md:0 8px 28px rgba(15,22,35,0.08), 0 2px 8px rgba(15,22,35,0.04);
    --shadow-lg:0 20px 56px rgba(15,22,35,0.12), 0 4px 16px rgba(15,22,35,0.06);
  }
  *{box-sizing:border-box;}
  body{
    margin:0; background:var(--paper);
    background-image:
      radial-gradient(ellipse 80% 50% at 10% -10%, rgba(47,184,174,0.09) 0%, transparent 60%),
      radial-gradient(ellipse 60% 40% at 90% 100%, rgba(232,163,61,0.07) 0%, transparent 55%);
    background-attachment: fixed;
    color:var(--ink); font-family:'Inter',sans-serif; line-height:1.6; -webkit-font-smoothing:antialiased;
  }
  h1,h2,h3{font-family:'Space Grotesk',sans-serif;margin:0;letter-spacing:-0.02em;}
  a{color:var(--teal);}
  .wrap{max-width:940px;margin:0 auto;padding:56px 24px 80px;}
  .back{display:inline-flex;align-items:center;gap:6px;margin-bottom:28px;color:var(--muted);font-size:14px;font-weight:600;text-decoration:none;transition:color 0.2s, transform 0.2s;}
  .back:hover{color:var(--ink);transform:translateX(-3px);}
  .grid{display:grid;grid-template-columns:1fr 1.2fr;gap:36px;align-items:start;}
  @media (max-width:760px){ .grid{grid-template-columns:1fr;} }
  .card{background:var(--card);border:1px solid var(--line-soft);border-radius:var(--radius);padding:36px;box-shadow:var(--shadow-md);transition:transform 0.3s cubic-bezier(0.16,1,0.3,1),box-shadow 0.3s;}
  .card:hover{box-shadow:var(--shadow-lg);}
  .summary .cat{font-size:12px;letter-spacing:0.12em;text-transform:uppercase;color:var(--teal);font-weight:700;margin-bottom:8px;background:rgba(47,184,174,0.10);padding:4px 12px;border-radius:999px;display:inline-block;}
  .summary h2{font-size:26px;margin-bottom:6px;font-weight:800;}
  .summary .tagline{color:var(--muted);font-size:14.5px;margin-bottom:24px;line-height:1.6;}
  .summary .price{font-family:'Space Grotesk',sans-serif;font-size:32px;font-weight:800;margin-bottom:24px;letter-spacing:-0.03em;color:var(--ink);}
  .summary ul{margin:0;padding-left:0;list-style:none;color:var(--muted);font-size:14.5px;display:grid;gap:12px;}
  .summary li{display:flex;align-items:flex-start;gap:10px;}
  .summary li::before{content:"✓";color:var(--teal);font-weight:800;font-size:15px;flex:none;}
  .field{display:grid;gap:7px;margin-bottom:18px;}
  .field label{font-size:12.5px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:0.04em;}
  .field input, .field textarea{border:1px solid var(--line);border-radius:12px;padding:13px 16px;font-family:'Inter',sans-serif;font-size:14.5px;color:var(--ink);background:#fcfdfe;transition:border-color 0.2s, box-shadow 0.2s, background 0.2s;}
  .field input:focus, .field textarea:focus{border-color:var(--teal);background:#fff;box-shadow:0 0 0 4px rgba(47,184,174,0.15);outline:none;}
  .field textarea{resize:vertical;min-height:88px;}
  .hp-field{position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden;}
  .submit-btn{
    width:100%;
    background:linear-gradient(135deg, var(--ink) 0%, #1e2d4a 100%);
    color:#fff;border:none;padding:15px 24px;border-radius:12px;
    font-weight:700;font-size:15.5px;cursor:pointer;margin-top:8px;
    box-shadow:0 4px 16px rgba(15,22,35,0.22);
    position:relative;overflow:hidden;
    transition:transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
  }
  .submit-btn::after{content:"";position:absolute;top:-50%;left:-50%;width:200%;height:200%;background:linear-gradient(60deg,transparent,rgba(255,255,255,0.2),transparent);transform:translateX(-100%) rotate(30deg);transition:transform 0.6s ease;}
  .submit-btn:hover{transform:translateY(-2px);box-shadow:0 10px 28px rgba(15,22,35,0.30);}
  .submit-btn:hover::after{transform:translateX(100%) rotate(30deg);}
  .errors{background:#fdecec;border:1px solid #d9534f;color:#8a2620;padding:14px 18px;border-radius:12px;margin-bottom:20px;font-size:14px;}
  .errors ul{margin:0;padding-left:18px;}
  .secure-note{font-size:12.5px;color:var(--muted);margin-top:16px;text-align:center;line-height:1.6;}
  .gateway-choice{display:grid;gap:10px;}
  .gateway-opt{display:flex;align-items:center;gap:12px;border:1.5px solid var(--line);border-radius:12px;padding:14px 16px;cursor:pointer;font-weight:normal;background:#fafbfc;transition:all 0.25s cubic-bezier(0.16,1,0.3,1);}
  .gateway-opt input{accent-color:var(--teal);width:18px;height:18px;flex:none;}
  .gateway-opt span{display:flex;flex-direction:column;font-size:14.5px;color:var(--ink);font-weight:700;}
  .gateway-opt small{color:var(--muted);font-weight:400;font-size:12.5px;margin-top:2px;}
  .gateway-opt:hover{border-color:var(--teal);background:#fff;transform:translateX(3px);}
  .gateway-opt:has(input:checked){border-color:var(--teal);background:rgba(47,184,174,0.06);transform:translateX(3px);box-shadow:0 4px 16px rgba(47,184,174,0.15);}
</style>
</head>
<body>
<div class="wrap">
  <a class="back" href="index.php#harga">&larr; Kembali ke daftar paket</a>
  <div class="grid">
    <div class="card summary">
      <div class="cat"><?php echo $package['category'] === 'website' ? 'Pembuatan Website' : 'Foto & Video'; ?></div>
      <h2><?php echo h($package['name']); ?></h2>
      <p class="tagline"><?php echo h($package['tagline']); ?></p>
      <div class="price"><?php echo h(format_rupiah($package['price'])); ?></div>
      <ul>
        <?php foreach ($features as $f): ?>
          <li><?php echo h($f); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div class="card">
      <h3 style="margin-bottom:18px;">Data Pemesan</h3>
      <?php if (!empty($errors)): ?>
        <div class="errors"><ul><?php foreach ($errors as $e) echo '<li>' . h($e) . '</li>'; ?></ul></div>
      <?php endif; ?>
      <form method="POST" action="checkout.php?package=<?php echo $packageId; ?>">
        <input type="hidden" name="package" value="<?php echo h($package['id']); ?>">
        <input class="hp-field" type="text" name="hp_url_confirm" id="hp_url_confirm" tabindex="-1" autocomplete="new-password" aria-hidden="true" value="">
        <div class="field"><label for="name">Nama lengkap</label><input id="name" name="name" type="text" required value="<?php echo h($_POST['name'] ?? ''); ?>"></div>
        <div class="field"><label for="email">Email</label><input id="email" name="email" type="email" required value="<?php echo h($_POST['email'] ?? ''); ?>"></div>
        <div class="field"><label for="phone">Nomor WhatsApp</label><input id="phone" name="phone" type="text" required value="<?php echo h($_POST['phone'] ?? ''); ?>"></div>
        <div class="field"><label for="notes">Catatan (opsional)</label><textarea id="notes" name="notes"><?php echo h($_POST['notes'] ?? ''); ?></textarea></div>
        <div class="field">
          <label>Metode pembayaran</label>
          <div class="gateway-choice">
            <label class="gateway-opt">
              <input type="radio" name="gateway" value="midtrans" <?php echo (($_POST['gateway'] ?? 'midtrans') === 'midtrans') ? 'checked' : ''; ?>>
              <span>Midtrans <small>Kartu, e-wallet, VA, QRIS</small></span>
            </label>
            <label class="gateway-opt">
              <input type="radio" name="gateway" value="ipaymu" <?php echo (($_POST['gateway'] ?? '') === 'ipaymu') ? 'checked' : ''; ?>>
              <span>iPaymu <small>VA, QRIS, e-wallet, Alfamart/Indomaret</small></span>
            </label>
          </div>
        </div>
        <button class="submit-btn" type="submit">Lanjut ke Pembayaran</button>
      </form>
      <p class="secure-note">
        Pembayaran diproses aman lewat Midtrans &amp; iPaymu.<br>
        Dengan melanjutkan, Anda menyetujui <a href="syarat-ketentuan.php" target="_blank" style="color:var(--teal);text-decoration:underline;">Syarat &amp; Ketentuan</a>, <a href="refund-policy.php" target="_blank" style="color:var(--teal);text-decoration:underline;">Refund Policy</a>, dan <a href="faq.php" target="_blank" style="color:var(--teal);text-decoration:underline;">FAQ</a>.
      </p>
    </div>
  </div>
</div>
</body>
</html>
