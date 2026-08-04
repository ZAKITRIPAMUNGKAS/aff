<?php
// admin/config.php
// PENTING: Buka admin/generate_hash.php terlebih dahulu untuk membuat hash password Anda,
// lalu tempel hasilnya menggantikan nilai di bawah ini.
// Setelah selesai, HAPUS file admin/generate_hash.php dari server untuk keamanan.

define('ADMIN_USERNAME', 'admin');      // Ubah username admin di sini
define('ADMIN_PASSWORD_HASH', '$2y$10$J03ePhefLn9Ihme4GAQnK.2kSLvMWM0D0VKxmbif8QtMPRyxDhdEy'); // Password default: admin123

// ============================================================
// KONFIGURASI DATABASE (MySQL)
// Ambil dari cPanel > MySQL Databases. Buat database & user dulu,
// lalu import file schema.sql lewat phpMyAdmin sebelum mengisi ini.
// ============================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'aff');
define('DB_USER', 'root');
define('DB_PASS', '');

// ============================================================
// KONFIGURASI MIDTRANS (Payment Gateway)
// Daftar & ambil kunci di https://dashboard.midtrans.com
// (gunakan akun SANDBOX dulu untuk uji coba, baru pindah ke Production saat live)
// ============================================================
define('MIDTRANS_SERVER_KEY', 'GANTI_DENGAN_SERVER_KEY_MIDTRANS');
define('MIDTRANS_CLIENT_KEY', 'GANTI_DENGAN_CLIENT_KEY_MIDTRANS');
define('MIDTRANS_IS_PRODUCTION', false);

// ============================================================
// KONFIGURASI IPAYMU (Payment Gateway alternatif)
// Daftar & ambil VA + API Key di https://my.ipaymu.com (Production)
// atau https://sandbox.ipaymu.com (Sandbox, untuk uji coba)
// Menu: Integrasi > API Integration
// ============================================================
define('IPAYMU_VA', '1179009612339608');
define('IPAYMU_API_KEY', '9A6BC28B-12C5-4EB7-B776-A2D0C5F2E8BF');
define('IPAYMU_IS_PRODUCTION', true);

// Alamat website ini TANPA garis miring di akhir, dipakai untuk redirect setelah pembayaran.
// Contoh: 'https://affdigital.id'
define('SITE_URL', 'https://affdigital.my.id');
