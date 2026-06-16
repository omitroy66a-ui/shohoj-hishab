<?php
require_once __DIR__ . '/../../config/auth.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Accounts - Sohoj Hishab</title>
    <style>
        body { font-family: Arial, sans-serif; padding:30px; background:#f4f4f4; }
        .container { max-width:800px; margin:0 auto; background:#fff; padding:20px; border-radius:8px; }
        .nav-list { list-style:none; padding:0; }
        .nav-list li { margin:12px 0; }
        .nav-list a { text-decoration:none; color:#007bff; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Accounts</h1>
        <ul class="nav-list">
            <li><a href="cashbook.php">Cashbook</a></li>
            <li><a href="ledger.php">General Ledger</a></li>
            <li><a href="trial_balance.php">Trial Balance</a></li>
            <li><a href="profit_loss.php">Profit & Loss</a></li>
            <li><a href="balance_sheet.php">Balance Sheet</a></li>
        </ul>
    </div>
</body>
</html>
