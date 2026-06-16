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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "POST required"]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$product_id = (int)($input['product_id'] ?? 0);
$qty = (float)($input['qty'] ?? 1);

if (!$product_id || $qty <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Invalid product or quantity"]);
    exit;
}

// Get product
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND business_id = ?");
$stmt->bind_param("ii", $product_id, $business_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    http_response_code(404);
    echo json_encode(["success" => false, "error" => "Product not found"]);
    exit;
}

if ($product['stock'] < $qty) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Insufficient stock"]);
    exit;
}

// Initialize cart session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add to cart
$cart_item = [
    'product_id' => $product['id'],
    'barcode' => $product['barcode'],
    'name' => $product['name'],
    'price' => (float)$product['sale_price'],
    'qty' => $qty,
    'total' => (float)$product['sale_price'] * $qty
];

$_SESSION['cart'][] = $cart_item;

http_response_code(200);
echo json_encode([
    "success" => true,
    "message" => "Product added to cart",
    "cart_count" => count($_SESSION['cart'])
]);
