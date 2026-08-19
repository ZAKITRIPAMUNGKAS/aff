<?php
// admin/index.php — Redirect to login.php or dashboard.php
require_once __DIR__ . '/../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit;
