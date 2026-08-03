<?php
// process_comment.php
// Menerima kiriman form komentar/ulasan dan menyimpannya ke data/comments.json

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Honeypot anti-spam: field ini harus kosong (disembunyikan dari manusia lewat CSS)
if (!empty($_POST['website'])) {
    header('Location: index.php?ok=1#testimoni');
    exit;
}

function clean_text($value, $maxLength) {
    $value = trim(strip_tags((string) $value));
    if (function_exists('mb_substr')) {
        $value = mb_substr($value, 0, $maxLength);
    } else {
        $value = substr($value, 0, $maxLength);
    }
    return $value;
}

$name    = clean_text($_POST['name'] ?? '', 60);
$layanan = clean_text($_POST['layanan'] ?? '', 40);
$message = clean_text($_POST['message'] ?? '', 500);

$allowedLayanan = ['Pembuatan Website', 'Foto & Video', 'Keduanya'];
if (!in_array($layanan, $allowedLayanan, true)) {
    $layanan = 'Pembuatan Website';
}

$rating = isset($_POST['rating']) ? (int) $_POST['rating'] : 5;
if ($rating < 1) { $rating = 1; }
if ($rating > 5) { $rating = 5; }

// Jika nama atau pesan kosong, batalkan penyimpanan
if ($name === '' || $message === '') {
    header('Location: index.php?err=1#tulis-ulasan');
    exit;
}

$dataFile = __DIR__ . '/data/comments.json';

// Buka file dengan lock supaya aman dari penulisan bersamaan
$fp = fopen($dataFile, 'c+');
if ($fp === false) {
    header('Location: index.php?err=1#tulis-ulasan');
    exit;
}

flock($fp, LOCK_EX);
$existing = stream_get_contents($fp);
$comments = json_decode($existing, true);
if (!is_array($comments)) {
    $comments = [];
}

$comments[] = [
    'id'      => uniqid('c_', true),
    'name'    => $name,
    'layanan' => $layanan,
    'rating'  => $rating,
    'message' => $message,
    'date'    => date('Y-m-d'),
];

ftruncate($fp, 0);
rewind($fp);
fwrite($fp, json_encode($comments, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
fflush($fp);
flock($fp, LOCK_UN);
fclose($fp);

header('Location: index.php?ok=1#testimoni');
exit;
