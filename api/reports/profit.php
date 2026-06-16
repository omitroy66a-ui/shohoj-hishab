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

$stmt = $conn->prepare("SELECT IFNULL(SUM(grand_total), 0) AS total FROM sales WHERE business_id = ?");
$stmt->bind_param("i", $business_id);
$stmt->execute();
$sales = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT IFNULL(SUM(grand_total), 0) AS total FROM purchases WHERE business_id = ?");
$stmt->bind_param("i", $business_id);
$stmt->execute();
$purchases = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT IFNULL(SUM(amount), 0) AS total FROM expenses WHERE business_id = ?");
$stmt->bind_param("i", $business_id);
$stmt->execute();
$expenses = $stmt->get_result()->fetch_assoc()['total'];

$profit = $sales - ($purchases + $expenses);

http_response_code(200);
echo json_encode([
    "success" => true,
    "sales" => (float)$sales,
    "purchases" => (float)$purchases,
    "expenses" => (float)$expenses,
    "profit" => (float)$profit
]);
