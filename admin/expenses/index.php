<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../modules/business/middleware.php';

$business_id = businessId();

if (!$business_id) {
    http_response_code(401);
    exit('Unauthorized');
}

// Get success message
$success_msg = $_SESSION['success'] ?? '';
unset($_SESSION['success']);

// Auto-detect actor (user/employee who logged in)
$actor_detected = 'System';
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT name FROM employees WHERE id = ? AND business_id = ?");
    $stmt->bind_param("ii", $_SESSION['user_id'], $business_id);
    $stmt->execute();
    $actor = $stmt->get_result()->fetch_assoc();
    if ($actor) {
        $actor_detected = htmlspecialchars($actor['name']);
    }
}

$stmt = $conn->prepare("SELECT * FROM expenses WHERE business_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $business_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Expenses</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
        .container { max-width: 1100px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #f1f1f1; }
        a { color: #007bff; text-decoration: none; }
        .btn { display: inline-block; padding: 10px 16px; background: #007bff; color: #fff; border-radius: 4px; margin-bottom: 15px; }
        .success { background: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #c3e6cb; }
        .actor-info { background: #e7f3ff; color: #004085; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Expenses</h1>
        <?php if ($success_msg): ?>
            <div class="success"><?php echo htmlspecialchars($success_msg); ?></div>
        <?php endif; ?>
        <div class="actor-info">👤 Current User: <strong><?php echo $actor_detected; ?></strong> (Auto-detected)</div>
        <a href="add.php" class="btn">Add Expense</a>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Employee</th>
                    <th>Date</th>
                    <th>Note</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td>
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM expense_categories WHERE id = ?");
                            $stmt->bind_param("i", $row['category_id']);
                            $stmt->execute();
                            $cat = $stmt->get_result()->fetch_assoc();
                            echo $cat ? htmlspecialchars($cat['name_en']) : 'N/A';
                            ?>
                        </td>
                        <td><?php echo number_format($row['amount'], 2); ?></td>
                        <td>
                            <?php
                            if ($row['employee_id']) {
                                $stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
                                $stmt->bind_param("i", $row['employee_id']);
                                $stmt->execute();
                                $emp = $stmt->get_result()->fetch_assoc();
                                echo $emp ? htmlspecialchars($emp['name']) : 'N/A';
                            }
                            ?>
                        </td>
                        <td><?php echo $row['expense_date']; ?></td>
                        <td><?php echo htmlspecialchars($row['note']); ?></td>
                        <td>
                            <a href="edit.php?id=<?php echo $row['id']; ?>">Edit</a> |
                            <a href="delete.php?id=<?php echo $row['id']; ?>" onclick="return confirm('"'"'Delete?'"'"');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
