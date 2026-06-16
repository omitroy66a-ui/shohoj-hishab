<?php
require_once "../../config/database.php";
require_once "../../modules/business/middleware.php";

$business_id = businessId();
$employees = $conn->query("SELECT id, name, salary FROM employees WHERE business_id='$business_id' ORDER BY name ASC");
$error = '';
$success = '';

if($_POST) {
    $employee_id = (int)($_POST['employee_id'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);
    $month = $conn->real_escape_string($_POST['month'] ?? '');
    
    if(!$employee_id) {
        $error = "Please select an employee";
    } elseif($amount <= 0) {
        $error = "Amount must be greater than 0";
    } elseif(empty($month)) {
        $error = "Month is required";
    } else {
        $conn->query("
            INSERT INTO salary_payments(employee_id, amount, month, paid_date, business_id)
            VALUES('$employee_id', '$amount', '$month', CURDATE(), '$business_id')
        ");
        
        // Add to cashbook
        require_once "../../modules/accounting/cashbook.php";
        addCashEntry($conn, $business_id, 'expense', $amount, "Salary Payment - $month", 'salary', $employee_id);
        
        $success = "Salary payment recorded successfully";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pay Salary</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; }
        label { display: block; margin: 15px 0 5px 0; font-weight: bold; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 15px; box-sizing: border-box; }
        button { padding: 12px 18px; background: #007bff; color: #fff; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        .error { color: red; padding: 10px; background: #ffe6e6; border-radius: 4px; margin-bottom: 15px; }
        .success { color: green; padding: 10px; background: #e6ffe6; border-radius: 4px; margin-bottom: 15px; }
        a { color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Pay Salary</h1>
        
        <?php if($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <form method="post">
            <label>Employee</label>
            <select name="employee_id" required>
                <option value="">Select Employee</option>
                <?php while($emp = $employees->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($emp['id']) ?>">
                    <?= htmlspecialchars($emp['name']) ?> (৳<?= number_format($emp['salary'], 2) ?>)
                </option>
                <?php endwhile; ?>
            </select>
            
            <label>Amount</label>
            <input type="number" step="0.01" name="amount" placeholder="Salary Amount" required>
            
            <label>Month (e.g., January 2026)</label>
            <input type="text" name="month" placeholder="January 2026" required>
            
            <button type="submit">Record Salary Payment</button>
            <p><a href="list.php">Back to Salary Payments</a></p>
        </form>
    </div>
</body>
</html>
