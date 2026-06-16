<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../modules/business/middleware.php';

$business_id = businessId();

if (!$business_id) {
    http_response_code(401);
    exit('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = (int) ($_POST['category_id'] ?? 0);
    $amount = (float) ($_POST['amount'] ?? 0);
    $note = $_POST['note'] ?? '';
    $employee_id = (int) ($_POST['employee_id'] ?? 0);

    $stmt = $conn->prepare("INSERT INTO expenses(category_id, amount, note, employee_id, expense_date, business_id) VALUES(?, ?, ?, ?, CURDATE(), ?)");
    $stmt->bind_param("idsii", $category_id, $amount, $note, $employee_id, $business_id);
    $stmt->execute();
    
    header('Location: index.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM expense_categories WHERE business_id = ? OR business_id = 0");
$zero = 0;
$stmt->bind_param("i", $business_id);
$stmt->execute();
$categories = $stmt->get_result();

$stmt = $conn->prepare("SELECT * FROM employees WHERE business_id = ?");
$stmt->bind_param("i", $business_id);
$stmt->execute();
$employees = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Add Expense</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
        .container { max-width: 700px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; }
        label { display: block; margin: 12px 0 5px 0; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 15px; box-sizing: border-box; }
        button { padding: 12px 18px; background: #007bff; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add Expense</h1>
        <form method="POST">
            <label>Category</label>
            <select name="category_id" required>
                <option value="">Select Category</option>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name_en']); ?></option>
                <?php endwhile; ?>
            </select>

            <label>Amount</label>
            <input type="number" step="0.01" name="amount" required>

            <label>Employee (Optional)</label>
            <select name="employee_id">
                <option value="0">Select Employee</option>
                <?php while ($emp = $employees->fetch_assoc()): ?>
                    <option value="<?php echo $emp['id']; ?>"><?php echo htmlspecialchars($emp['name']); ?></option>
                <?php endwhile; ?>
            </select>

            <label>Note</label>
            <textarea name="note" rows="4"></textarea>

            <button type="submit">Add Expense</button>
        </form>
        <p><a href="index.php">Back to Expenses</a></p>
    </div>
</body>
</html>
