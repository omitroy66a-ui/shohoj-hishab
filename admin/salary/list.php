<?php
require_once "../../config/database.php";
require_once "../../modules/business/middleware.php";

$business_id = businessId();

// Get salary payments
$res = $conn->query("
    SELECT sp.*, e.name as employee_name
    FROM salary_payments sp
    JOIN employees e ON e.id = sp.employee_id
    WHERE sp.business_id='$business_id'
    ORDER BY sp.paid_date DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Salary Payments</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
        .container { max-width: 1000px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #007bff; color: white; }
        .btn { display: inline-block; padding: 10px 16px; background: #007bff; color: #fff; text-decoration: none; border-radius: 4px; margin-bottom: 15px; }
        .total { background: #f1f1f1; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Salary Payments</h1>
        <a href="add.php" class="btn">+ Pay Salary</a>
        
        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Amount</th>
                    <th>Month</th>
                    <th>Paid Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $total = 0; while($row = $res->fetch_assoc()): $total += $row['amount']; ?>
                <tr>
                    <td><?= htmlspecialchars($row['employee_name']) ?></td>
                    <td><?= number_format($row['amount'], 2) ?></td>
                    <td><?= htmlspecialchars($row['month']) ?></td>
                    <td><?= htmlspecialchars($row['paid_date']) ?></td>
                    <td>
                        <a href="delete.php?id=<?= htmlspecialchars($row['id']) ?>" onclick="return confirm('Delete this payment?');">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <tr class="total">
                    <td colspan="4">Total Salary Paid:</td>
                    <td><?= number_format($total, 2) ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
