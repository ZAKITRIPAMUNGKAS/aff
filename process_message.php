<?php
// process_message.php
// Menerima kiriman form kontak dan menyimpannya ke data/pesan.json

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Honeypot anti-spam
if (!empty($_POST['company'])) {
    header('Location: index.php?okmsg=1#kontak');
    exit;
}

function clean_text_msg($value, $maxLength) {
    $value = trim(strip_tags((string) $value));
    if (function_exists('mb_substr')) {
        $value = mb_substr($value, 0, $maxLength);
    } else {
        $value = substr($value, 0, $maxLength);
    }
    return $value;
}

$nama    = clean_text_msg($_POST['nama'] ?? '', 60);
$kontak  = clean_text_msg($_POST['kontak'] ?? '', 60);
$layanan = clean_text_msg($_POST['layanan'] ?? '', 40);
$pesan   = clean_text_msg($_POST['pesan'] ?? '', 800);

if ($nama === '' || $kontak === '') {
    header('Location: index.php?errmsg=1#kontak');
    exit;
}

$dataFile = __DIR__ . '/data/pesan.json';

$fp = fopen($dataFile, 'c+');
if ($fp === false) {
    header('Location: index.php?errmsg=1#kontak');
    exit;
}

flock($fp, LOCK_EX);
$existing = stream_get_contents($fp);
$pesanList = json_decode($existing, true);
if (!is_array($pesanList)) {
    $pesanList = [];
}

$pesanList[] = [
    'id'      => uniqid('m_', true),
    'nama'    => $nama,
    'kontak'  => $kontak,
    'layanan' => $layanan,
    'pesan'   => $pesan,
    'tanggal' => date('Y-m-d H:i'),
];

ftruncate($fp, 0);
rewind($fp);
fwrite($fp, json_encode($pesanList, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
fflush($fp);
flock($fp, LOCK_UN);
fclose($fp);

header('Location: index.php?okmsg=1#kontak');
exit;
