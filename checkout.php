<?php
// checkout.php — AFF Digital Direct Payment Gateway Checkout
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

// Ambil package ID dari GET atau POST
$packageId = 1;
if (isset($_GET['package']) && (int)$_GET['package'] > 0) {
    $packageId = (int)$_GET['package'];
} elseif (isset($_POST['package']) && (int)$_POST['package'] > 0) {
    $packageId = (int)$_POST['package'];
}
$package = get_package_by_id($packageId);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['hp_url_confirm'])) {
        header('Location: index.php#pricing');
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
        $amount    = (int) preg_replace('/[^0-9]/', '', (string)$package['price']);
        if ($amount <= 0) $amount = 1500000;

        try {
            $db = get_db();
            if ($db) {
                $stmt = $db->prepare(
                    "INSERT INTO orders (order_code, package_id, customer_name, customer_email, customer_phone, notes, amount, payment_gateway, status)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')"
                );
                $stmt->execute([$orderCode, $package['id'], $name, $email, $phone, $notes, $amount, $gateway]);
            }
        } catch (Exception $e) {}

        $itemName = ($package['category'] === 'website' ? 'Website - ' : 'Sistem - ') . $package['name'];

        if ($gateway === 'ipaymu') {
            if (IPAYMU_API_KEY === 'GANTI_DENGAN_API_KEY_IPAYMU') {
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
                $errors[] = 'Gagal membuat transaksi iPaymu: ' . $result['error'] . ' <br><a href="order_status.php?order=' . urlencode($orderCode) . '&demo=1" class="text-rose-400 font-bold underline">Lanjut ke Simulasi Pesanan Saja &rarr;</a>';
            } elseif (!empty($result['Url'])) {
                header('Location: ' . $result['Url']);
                exit;
            } else {
                $errors[] = 'Respons pembayaran iPaymu tidak valid. Silakan coba lagi.';
            }
        } else {
            // Midtrans Snap
            if (MIDTRANS_SERVER_KEY === 'GANTI_DENGAN_SERVER_KEY_MIDTRANS_ANDA') {
                header('Location: order_status.php?order=' . urlencode($orderCode) . '&gateway=midtrans&demo=1');
                exit;
            }

            $nameParts = preg_split('/\s+/', $name, 2);
            $params = [
                'transaction_details' => [
                    'order_id'     => $orderCode,
                    'gross_amount' => $amount,
                ],
                'item_details' => [
                    [
                        'id'       => (string) $package['id'],
                        'price'    => $amount,
                        'quantity' => 1,
                        'name'     => mb_substr($itemName, 0, 50),
                    ]
                ],
                'customer_details' => [
                    'first_name' => $nameParts[0],
                    'last_name'  => $nameParts[1] ?? '',
                    'email'      => $email,
                    'phone'      => $phone,
                ],
            ];

            $snapToken = midtrans_create_snap_token($params);

            if (is_array($snapToken) && isset($snapToken['error'])) {
                $errors[] = 'Gagal membuat token Midtrans: ' . $snapToken['error'] . ' <br><a href="order_status.php?order=' . urlencode($orderCode) . '&demo=1" class="text-rose-400 font-bold underline">Lanjut ke Simulasi Pesanan Saja &rarr;</a>';
            } elseif (is_string($snapToken) && $snapToken !== '') {
                $snapUrl = MIDTRANS_IS_PRODUCTION
                    ? 'https://app.midtrans.com/snap/v2/vtweb/' . $snapToken
                    : 'https://app.sandbox.midtrans.com/snap/v2/vtweb/' . $snapToken;

                header('Location: ' . $snapUrl);
                exit;
            } else {
                $errors[] = 'Token pembayaran tidak valid.';
            }
        }
    }
}

