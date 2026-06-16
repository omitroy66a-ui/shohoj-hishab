<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../modules/business/middleware.php';

$business_id = businessId();
$sales = $conn->query("SELECT IFNULL(SUM(grand_total), 0) AS t FROM sales WHERE business_id='$business_id'")->fetch_assoc();
$products = $conn->query("SELECT COUNT(*) total FROM products WHERE business_id='$business_id'")->fetch_assoc();

echo json_encode([
    'success' => true,
    'sales' => $sales['t'],
    'products' => $products['total']
]);
