<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/../modules/business/middleware.php';

$business_id = businessId();
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['sales']) || !is_array($input['sales'])) {
    echo json_encode(["success" => false, "message" => "Invalid payload"]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO sales(business_id, invoice_no, grand_total, paid, due, created_at) VALUES(?, ?, ?, 0, ?, NOW())");
foreach ($input['sales'] as $sale) {
    $invoice = trim($sale['invoice'] ?? '');
    $total = isset($sale['total']) ? (float) $sale['total'] : 0;
    if ($stmt) {
        $stmt->bind_param('isdd', $business_id, $invoice, $total, $total);
        $stmt->execute();
    }
}
if ($stmt) $stmt->close();

echo json_encode(["success" => true]);
