<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$sale = null;
if($id > 0){
    $sale = $conn->query("SELECT sales.*, customers.name AS customer_name FROM sales LEFT JOIN customers ON customers.id = sales.customer_id WHERE sales.id=$id")->fetch_assoc();
    if ($sale) {
        $sale_items = $conn->query("SELECT sale_items.*, products.name AS product_name FROM sale_items LEFT JOIN products ON products.id = sale_items.product_id WHERE sale_items.sale_id=$id")->fetch_all(MYSQLI_ASSOC);
    }
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
    <title>Thermal Invoice #<?php echo htmlspecialchars($sale['invoice_no']); ?></title>
    <style>
        body { font-family: monospace; padding: 10px; background: #fff; color: #000; }
        .receipt { width: 58mm; max-width: 100%; margin: 0 auto; border: 1px dashed #000; padding: 10px; }
        .receipt h2 { margin: 0 0 10px; text-align: center; }
        .line { border-bottom: 1px dashed #000; margin: 10px 0; }
        .text-center { text-align: center; }
        .item-row { display: flex; justify-content: space-between; margin-bottom: 4px; }
        .item-row span { font-size: 12px; }
    </style>
</head>
<body>
    <div class="receipt">
        <h2>Sohoj Hishab</h2>
        <p class="text-center">Invoice #: <?php echo htmlspecialchars($sale['invoice_no']); ?></p>
        <p>Customer: <?php echo htmlspecialchars($sale['customer_name'] ?? 'Walk-in'); ?></p>
        <div class="line"></div>
        <?php if (!empty($sale_items)): ?>
            <?php foreach ($sale_items as $item): ?>
                <div class="item-row">
                    <span><?php echo htmlspecialchars($item['product_name'] ?? 'Item'); ?> x<?php echo htmlspecialchars($item['qty']); ?></span>
                    <span><?php echo htmlspecialchars($item['subtotal']); ?></span>
                </div>
            <?php endforeach; ?>
            <div class="line"></div>
        <?php endif; ?>
        <p>Total: <?php echo htmlspecialchars($sale['grand_total']); ?></p>
        <p>Paid: <?php echo htmlspecialchars($sale['paid']); ?></p>
        <p>Due: <?php echo htmlspecialchars($sale['due']); ?></p>
        <p>Method: <?php echo htmlspecialchars($sale['payment_method']); ?></p>
        <div class="line"></div>
        <p class="text-center">Thank you!</p>
    </div>
</body>
</html>
