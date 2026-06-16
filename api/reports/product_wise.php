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

$stmt = $conn->prepare("
    SELECT p.name, IFNULL(SUM(si.qty), 0) AS total_qty 
    FROM sale_items si 
    LEFT JOIN products p ON si.product_id = p.id 
    WHERE si.sale_id IN (SELECT id FROM sales WHERE business_id = ?) 
    GROUP BY si.product_id 
    ORDER BY total_qty DESC
");

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

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

http_response_code(200);
echo json_encode([
    "success" => true,
    "data" => $data
]);
