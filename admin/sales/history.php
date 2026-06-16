<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

$result = $conn->query("SELECT sales.*, customers.name AS customer_name FROM sales LEFT JOIN customers ON customers.id = sales.customer_id ORDER BY sales.id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sale History - Sohoj Hishab</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f9f9f9; padding: 30px; }
        .container { max-width: 1000px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 6px; box-shadow: 0 0 10px rgba(0,0,0,0.08); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #f4f4f4; }
        a { color: #007bff; text-decoration:none; }
        .actions a { margin-right: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Sale History</h1>
        <p><a href="pos.php">Create New Sale</a></p>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Invoice</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Paid</th>
                    <th>Due</th>
                    <th>Method</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['invoice_no']); ?></td>
                    <td><?php echo htmlspecialchars($row['customer_name'] ?? 'Unknown'); ?></td>
                    <td><?php echo htmlspecialchars($row['total']); ?></td>
                    <td><?php echo htmlspecialchars($row['paid']); ?></td>
                    <td><?php echo htmlspecialchars($row['due']); ?></td>
                    <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                    <td class="actions">
                        <a href="invoice.php?id=<?php echo $row['id']; ?>">Invoice</a>
                        <a href="print_invoice.php?id=<?php echo $row['id']; ?>">Print</a>
                        <a href="share_invoice.php?token=<?php echo urlencode($row['share_token']); ?>">Share</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
