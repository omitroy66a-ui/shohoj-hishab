<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../modules/business/middleware.php';

$business_id = businessId();
$products = [];
$res = $conn->query("SELECT id,name,sale_price,stock FROM products WHERE stock > 0 AND business_id='$business_id'");
while ($row = $res->fetch_assoc()) {
    $products[] = $row;
}

echo json_encode([
    "success" => true,
    "products" => $products
]);
