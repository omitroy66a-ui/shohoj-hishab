<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$purchase = null;
if ($id > 0) {
    $purchase = $conn->query("SELECT purchases.*, suppliers.name AS supplier_name FROM purchases LEFT JOIN suppliers ON suppliers.id = purchases.supplier_id WHERE purchases.id=$id")->fetch_assoc();
}
if (!$purchase) {
    echo 'Purchase not found.';
    exit;
}
$items = $conn->query("SELECT purchase_items.*, products.name AS product_name FROM purchase_items LEFT JOIN products ON products.id = purchase_items.product_id WHERE purchase_items.purchase_id=$id")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Invoice #<?php echo htmlspecialchars($purchase['invoice_no']); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; padding:30px; background:#f4f4f4; }
        .invoice { max-width:800px; margin:0 auto; background:#fff; padding:20px; border-radius:8px; }
        table { width:100%; border-collapse:collapse; margin-top:20px; }
        th, td { padding:10px; border:1px solid #ddd; }
        th { background:#f9f9f9; }
    </style>
</head>
<body>
    <div class="invoice">
        <h1>Purchase Invoice</h1>
        <p><strong>Invoice:</strong> <?php echo htmlspecialchars($purchase['invoice_no']); ?></p>
        <p><strong>Supplier:</strong> <?php echo htmlspecialchars($purchase['supplier_name'] ?? 'Unknown'); ?></p>
        <table>
            <thead>
                <tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td style="text-align:right;"><?php echo $item['qty']; ?></td>
                        <td style="text-align:right;"><?php echo number_format($item['price'],2); ?></td>
                        <td style="text-align:right;"><?php echo number_format($item['subtotal'],2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p><strong>Subtotal:</strong> <?php echo number_format($purchase['subtotal'],2); ?></p>
        <p><strong>Discount:</strong> <?php echo number_format($purchase['discount'],2); ?></p>
        <p><strong>Grand Total:</strong> <?php echo number_format($purchase['grand_total'],2); ?></p>
        <p><strong>Paid:</strong> <?php echo number_format($purchase['paid'],2); ?></p>
        <p><strong>Due:</strong> <?php echo number_format($purchase['due'],2); ?></p>
        <p><a href="list.php">Back to Purchase List</a></p>
    </div>
</body>
</html>
