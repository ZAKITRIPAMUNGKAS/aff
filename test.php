<?php
require 'db.php';
$db = get_db();
$stmt = $db->query("DESCRIBE portfolios");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
