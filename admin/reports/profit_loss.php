<?php
require_once "../../config/database.php";
require_once "../../modules/business/middleware.php";
require_once "../../modules/accounting/profit.php";

$business_id = businessId();
$pnl = calculateProfit($conn, $business_id);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Profit & Loss Statement</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
        .container { max-width: 800px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; }
        .pnl-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .pnl-table tr { border-bottom: 1px solid #ddd; }
        .pnl-table td { padding: 12px; }
        .label { font-weight: bold; width: 60%; }
        .amount { text-align: right; width: 40%; }
        .section-header { background: #f1f1f1; font-weight: bold; }
        .total-line { border-top: 2px solid #000; border-bottom: 2px solid #000; }
        .profit { background: #d4edda; color: #155724; }
        .loss { background: #f8d7da; color: #721c24; }
        h1 { text-align: center; }
        a { color: #007bff; text-decoration: none; display: inline-block; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Profit & Loss Statement</h1>
        <p style="text-align: center; color: #666;">Period: This Month</p>
        
        <table class="pnl-table">
            <tr>
                <td class="label section-header">Revenue</td>
                <td class="amount section-header"></td>
            </tr>
            <tr>
                <td class="label">Sales Revenue</td>
                <td class="amount">৳<?= number_format($pnl['sales'], 2) ?></td>
            </tr>
            
            <tr>
                <td class="label section-header">Cost of Goods Sold</td>
                <td class="amount section-header"></td>
            </tr>
            <tr>
                <td class="label">Purchases</td>
                <td class="amount">৳<?= number_format($pnl['purchases'], 2) ?></td>
            </tr>
            
            <tr>
                <td class="label section-header">Operating Expenses</td>
                <td class="amount section-header"></td>
            </tr>
            <tr>
                <td class="label">Expenses</td>
                <td class="amount">৳<?= number_format($pnl['expenses'], 2) ?></td>
            </tr>
            <tr>
                <td class="label">Salaries</td>
                <td class="amount">৳<?= number_format($pnl['salaries'], 2) ?></td>
            </tr>
            
            <tr>
                <td class="label">Total Costs & Expenses</td>
                <td class="amount">৳<?= number_format($pnl['total_costs'], 2) ?></td>
            </tr>
            
            <tr class="total-line <?= $pnl['profit'] >= 0 ? 'profit' : 'loss' ?>">
                <td class="label">Net Profit/Loss</td>
                <td class="amount">৳<?= number_format($pnl['profit'], 2) ?></td>
            </tr>
            
            <tr>
                <td class="label">Profit Margin</td>
                <td class="amount"><?= number_format($pnl['profit_percentage'], 2) ?>%</td>
            </tr>
        </table>
        
        <a href="../accounts/index.php">Back to Accounts</a>
    </div>
</body>
</html>
