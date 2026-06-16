<?php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json; charset=utf-8');

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$barcode = isset($_GET['barcode']) ? trim($_GET['barcode']) : '';
$products = [];

if ($barcode !== '') {
    $barcodeEscaped = $conn->real_escape_string($barcode);
    $result = $conn->query("SELECT id, name, sale_price, stock, barcode FROM products WHERE barcode = '$barcodeEscaped' LIMIT 1");
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
} elseif ($q !== '') {
    $qEscaped = $conn->real_escape_string($q);
    $result = $conn->query("SELECT id, name, sale_price, stock, barcode FROM products WHERE name LIKE '%$qEscaped%' OR barcode LIKE '%$qEscaped%' ORDER BY name ASC LIMIT 20");
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

echo json_encode($products);
