<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

$data = [];
$res = $conn->query("SELECT DATE(created_at) d, SUM(grand_total) total FROM sales GROUP BY DATE(created_at) ORDER BY DATE(created_at) ASC");
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
