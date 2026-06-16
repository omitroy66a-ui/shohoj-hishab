<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$totalSales = $conn->query("SELECT IFNULL(SUM(grand_total), 0) AS total FROM sales")->fetch_assoc()['total'];
$totalProducts = $conn->query("SELECT COUNT(*) total FROM products")->fetch_assoc()['total'];

echo json_encode([
    'success' => true,
    'sales' => $totalSales,
    'products' => $totalProducts
]);
