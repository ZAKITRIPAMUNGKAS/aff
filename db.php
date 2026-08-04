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
        } catch (PDOException $e) {
            http_response_code(500);
            die('Koneksi database gagal. Pastikan DB_HOST, DB_NAME, DB_USER, DB_PASS di admin/config.php sudah benar.');
        }
    }
    return $pdo;
}

// Ambil semua paket aktif, dikelompokkan per kategori.
function get_active_packages() {
    $stmt = get_db()->query("SELECT * FROM packages WHERE is_active = 1 ORDER BY category, sort_order, id");
    $rows = $stmt->fetchAll();
    $grouped = [];
    foreach ($rows as $row) {
        $grouped[$row['category']][] = $row;
    }
    return $grouped;
}

// Ambil satu paket aktif berdasarkan ID.
function get_package_by_id($id) {
    $stmt = get_db()->prepare("SELECT * FROM packages WHERE id = ? AND is_active = 1 LIMIT 1");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

// Ambil semua portofolio aktif.
function get_active_portfolios() {
    try {
        $stmt = get_db()->query("SELECT * FROM portfolios WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

function format_rupiah($amount) {
    return 'Rp' . number_format((float) $amount, 0, ',', '.');
}

