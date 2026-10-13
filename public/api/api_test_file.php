<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

echo json_encode([
    'connected'   => $conn instanceof mysqli && !$conn->connect_error,
    'server_info' => $conn->server_info,
    'database'    => $conn->query("SELECT DATABASE() AS db")->fetch_assoc()['db'],
]);