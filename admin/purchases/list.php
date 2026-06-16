<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

$result = $conn->query("SELECT purchases.*, suppliers.name AS supplier_name FROM purchases LEFT JOIN suppliers ON suppliers.id = purchases.supplier_id ORDER BY purchases.id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase List - Sohoj Hishab</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; padding:30px; background:#f4f4f4; }
        .container { max-width:1100px; margin:0 auto; background:#fff; padding:20px; border-radius:8px; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:10px; border:1px solid #ddd; }
        th { background:#f1f1f1; }
        a.button { display:inline-block; margin-bottom:15px; padding:10px 16px; background:#007bff; color:#fff; border-radius:4px; text-decoration:none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Purchase List</h1>
        <a class="button" href="add.php">Add New Purchase</a>
        <table>
            <thead>
                <tr><th>#</th><th>Invoice</th><th>Supplier</th><th>Grand Total</th><th>Paid</th><th>Due</th><th>Date</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php while($purchase = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $purchase['id']; ?></td>
                        <td><?php echo htmlspecialchars($purchase['invoice_no']); ?></td>
                        <td><?php echo htmlspecialchars($purchase['supplier_name'] ?? 'Unknown'); ?></td>
                        <td><?php echo number_format($purchase['grand_total'],2); ?></td>
                        <td><?php echo number_format($purchase['paid'],2); ?></td>
                        <td><?php echo number_format($purchase['due'],2); ?></td>
                        <td><?php echo $purchase['created_at']; ?></td>
                        <td><a href="invoice.php?id=<?php echo $purchase['id']; ?>">View</a></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
