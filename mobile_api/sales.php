<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../modules/business/middleware.php';

$business_id = businessId();
$res = $conn->query("SELECT * FROM sales WHERE business_id='$business_id' ORDER BY id DESC");
$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
