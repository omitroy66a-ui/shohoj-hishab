<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../modules/business/middleware.php';

$business_id = businessId();

if (!$business_id) {
    http_response_code(401);
    exit('Unauthorized');
}

$id = (int) ($_GET['id'] ?? 0);

if ($id > 0) {
    // Get expense details before deletion
    $stmt = $conn->prepare("SELECT * FROM expenses WHERE id = ? AND business_id = ?");
    $stmt->bind_param("ii", $id, $business_id);
    $stmt->execute();
    $expense = $stmt->get_result()->fetch_assoc();

    if ($expense) {
        // Log deletion for audit trail
        $actor_type = 'admin';
        $actor_id = null;
        $action = 'delete';
        
        if (isset($_SESSION['user_id'])) {
            $actor_id = (int)$_SESSION['user_id'];
        }

        $stmt = $conn->prepare("INSERT INTO expense_logs(expense_id, action, actor_type, actor_id, business_id, old_amount, old_category_id) VALUES(?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isisiii", $id, $action, $actor_type, $actor_id, $business_id, $expense['amount'], $expense['category_id']);
        $stmt->execute();

        // Delete expense
        $stmt = $conn->prepare("DELETE FROM expenses WHERE id = ? AND business_id = ?");
        $stmt->bind_param("ii", $id, $business_id);
        $stmt->execute();

        $_SESSION['success'] = 'Expense deleted successfully';
    }
}

header('Location: index.php');
exit;

