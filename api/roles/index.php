<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

$result = $conn->query("SELECT * FROM roles ORDER BY id DESC");
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode(["success" => true, "data" => $data]);
