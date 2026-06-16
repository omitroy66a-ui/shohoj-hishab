<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$businesses = $conn->query("SELECT COUNT(*) t FROM businesses")->fetch_assoc()['t'];
$users = $conn->query("SELECT COUNT(*) t FROM customers")->fetch_assoc()['t'];
$sales = $conn->query("SELECT IFNULL(SUM(grand_total),0) t FROM sales")->fetch_assoc()['t'];

echo json_encode([
    'success' => true,
    'businesses' => $businesses,
    'users' => $users,
    'sales' => $sales
]);
