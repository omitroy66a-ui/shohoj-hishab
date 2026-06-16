<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$sale = null;
if($id > 0){
    $sale = $conn->query("SELECT sales.*, customers.name AS customer_name FROM sales LEFT JOIN customers ON customers.id = sales.customer_id WHERE sales.id=$id")->fetch_assoc();
}
if(!$sale){
    echo 'Sale not found.';
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Print Invoice #<?php echo htmlspecialchars($sale['invoice_no']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; }
        .invoice { max-width: 700px; margin: 0 auto; }
        .invoice h1 { margin-bottom: 0; }
        .invoice-header { display: flex; justify-content: space-between; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 8px; border-bottom: 1px solid #ddd; }
        .print-button { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="invoice">
        <button class="print-button" onclick="window.print();">Print Invoice</button>
        <div class="invoice-header">
            <div>
                <h1>Invoice</h1>
                <p>Invoice #: <?php echo htmlspecialchars($sale['invoice_no']); ?></p>
            </div>
            <div>
                <p>Date: <?php echo htmlspecialchars($sale['created_at']); ?></p>
            </div>
        </div>
        <table>
            <tr><td>Customer Name</td><td><?php echo htmlspecialchars($sale['customer_name'] ?? 'Walk-in'); ?></td></tr>
            <tr><td>Total</td><td><?php echo htmlspecialchars($sale['total']); ?></td></tr>
            <tr><td>Discount</td><td><?php echo htmlspecialchars($sale['discount']); ?></td></tr>
            <tr><td>Paid</td><td><?php echo htmlspecialchars($sale['paid']); ?></td></tr>
            <tr><td>Due</td><td><?php echo htmlspecialchars($sale['due']); ?></td></tr>
            <tr><td>Payment Method</td><td><?php echo htmlspecialchars($sale['payment_method']); ?></td></tr>
        </table>
    </div>
</body>
</html>
