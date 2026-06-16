<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../modules/business/middleware.php';

$business_id = businessId();

if (!$business_id) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "error" => "Unauthorized"
    ]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// GET - Fetch all expenses
if ($method === 'GET') {
    $stmt = $conn->prepare("SELECT e.*, c.name_en, c.name_bn FROM expenses e LEFT JOIN expense_categories c ON e.category_id = c.id WHERE e.business_id = ? ORDER BY e.id DESC");
    $stmt->bind_param("i", $business_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "data" => $data
    ]);
}

// DELETE - Delete expense
elseif ($method === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
    $expense_id = (int)($input['id'] ?? 0);

    if (!$expense_id) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "error" => "Expense ID required"
        ]);
        exit;
    }

    // Verify expense belongs to this business
    $stmt = $conn->prepare("SELECT * FROM expenses WHERE id = ? AND business_id = ?");
    $stmt->bind_param("ii", $expense_id, $business_id);
    $stmt->execute();
    $expense = $stmt->get_result()->fetch_assoc();

    if (!$expense) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "error" => "Expense not found"
        ]);
        exit;
    }

    // Log deletion for audit trail
    $actor_type = 'api';
    $actor_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

    $stmt = $conn->prepare("INSERT INTO expense_logs(expense_id, action, actor_type, actor_id, business_id, old_amount, old_category_id) VALUES(?, ?, ?, ?, ?, ?, ?)");
    $action = 'delete';
    $stmt->bind_param("isssiii", $expense_id, $action, $actor_type, $actor_id, $business_id, $expense['amount'], $expense['category_id']);
    $stmt->execute();

    // Delete expense
    $stmt = $conn->prepare("DELETE FROM expenses WHERE id = ? AND business_id = ?");
    $stmt->bind_param("ii", $expense_id, $business_id);
    
    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Expense deleted successfully",
            "deleted_id" => $expense_id
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "error" => "Failed to delete expense"
        ]);
    }
}

// Unsupported method
else {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "error" => "Method not allowed. Use GET or DELETE."
    ]);
}
