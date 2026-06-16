<?php
require_once "../../config/database.php";
require_once "../../modules/business/middleware.php";
require_once "../../modules/accounting/cashbook.php";

$business_id = businessId();
$res = $conn->query("SELECT * FROM cashbook WHERE business_id='$business_id' ORDER BY created_at DESC LIMIT 100");
$summary = getCashSummary($conn, $business_id);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cashbook</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
        .container { max-width: 1200px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; }
        .summary { display: flex; gap: 20px; margin-bottom: 20px; }
        .summary-card { flex: 1; padding: 15px; border-radius: 4px; color: white; }
        .income { background: #28a745; }
        .expense { background: #dc3545; }
        .balance { background: #007bff; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #f1f1f1; }
        .debit { color: #28a745; }
        .credit { color: #dc3545; }
        .btn { display: inline-block; padding: 10px 16px; background: #007bff; color: #fff; text-decoration: none; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Cashbook</h1>
        
        <div class="summary">
            <div class="summary-card income">
                <h3>Income</h3>
                <p>৳<?= number_format($summary['income'], 2) ?></p>
            </div>
            <div class="summary-card expense">
                <h3>Expense</h3>
                <p>৳<?= number_format($summary['expense'], 2) ?></p>
            </div>
            <div class="summary-card balance">
                <h3>Balance</h3>
                <p>৳<?= number_format($summary['balance'], 2) ?></p>
            </div>
        </div>
        
        <a href="add.php" class="btn">+ Add Entry</a>
        
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Note</th>
                    <th>Reference</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $res->fetch_assoc()): ?>
                <tr>
                    <td><span class="<?= $row['type'] === 'income' ? 'debit' : 'credit' ?>"><?= ucfirst($row['type']) ?></span></td>
                    <td><?= number_format($row['amount'], 2) ?></td>
                    <td><?= htmlspecialchars($row['note']) ?></td>
                    <td><?= htmlspecialchars($row['ref_type']) ?> (#<?= $row['ref_id'] ?>)</td>
                    <td><?= $row['created_at'] ?></td>
                    <td>
                        <a href="delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this entry?');">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
