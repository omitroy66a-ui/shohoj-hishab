<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../modules/business/middleware.php';

header('Content-Type: application/json');

$business_id = businessId();

if (!$business_id) {
    http_response_code(401);
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit;
}

$barcode = $_GET['barcode'] ?? '';

if (empty($barcode)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Barcode required"]);
    exit;
}

$stmt = $conn->prepare("SELECT id, barcode, name, sale_price, stock FROM products WHERE barcode = ? AND business_id = ?");
$stmt->bind_param("si", $barcode, $business_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if ($product) {
    http_response_code(200);
    echo json_encode(["success" => true, "data" => $product]);
} else {
    http_response_code(404);
    echo json_encode(["success" => false, "error" => "Product not found"]);
}
