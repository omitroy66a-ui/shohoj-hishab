<?php
require_once __DIR__ . '/../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$business_id = $_SESSION['business_id'] ?? 1;

if ($method === 'GET') {
    $stmt = $conn->prepare("SELECT 
        IFNULL(SUM(grand_total), 0) as sales,
        (SELECT IFNULL(SUM(grand_total), 0) FROM purchases WHERE business_id = ?) as purchases,
        (SELECT IFNULL(SUM(amount), 0) FROM expenses WHERE business_id = ?) as expenses,
        (SELECT COUNT(*) FROM customers WHERE business_id = ?) as total_customers,
        (SELECT COUNT(*) FROM sales WHERE business_id = ?) as total_sales
    FROM sales WHERE business_id = ?");
    
    $stmt->bind_param("iiiii", $business_id, $business_id, $business_id, $business_id, $business_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    http_response_code(200);
    echo json_encode([
        "success" => true,
        "data" => [
            "sales" => (float)$result['sales'],
            "purchases" => (float)$result['purchases'],
            "expenses" => (float)$result['expenses'],
            "profit" => (float)($result['sales'] - ($result['purchases'] + $result['expenses'])),
            "total_customers" => (int)$result['total_customers'],
            "total_sales" => (int)$result['total_sales']
        ]
    ]);
}
