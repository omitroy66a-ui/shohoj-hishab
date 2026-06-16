<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

$assets = $conn->query("SELECT IFNULL(SUM(amount),0) AS total FROM cashbook WHERE type='income'")->fetch_assoc()['total'];
$liabilities = $conn->query("SELECT IFNULL(SUM(amount),0) AS total FROM cashbook WHERE type='expense'")->fetch_assoc()['total'];
$receivables = $conn->query("SELECT IFNULL(SUM(debit-credit),0) AS total FROM customer_ledger")->fetch_assoc()['total'];
$payables = $conn->query("SELECT IFNULL(SUM(debit-credit),0) AS total FROM supplier_ledger")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Trial Balance - Sohoj Hishab</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; padding:30px; background:#f4f4f4; }
        .container { max-width:900px; margin:0 auto; background:#fff; padding:20px; border-radius:8px; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:10px; border:1px solid #ddd; }
        th { background:#f1f1f1; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Trial Balance</h1>
        <table>
            <thead>
                <tr><th>Account</th><th>Debit</th><th>Credit</th></tr>
            </thead>
            <tbody>
                <tr><td>Cash / Bank</td><td><?php echo number_format($assets,2); ?></td><td></td></tr>
                <tr><td>Customer Receivables</td><td><?php echo number_format($receivables,2); ?></td><td></td></tr>
                <tr><td>Supplier Payables</td><td></td><td><?php echo number_format($payables,2); ?></td></tr>
                <tr><td>Expenses</td><td></td><td><?php echo number_format($liabilities,2); ?></td></tr>
            </tbody>
            <tfoot>
                <tr><th>Total</th><th><?php echo number_format($assets + $receivables,2); ?></th><th><?php echo number_format($liabilities + $payables,2); ?></th></tr>
            </tfoot>
        </table>
    </div>
</body>
</html>
