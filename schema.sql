-- schema.sql
-- Import file ini lewat phpMyAdmin (cPanel > phpMyAdmin > pilih database Anda > tab Import)
-- SEBELUM mengisi kredensial database di admin/config.php.

CREATE TABLE IF NOT EXISTS packages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category VARCHAR(30) NOT NULL,          -- 'website' atau 'foto_video'
  name VARCHAR(100) NOT NULL,
  tagline VARCHAR(160) DEFAULT NULL,
  price DECIMAL(12,0) NOT NULL,           -- harga dalam Rupiah, tanpa desimal
  features TEXT,                          -- daftar fitur, dipisah baris baru (\n)
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_code VARCHAR(40) NOT NULL UNIQUE,
  package_id INT NOT NULL,
  customer_name VARCHAR(100) NOT NULL,
  customer_email VARCHAR(150) NOT NULL,
  customer_phone VARCHAR(30) NOT NULL,
  notes VARCHAR(500) DEFAULT NULL,
  amount DECIMAL(12,0) NOT NULL,
  payment_gateway ENUM('midtrans','ipaymu') NOT NULL DEFAULT 'midtrans',
  status ENUM('pending','paid','failed','expired','cancelled') NOT NULL DEFAULT 'pending',
  snap_token VARCHAR(120) DEFAULT NULL,
  payment_type VARCHAR(50) DEFAULT NULL,
  midtrans_transaction_id VARCHAR(100) DEFAULT NULL,
  raw_notification TEXT DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_orders_package FOREIGN KEY (package_id) REFERENCES packages(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- MIGRASI (hanya jalankan ini jika Anda SUDAH pernah import schema.sql
-- versi sebelumnya yang belum punya kolom payment_gateway).
-- Jika ini instalasi baru, ABAIKAN baris di bawah — kolom sudah
-- termasuk di definisi tabel "orders" di atas.
-- ============================================================
-- ALTER TABLE orders ADD COLUMN payment_gateway ENUM('midtrans','ipaymu') NOT NULL DEFAULT 'midtrans' AFTER amount;

-- ============================================================
-- CONTOH PAKET (silakan ubah nama, harga, dan fitur sesuai bisnis Anda
-- lewat phpMyAdmin, tabel "packages")
-- ============================================================

INSERT INTO packages (category, name, tagline, price, features, sort_order) VALUES
('website', 'Basic', 'Company profile / landing page', 1500000,
 'Landing page 1 halaman\nDesain responsif (HP & desktop)\nForm kontak\nOptimasi kecepatan dasar\nRevisi 2x', 1),
('website', 'Pro', 'Toko online / katalog produk', 3500000,
 'Website multi-halaman\nKatalog produk / toko online\nIntegrasi WhatsApp\nSEO dasar\nRevisi 3x\nPendampingan 30 hari', 2),
('website', 'Custom', 'Sistem custom (POS, HR, Inventori, dll)', 7500000,
 'Sistem sesuai alur bisnis Anda\nDashboard admin & laporan\nManajemen pengguna / hak akses\nIntegrasi database\nPendampingan 60 hari\nHarga final menyesuaikan kebutuhan', 3),
('foto_video', 'Basic', 'Foto produk untuk katalog', 750000,
 '10 foto produk\nEditing & color grading\nFormat siap upload marketplace\nDurasi pengerjaan 3 hari', 1),
('foto_video', 'Pro', 'Foto & video promosi', 2000000,
 '20 foto produk\n1 video promosi (30-60 detik)\nEditing profesional\nFormat siap unggah media sosial\nDurasi pengerjaan 5 hari', 2),
('foto_video', 'Custom', 'Dokumentasi event full day', 3500000,
 'Dokumentasi foto & video seharian\n1 videografer + 1 fotografer\nHighlight video\nSemua file mentah disertakan\nHarga final menyesuaikan lokasi & durasi', 3);
