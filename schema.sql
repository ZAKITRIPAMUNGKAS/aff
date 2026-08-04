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
-- TABEL PORTOFOLIO
-- ============================================================

CREATE TABLE IF NOT EXISTS portfolios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  category_label VARCHAR(100) NOT NULL,   -- 'Website', 'Sistem', 'Foto Produk', 'Video Promosi', 'Desain Grafis', 'Lainnya'
  description TEXT DEFAULT NULL,
  media_type ENUM('image', 'video') NOT NULL DEFAULT 'image',
  media_url VARCHAR(255) NOT NULL,        -- Gambar/Video utama
  images_json TEXT DEFAULT NULL,           -- JSON Array untuk hingga 10 gambar gallery (misal: ["path1.jpg", "path2.jpg"])
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Migrasi (jika tabel portfolios sudah ada di DB sebelumnya):
-- ALTER TABLE portfolios ADD COLUMN images_json TEXT DEFAULT NULL AFTER media_url;

-- CONTOH DATA PORTOFOLIO AWAL
INSERT INTO portfolios (title, category_label, description, media_type, media_url, sort_order) VALUES
('Company Profile UMKM', 'Website', 'Landing page satu halaman, fokus konversi.', 'image', 'assets/images/company_profile.jpg', 1),
('Toko Online', 'Website', 'Katalog produk lengkap dengan halaman detail.', 'image', 'assets/images/toko_online.jpg', 2),
('Sistem Absensi HR', 'Sistem', 'Pencatatan kehadiran karyawan berbasis web.', 'image', 'assets/images/sistem_absensi.jpg', 3),
('Sistem Retail / Kasir (POS)', 'Sistem', 'Transaksi, stok, dan laporan penjualan.', 'image', 'assets/images/sistem_pos.jpg', 4),
('Sistem Manajemen Gudang', 'Sistem', 'Stok masuk-keluar, lokasi rak, dan laporan gudang.', 'image', 'assets/images/sistem_gudang.jpg', 5),
('Foto Produk', 'Foto Produk', 'Set foto katalog untuk marketplace.', 'image', 'assets/images/foto_produk.jpg', 6),
('Video Promosi', 'Video Promosi', 'Video pendek untuk media sosial.', 'image', 'assets/images/video_promosi.jpg', 7);


