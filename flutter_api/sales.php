<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$res = $conn->query("SELECT * FROM sales ORDER BY id DESC");
$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
