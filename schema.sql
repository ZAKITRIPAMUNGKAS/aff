-- schema.sql
-- Import file ini lewat phpMyAdmin (cPanel > phpMyAdmin > pilih database Anda > tab Import)
-- SEBELUM mengisi kredensial database di config.php.

CREATE TABLE IF NOT EXISTS packages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category VARCHAR(30) NOT NULL DEFAULT 'website',
  name VARCHAR(100) NOT NULL,
  tagline VARCHAR(160) DEFAULT NULL,
  description TEXT DEFAULT NULL,
  price VARCHAR(255) NOT NULL DEFAULT '0',
  features TEXT DEFAULT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Contoh paket awal
INSERT INTO packages (category, name, tagline, description, price, features, sort_order) VALUES
('website', 'Paket Basic Website', 'Cocok untuk Landing Page Perusahaan, UMKM & Company Profile', 'Cocok untuk Landing Page Perusahaan, UMKM, & Company Profile Modern.', '1500000', '1 Halaman Responsive Landing Page\nDesain Custom Premium Dark/Light\nIntegrasi Tombol WhatsApp Direct\nDomain & Hosting 1 Tahun\nFree Maintenance 1 Bulan', 1),
('website', 'Paket Custom POS & HR System', 'Pilihan utama bisnis toko/resto yang ingin sistem terintegrasi', 'Pilihan utama bisnis toko/resto yang ingin sistem Kasir POS & Presensi Karyawan terintegrasi.', '3500000', 'Sistem Kasir POS Multi-Company\nPortal Karyawan & Presensi GPS\nKelola Stok, Harga & Menu Makanan\nLaporan Omset Harian/Bulanan\nDatabase System Dedicated', 2),
('website', 'Paket Ultimate Enterprise', 'Pengembangan software skala besar kebutuhan arsitektur custom', 'Pengembangan software skala besar dengan kebutuhan arsitektur custom khusus.', '6000000', 'Custom Web & Backend Architecture\nIntegrasi Payment Gateway & WhatsApp API\nFull Source Code & Database Handover\nDokumentasi Sistem Lengkap\nSupport Prioritas 24/7 (6 Bulan)', 3);

CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_code VARCHAR(40) NOT NULL UNIQUE,
  package_id INT NOT NULL DEFAULT 1,
  customer_name VARCHAR(100) NOT NULL,
  customer_email VARCHAR(150) NOT NULL,
  customer_phone VARCHAR(30) NOT NULL,
  notes VARCHAR(500) DEFAULT NULL,
  amount DECIMAL(12,0) NOT NULL DEFAULT 0,
  payment_gateway VARCHAR(30) NOT NULL DEFAULT 'midtrans',
  status VARCHAR(20) NOT NULL DEFAULT 'pending',
  snap_token VARCHAR(120) DEFAULT NULL,
  payment_type VARCHAR(50) DEFAULT NULL,
  midtrans_transaction_id VARCHAR(100) DEFAULT NULL,
  raw_notification TEXT DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS portfolios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  category_label VARCHAR(100) NOT NULL DEFAULT 'Website',
  description TEXT DEFAULT NULL,
  media_type VARCHAR(10) NOT NULL DEFAULT 'image',
  project_link VARCHAR(255) DEFAULT NULL,
  media_url VARCHAR(255) NOT NULL DEFAULT '',
  images_json TEXT DEFAULT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Contoh data portofolio awal
INSERT INTO portfolios (title, category_label, description, media_type, media_url, sort_order) VALUES
('Company Profile UMKM', 'Website', 'Landing page satu halaman, fokus konversi.', 'image', 'https://images.unsplash.com/photo-1547658719-da2b51169166?w=800&auto=format&fit=crop&q=80', 1),
('Sistem POS Kasir', 'Sistem', 'Transaksi, stok, laporan omset penjualan.', 'image', 'https://images.unsplash.com/photo-1556742049-0a67daf64f42?w=800&auto=format&fit=crop&q=80', 2),
('Portal Karyawan HR', 'Sistem', 'Presensi GPS, slip gaji digital, pengajuan cuti.', 'image', 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=800&auto=format&fit=crop&q=80', 3),
('Foto Produk UMKM', 'Foto Produk', 'Set foto katalog untuk marketplace & medsos.', 'image', 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800&auto=format&fit=crop&q=80', 4);
