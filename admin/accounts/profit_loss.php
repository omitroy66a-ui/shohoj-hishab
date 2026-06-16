<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

$total_sales = $conn->query("SELECT IFNULL(SUM(grand_total),0) AS total FROM sales")->fetch_assoc()['total'];
$total_purchase = $conn->query("SELECT IFNULL(SUM(total_amount),0) AS total FROM purchases")->fetch_assoc()['total'];
$total_expense = $conn->query("SELECT IFNULL(SUM(amount),0) AS total FROM cashbook WHERE type='expense'")->fetch_assoc()['total'];
$profit = $total_sales - ($total_purchase + $total_expense);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Profit & Loss - Sohoj Hishab</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; padding:30px; background:#f4f4f4; }
        .container { max-width:700px; margin:0 auto; background:#fff; padding:20px; border-radius:8px; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:10px; border:1px solid #ddd; }
        th { background:#f1f1f1; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Profit & Loss Statement</h1>
        <table>
            <tbody>
                <tr><th>Total Sales</th><td><?php echo number_format($total_sales,2); ?></td></tr>
                <tr><th>Total Purchase</th><td><?php echo number_format($total_purchase,2); ?></td></tr>
                <tr><th>Total Expense</th><td><?php echo number_format($total_expense,2); ?></td></tr>
                <tr><th>Net Profit</th><td><?php echo number_format($profit,2); ?></td></tr>
            </tbody>
        </table>
    </div>
</body>
</html>
