<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

$supplier_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$supplier = $conn->query("SELECT * FROM suppliers WHERE id=$supplier_id")->fetch_assoc();
if (!$supplier) {
    echo 'Supplier not found.';
    exit;
}
$result = $conn->query("SELECT * FROM supplier_ledger WHERE supplier_id=$supplier_id ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Supplier Ledger - Sohoj Hishab</title>
    <style>
        body { font-family: Arial, sans-serif; padding:30px; background:#f4f4f4; }
        .container { max-width:1000px; margin:0 auto; background:#fff; padding:20px; border-radius:8px; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:10px; border:1px solid #ddd; }
        th { background:#f1f1f1; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Supplier Ledger for <?php echo htmlspecialchars($supplier['name']); ?></h1>
        <table>
            <thead>
                <tr><th>Date</th><th>Purchase ID</th><th>Debit</th><th>Credit</th><th>Note</th></tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['created_at']; ?></td>
                        <td><?php echo $row['purchase_id']; ?></td>
                        <td><?php echo number_format($row['debit'],2); ?></td>
                        <td><?php echo number_format($row['credit'],2); ?></td>
                        <td><?php echo htmlspecialchars($row['note']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <p><a href="list.php">Back to Suppliers</a></p>
    </div>
</body>
</html>
