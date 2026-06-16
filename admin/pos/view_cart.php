<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../modules/business/middleware.php';

$business_id = businessId();

if (!$business_id) {
    http_response_code(401);
    exit('Unauthorized');
}

$cart = $_SESSION['cart'] ?? [];
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['total'];
}

$discount = 0;
$grand_total = $subtotal - $discount;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - POS</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
        .container { max-width: 1200px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #007bff; color: #fff; }
        tr:hover { background: #f9f9f9; }
        .summary { background: #f9f9f9; padding: 20px; border-radius: 8px; margin-top: 20px; }
        .summary-row { display: flex; justify-content: space-between; margin: 10px 0; font-size: 18px; }
        .summary-row strong { min-width: 150px; }
        .total { font-size: 24px; font-weight: bold; color: #28a745; }
        .btn { padding: 10px 20px; margin: 5px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        .btn-danger { background: #dc3545; color: #fff; }
        .btn-primary { background: #007bff; color: #fff; }
        .btn-success { background: #28a745; color: #fff; }
        .btn:hover { opacity: 0.9; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🛒 Shopping Cart</h1>
        
        <?php if (empty($cart)): ?>
            <p style="text-align: center; font-size: 18px; color: #999;">Cart is empty</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart as $index => $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo $item['qty']; ?></td>
                            <td><?php echo number_format($item['total'], 2); ?></td>
                            <td>
                                <button class="btn btn-danger" onclick="removeItem(<?php echo $index; ?>)">Remove</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="summary">
                <div class="summary-row">
                    <strong>Subtotal:</strong>
                    <span><?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="summary-row">
                    <strong>Discount:</strong>
                    <input type="number" id="discount" value="0" step="0.01" style="width: 150px; padding: 5px;">
                </div>
                <div class="summary-row total">
                    <strong>Grand Total:</strong>
                    <span id="grand_total"><?php echo number_format($grand_total, 2); ?></span>
                </div>
            </div>

            <div style="text-align: right; margin-top: 20px;">
                <a href="index.php" class="btn btn-primary">← Continue Shopping</a>
                <button class="btn btn-success" onclick="proceedCheckout()">Checkout →</button>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function removeItem(index) {
            fetch('cart.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ index: index })
            }).then(() => location.reload());
        }

        function proceedCheckout() {
            const discount = parseFloat(document.getElementById('discount').value) || 0;
            window.location.href = 'checkout.php?discount=' + discount;
        }

        document.getElementById('discount').addEventListener('change', function() {
            const subtotal = <?php echo $subtotal; ?>;
            const discount = parseFloat(this.value) || 0;
            const grandTotal = subtotal - discount;
            document.getElementById('grand_total').textContent = grandTotal.toFixed(2);
        });
    </script>
</body>
</html>
