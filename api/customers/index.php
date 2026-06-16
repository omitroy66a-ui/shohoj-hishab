<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../modules/business/middleware.php';

$business_id = (int) businessId();
$data = [];

$stmt = $conn->prepare("SELECT id, name, phone, address, opening_due, business_id FROM customers WHERE business_id = ? ORDER BY id DESC");
if ($stmt) {
    $stmt->bind_param('i', $business_id);
    $stmt->execute();
    $result = method_exists($stmt, 'get_result') ? $stmt->get_result() : null;
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    } else {
        $stmt->bind_result($id, $name, $phone, $address, $opening_due, $biz_id);
        while ($stmt->fetch()) {
            $data[] = [
                'id' => $id,
                'name' => $name,
                'phone' => $phone,
                'address' => $address,
                'opening_due' => $opening_due,
                'business_id' => $biz_id,
            ];
        }
    }
    $stmt->close();
} else {
    // fallback to safe casted query
    $result = $conn->query("SELECT id, name, phone, address, opening_due, business_id FROM customers WHERE business_id=" . $business_id . " ORDER BY id DESC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
}

echo json_encode(["success" => true, "data" => $data]);
