<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../modules/business/middleware.php';

$customer_name = isset($_POST['customer_name']) ? trim($_POST['customer_name']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$address = isset($_POST['address']) ? trim($_POST['address']) : '';
$total = isset($_POST['total']) ? (float) $_POST['total'] : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : 'pending';
$business_id = (int) businessId();

$stmt = $conn->prepare("INSERT INTO online_orders(business_id, customer_name, phone, address, total, status) VALUES(?, ?, ?, ?, ?, ?)");
if ($stmt) {
    $stmt->bind_param('issdss', $business_id, $customer_name, $phone, $address, $total, $status);
    $stmt->execute();
    if ($conn->affected_rows > 0) {
        echo json_encode(["success" => true, "order_id" => $conn->insert_id]);
    } else {
        echo json_encode(["success" => false, "message" => "Unable to create order"]);
    }
    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Failed to prepare statement"]);
}
