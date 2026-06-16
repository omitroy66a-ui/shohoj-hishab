<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../modules/business/middleware.php';

$business_id = (int) businessId();
$category_id = (int) ($_POST['category_id'] ?? 0);
$amount = (float) ($_POST['amount'] ?? 0);
$note = trim($_POST['note'] ?? '');
$employee_id = (int) ($_POST['employee_id'] ?? 0);

if ($amount <= 0) {
    echo json_encode(["success" => false, "message" => "Amount must be greater than 0"]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO expenses(category_id, amount, note, employee_id, expense_date, business_id) VALUES(?, ?, ?, ?, CURDATE(), ?)");
if ($stmt) {
    $stmt->bind_param('idsii', $category_id, $amount, $note, $employee_id, $business_id);
    $stmt->execute();
    if ($conn->affected_rows > 0) {
        echo json_encode(["success" => true, "expense_id" => $conn->insert_id]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to add expense"]);
    }
    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Failed to prepare statement"]);
}
