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

$stmt = $conn->prepare("SELECT IFNULL(SUM(opening_due), 0) AS total FROM customers WHERE business_id = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Database error"
    ]);
    exit;
}

$stmt->bind_param("i", $business_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Query execution failed"
    ]);
    exit;
}

$total_due = $result->fetch_assoc();

http_response_code(200);
echo json_encode([
    "success" => true,
    "total_due" => (float)$total_due['total']
]);
