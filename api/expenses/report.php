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

$stmt = $conn->prepare("SELECT IFNULL(SUM(amount), 0) AS total FROM expenses WHERE MONTH(expense_date) = MONTH(CURDATE()) AND YEAR(expense_date) = YEAR(CURDATE()) AND business_id = ?");
$stmt->bind_param("i", $business_id);
$stmt->execute();
$monthly = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("SELECT IFNULL(SUM(amount), 0) AS total FROM expenses WHERE YEAR(expense_date) = YEAR(CURDATE()) AND business_id = ?");
$stmt->bind_param("i", $business_id);
$stmt->execute();
$yearly = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("SELECT IFNULL(SUM(amount), 0) AS total FROM expenses WHERE business_id = ?");
$stmt->bind_param("i", $business_id);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc();

http_response_code(200);
echo json_encode([
    "success" => true,
    "monthly_expense" => (float)$monthly['total'],
    "yearly_expense" => (float)$yearly['total'],
    "total_expense" => (float)$total['total']
]);
