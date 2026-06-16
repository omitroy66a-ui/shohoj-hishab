<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../modules/business/middleware.php';

$business_id = businessId();

$sales = $conn->query("SELECT IFNULL(SUM(grand_total), 0) AS total FROM sales WHERE business_id='$business_id'")->fetch_assoc();
$purchase = $conn->query("SELECT IFNULL(SUM(grand_total), 0) AS total FROM purchases WHERE business_id='$business_id'")->fetch_assoc();
$expense = $conn->query("SELECT IFNULL(SUM(amount), 0) AS total FROM expenses WHERE business_id='$business_id'")->fetch_assoc();
$customers = $conn->query("SELECT COUNT(*) AS t FROM customers WHERE business_id='$business_id'")->fetch_assoc();
$products = $conn->query("SELECT COUNT(*) total FROM products WHERE business_id='$business_id'")->fetch_assoc();

echo json_encode([
    "success" => true,
    "sales" => $sales['total'],
    "purchase" => $purchase['total'],
    "expense" => $expense['total'],
    "customers" => $customers['t'],
    "products" => $products['total']
]);
