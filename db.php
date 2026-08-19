<?php
// db.php
// Koneksi database (PDO/MySQL). File ini hanya berisi fungsi, aman di-include di mana saja.

require_once __DIR__ . '/config.php';

function get_db() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
            
            // Auto add column project_link if not exists
            try {
                $pdo->exec("ALTER TABLE portfolios ADD COLUMN project_link VARCHAR(255) NULL AFTER media_type");
            } catch (PDOException $e) {}

            // Auto modify price column in packages to VARCHAR
            try {
                $pdo->exec("ALTER TABLE packages MODIFY COLUMN price VARCHAR(255) NOT NULL");
            } catch (PDOException $e) {}
            
        } catch (PDOException $e) {
            // Return null if database connection fails
            return null;
        }
    }
    return $pdo;
}

// Ambil semua paket aktif, dikelompokkan per kategori.
function get_active_packages() {
    try {
        $db = get_db();
        if ($db) {
            $stmt = $db->query("SELECT * FROM packages WHERE is_active = 1 ORDER BY category, sort_order, id");
            $rows = $stmt->fetchAll();
            $grouped = [];
            foreach ($rows as $row) {
                $grouped[$row['category']][] = $row;
            }
            if (!empty($grouped)) return $grouped;
        }
    } catch (Exception $e) {}

    // Fallback demo packages if DB empty or not connected
    return [
        'Website & System' => [
            [
                'id' => 1,
                'name' => 'Paket Basic Website',
                'price' => '1500000',
                'category' => 'Website & System',
                'description' => 'Cocok untuk Landing Page Perusahaan, UMKM, & Company Profile Modern.',
                'features' => "1 Halaman Responsive Landing Page\nDesain Custom Premium Dark/Light\nIntegrasi Tombol WhatsApp Direct\nDomain & Hosting 1 Tahun\nFree Maintenance 1 Bulan"
            ],
            [
                'id' => 2,
                'name' => 'Paket Custom POS & HR System',
                'price' => '3500000',
                'category' => 'Website & System',
                'description' => 'Pilihan utama bisnis toko/resto yang ingin sistem Kasir POS & Presensi Karyawan terintegrasi.',
                'features' => "Sistem Kasir POS Multi-Company\nPortal Karyawan & Presensi GPS\nKelola Stok, Harga & Menu Makanan\nLaporan Omset Harian/Bulanan\nDatabase System Dedicated"
            ],
            [
                'id' => 3,
                'name' => 'Paket Ultimate Enterprise',
                'price' => '6000000',
                'category' => 'Website & System',
                'description' => 'Pengembangan software skala besar dengan kebutuhan arsitektur custom khusus.',
                'features' => "Custom Web & Backend Architecture\nIntegrasi Payment Gateway & WhatsApp API\nFull Source Code & Database Handover\nDokumentasi Sistem Lengkap\nSupport Prioritas 24/7 (6 Bulan)"
            ]
        ]
    ];
}

// Ambil satu paket aktif berdasarkan ID.
function get_package_by_id($id) {
    try {
        $db = get_db();
        if ($db) {
            $stmt = $db->prepare("SELECT * FROM packages WHERE id = ? AND is_active = 1 LIMIT 1");
            $stmt->execute([$id]);
            $pkg = $stmt->fetch();
            if ($pkg) return $pkg;
        }
    } catch (Exception $e) {}

    $fallback_packages = [
        1 => [
            'id' => 1,
            'name' => 'Paket Basic Website',
            'price' => 1500000,
            'category' => 'Website & System',
            'description' => 'Cocok untuk Landing Page Perusahaan, UMKM, & Company Profile Modern.',
            'features' => "1 Halaman Responsive Landing Page\nDesain Custom Premium Dark/Light\nIntegrasi Tombol WhatsApp Direct\nDomain & Hosting 1 Tahun\nFree Maintenance 1 Bulan"
        ],
        2 => [
            'id' => 2,
            'name' => 'Paket Custom POS & HR System',
            'price' => 3500000,
            'category' => 'Website & System',
            'description' => 'Pilihan utama bisnis toko/resto yang ingin sistem Kasir POS & Presensi Karyawan terintegrasi.',
            'features' => "Sistem Kasir POS Multi-Company\nPortal Karyawan & Presensi GPS\nKelola Stok, Harga & Menu Makanan\nLaporan Omset Harian/Bulanan\nDatabase System Dedicated"
        ],
        3 => [
            'id' => 3,
            'name' => 'Paket Ultimate Enterprise',
            'price' => 6000000,
            'category' => 'Website & System',
            'description' => 'Pengembangan software skala besar dengan kebutuhan arsitektur custom khusus.',
            'features' => "Custom Web & Backend Architecture\nIntegrasi Payment Gateway & WhatsApp API\nFull Source Code & Database Handover\nDokumentasi Sistem Lengkap\nSupport Prioritas 24/7 (6 Bulan)"
        ]
    ];

    return $fallback_packages[$id] ?? [
        'id' => $id,
        'name' => 'Paket Custom AFF Digital #' . $id,
        'price' => 1500000,
        'category' => 'Website & System',
        'description' => 'Paket layanan kustom dari AFF Digital',
        'features' => "Garansi Maintenance\nSupport 24/7"
    ];
}

// Ambil semua portofolio aktif (terbaru paling atas).
function get_active_portfolios() {
    try {
        $db = get_db();
        if ($db) {
            $stmt = $db->query("SELECT * FROM portfolios WHERE is_active = 1 ORDER BY id DESC");
            return $stmt->fetchAll();
        }
    } catch (PDOException $e) {}
    return [];
}

function format_rupiah($amount) {
    if (is_numeric($amount)) {
        return 'Rp ' . number_format((float) $amount, 0, ',', '.');
    }
    
    $amountStr = trim((string)$amount);
    if (stripos($amountStr, 'Rp') === 0) {
        return $amountStr;
    }
    return 'Rp ' . $amountStr;
}