$price_formatted = is_numeric($package['price']) ? 'Rp ' . number_format((float)$package['price'], 0, ',', '.') : $package['price'];
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-[#120c0c]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pemesanan &amp; Payment Gateway — <?= h($package['name']) ?></title>
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        bebas: ['"Bebas Neue"', 'cursive'],
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        grotesk: ['"Space Grotesk"', 'sans-serif']
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-full bg-[#120c0c] text-[#eee6d8] font-sans antialiased p-4 sm:p-8 flex items-center justify-center">

    <div class="max-w-xl w-full bg-[#1c1313] border border-[#2d1b1b] rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6">
        
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-[#2d1b1b] pb-4">
            <div class="flex items-center gap-3">
                <a href="index.php" class="w-10 h-10 rounded-xl bg-[#8b1818] text-[#eee6d8] flex items-center justify-center font-bebas text-2xl shadow-md">
                    AFF
                </a>
                <div>
                    <h1 class="font-bebas text-2xl text-[#eee6d8] tracking-wider leading-tight">FORM PEMBAYARAN ONLINE</h1>
                    <span class="text-[10px] font-mono text-[#e63946] uppercase font-bold">AFF DIGITAL PAYMENT GATEWAY</span>
                </div>
            </div>

            <a href="index.php#pricing" class="text-xs font-mono text-[#a69090] hover:text-[#eee6d8]">&larr; Batal</a>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="p-4 rounded-2xl bg-rose-950/60 border border-rose-800 text-rose-200 text-xs font-mono space-y-2">
                <?php foreach ($errors as $err): ?>
                    <p class="flex items-start gap-2">
                        <i class="ph-bold ph-warning text-base text-rose-400 shrink-0 mt-0.5"></i>
                        <span><?= $err ?></span>
                    </p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Selected Package Summary Box -->
        <div class="p-4 bg-[#120c0c] rounded-2xl border border-[#8b1818] flex items-center justify-between font-mono">
            <div>
                <span class="text-[10px] text-[#a69090] uppercase block font-bold">PAKET DIPILIH:</span>
                <strong class="text-base font-extrabold text-[#eee6d8]"><?= h($package['name']) ?></strong>
                <p class="text-[11px] text-[#a69090] font-sans mt-0.5 line-clamp-1"><?= h($package['description']) ?></p>
            </div>
            <div class="text-right shrink-0">
                <span class="text-[10px] text-[#a69090] uppercase block">TOTAL</span>
                <strong class="font-bebas text-3xl text-[#e63946]"><?= h($price_formatted) ?></strong>
            </div>
        </div>

        <!-- Checkout Form -->
        <form method="POST" action="" class="space-y-4 font-mono text-xs">
            <input type="hidden" name="package" value="<?= h($package['id']) ?>">
            <input type="text" name="hp_url_confirm" value="" style="display:none;" tabindex="-1" autocomplete="off">

            <div>
                <label class="block text-[11px] font-bold text-[#a69090] uppercase mb-1">Nama Lengkap</label>
                <input type="text" name="name" value="<?= h($_POST['name'] ?? '') ?>" required placeholder="Contoh: Budi Santoso" class="w-full px-4 py-3 bg-[#120c0c] border border-[#2d1b1b] rounded-2xl text-[#eee6d8] placeholder-[#594848] focus:outline-none focus:border-[#8b1818]">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-[#a69090] uppercase mb-1">Email</label>
                    <input type="email" name="email" value="<?= h($_POST['email'] ?? '') ?>" required placeholder="budi@domain.com" class="w-full px-4 py-3 bg-[#120c0c] border border-[#2d1b1b] rounded-2xl text-[#eee6d8] placeholder-[#594848] focus:outline-none focus:border-[#8b1818]">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-[#a69090] uppercase mb-1">No. WhatsApp / HP</label>
                    <input type="text" name="phone" value="<?= h($_POST['phone'] ?? '') ?>" required placeholder="081234567890" class="w-full px-4 py-3 bg-[#120c0c] border border-[#2d1b1b] rounded-2xl text-[#eee6d8] placeholder-[#594848] focus:outline-none focus:border-[#8b1818]">
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-[#a69090] uppercase mb-1">Pilih Payment Gateway</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="p-3 bg-[#120c0c] border border-[#8b1818] rounded-2xl flex items-center gap-2 cursor-pointer hover:border-[#e63946] transition-colors">
                        <input type="radio" name="gateway" value="midtrans" checked class="accent-[#e63946]">
                        <div>
                            <strong class="text-xs text-[#eee6d8] block">Midtrans Snap</strong>
                            <span class="text-[9px] text-[#a69090]">BCA, Mandiri, QRIS</span>
                        </div>
                    </label>
                    <label class="p-3 bg-[#120c0c] border border-[#2d1b1b] rounded-2xl flex items-center gap-2 cursor-pointer hover:border-[#e63946] transition-colors">
                        <input type="radio" name="gateway" value="ipaymu" class="accent-[#e63946]">
                        <div>
                            <strong class="text-xs text-[#eee6d8] block">iPaymu Gateway</strong>
                            <span class="text-[9px] text-[#a69090]">VA, E-Wallet, Retail</span>
                        </div>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-[#a69090] uppercase mb-1">Catatan Tambahan (Opsional)</label>
                <textarea name="notes" rows="2" placeholder="Jelaskan kebutuhan khusus atau preferensi warna/domain..." class="w-full px-4 py-2.5 bg-[#120c0c] border border-[#2d1b1b] rounded-2xl text-[#eee6d8] placeholder-[#594848] focus:outline-none focus:border-[#8b1818]"><?= h($_POST['notes'] ?? '') ?></textarea>
            </div>

            <div class="pt-3 border-t border-[#2d1b1b] flex items-center justify-between">
                <a href="index.php#pricing" class="text-xs text-[#a69090] hover:text-[#eee6d8]">&larr; Kembali</a>
                <button type="submit" class="py-3.5 px-6 rounded-2xl bg-[#8b1818] hover:bg-[#a81d1d] text-[#eee6d8] font-grotesk font-bold text-xs uppercase tracking-wider transition-all shadow-[0_0_20px_rgba(139,24,24,0.5)] flex items-center gap-2 hover:scale-[1.02]">
                    <i class="ph-bold ph-lock-key text-base"></i>
                    <span>Bayar Sekarang &rarr;</span>
                </button>
            </div>
        </form>

    </div>

</body>
</html>
