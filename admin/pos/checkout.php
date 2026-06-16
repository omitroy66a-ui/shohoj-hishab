<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../modules/business/middleware.php';

$business_id = businessId();

if (!$business_id) {
    http_response_code(401);
    exit('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    exit('POST required');
}

$input = json_decode(file_get_contents('php://input'), true);
$customer_id = (int)($input['customer_id'] ?? 0);
$subtotal = (float)($input['subtotal'] ?? 0);
$discount = (float)($input['discount'] ?? 0);
$paid = (float)($input['paid'] ?? 0);
$cart = $input['cart'] ?? [];

if (empty($cart) || $subtotal <= 0) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["success" => false, "error" => "Invalid cart"]);
    exit;
}

// Generate invoice number
$date_prefix = date('YmdHis');
$invoice_no = 'INV-' . $date_prefix;

$grand_total = $subtotal - $discount;
$due_amount = $grand_total - $paid;

// Insert sale
$stmt = $conn->prepare("INSERT INTO sales(invoice_no, business_id, customer_id, subtotal, discount, grand_total, paid, due_amount) VALUES(?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("siiddddd", $invoice_no, $business_id, $customer_id, $subtotal, $discount, $grand_total, $paid, $due_amount);
$stmt->execute();
$sale_id = $conn->insert_id;

// Insert sale items and update stock
foreach ($cart as $item) {
    $product_id = (int)$item['product_id'];
    $qty = (float)$item['qty'];
    $price = (float)$item['price'];
    $total = (float)$item['total'];
    
    $stmt = $conn->prepare("INSERT INTO sale_items(sale_id, product_id, qty, price, total) VALUES(?, ?, ?, ?, ?)");
    $stmt->bind_param("iiddd", $sale_id, $product_id, $qty, $price, $total);
    $stmt->execute();
    
    // Update stock
    $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND business_id = ?");
    $stmt->bind_param("dii", $qty, $product_id, $business_id);
    $stmt->execute();
}

header('Content-Type: application/json');
http_response_code(200);
echo json_encode([
    "success" => true,
    "sale_id" => $sale_id,
    "invoice_no" => $invoice_no,
    "grand_total" => $grand_total
]);
