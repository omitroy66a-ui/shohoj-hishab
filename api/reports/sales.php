<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../modules/business/middleware.php';

$business_id = businessId();

if (!$business_id) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "error" => "Unauthorized"
    ]);
    exit;
}

$stmt = $conn->prepare("SELECT DATE(created_at) AS date, SUM(grand_total) AS total FROM sales WHERE business_id = ? GROUP BY DATE(created_at) ORDER BY DATE(created_at) ASC");
$stmt->bind_param("i", $business_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

http_response_code(200);
echo json_encode([
    "success" => true,
    "data" => $data
]);
