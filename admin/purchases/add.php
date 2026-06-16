<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
session_start();

$suppliers = $conn->query("SELECT * FROM suppliers ORDER BY name ASC");
$products = $conn->query("SELECT * FROM products ORDER BY name ASC");
$last = $conn->query("SELECT id FROM purchases ORDER BY id DESC LIMIT 1")->fetch_assoc();
$next = isset($last['id']) ? $last['id'] + 1 : 1;
$invoice_no = "PUR-" . str_pad($next, 5, "0", STR_PAD_LEFT);
$cartItems = $_SESSION['purchase_cart'] ?? [];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Add Purchase - Sohoj Hishab</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; background:#f4f4f4; }
        .container { max-width: 900px; margin:0 auto; background:#fff; padding:20px; border-radius:8px; }
        .field-row { display:grid; grid-template-columns:repeat(2,1fr); gap:20px; }
        label { display:block; margin:10px 0 5px; }
        input, select { width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; }
        button { padding:12px 18px; border:none; border-radius:4px; color:#fff; background:#007bff; cursor:pointer; }
        table { width:100%; border-collapse:collapse; margin-top:20px; }
        th, td { border:1px solid #ddd; padding:10px; }
        th { background:#f1f1f1; }
    </style>
    <script>
        function updateProductDetails() {
            const productSelect = document.getElementById('product_id');
            const selected = productSelect.options[productSelect.selectedIndex];
            document.getElementById('price').value = selected.dataset.price || '';
            document.getElementById('stock_info').textContent = selected.dataset.stock ? 'Current stock: ' + selected.dataset.stock : '';
            updateSubtotal();
        }
        function updateSubtotal() {
            const qty = parseFloat(document.getElementById('qty').value) || 0;
            const price = parseFloat(document.getElementById('price').value) || 0;
            document.getElementById('amount').value = (qty * price).toFixed(2);
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Add Purchase</h1>
        <form method="post" action="save_purchase.php">
            <div class="field-row">
                <div>
                    <label for="supplier_id">Supplier</label>
                    <select id="supplier_id" name="supplier_id" required>
                        <option value="">Select Supplier</option>
                        <?php while($supplier = $suppliers->fetch_assoc()): ?>
                            <option value="<?php echo $supplier['id']; ?>"><?php echo htmlspecialchars($supplier['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label for="invoice_no">Invoice Number</label>
                    <input type="text" id="invoice_no" name="invoice_no" value="<?php echo $invoice_no; ?>" readonly>
                </div>
            </div>
            <div class="field-row">
                <div>
                    <label for="product_id">Product</label>
                    <select id="product_id" name="product_id" onchange="updateProductDetails()" required>
                        <option value="">Select Product</option>
                        <?php while($product = $products->fetch_assoc()): ?>
                            <option value="<?php echo $product['id']; ?>" data-price="<?php echo $product['purchase_price']; ?>" data-stock="<?php echo $product['stock']; ?>"><?php echo htmlspecialchars($product['name']); ?> (Stock: <?php echo $product['stock']; ?>)</option>
                        <?php endwhile; ?>
                    </select>
                    <small id="stock_info"></small>
                </div>
                <div>
                    <label for="qty">Qty</label>
                    <input type="number" id="qty" name="qty" value="1" min="1" onchange="updateSubtotal()" required>
                </div>
            </div>
            <div class="field-row">
                <div>
                    <label for="price">Purchase Price</label>
                    <input type="number" step="0.01" id="price" name="price" onchange="updateSubtotal()" required>
                </div>
                <div>
                    <label for="amount">Subtotal</label>
                    <input type="number" step="0.01" id="amount" name="amount" value="0.00" readonly>
                </div>
            </div>
            <div class="field-row">
                <div>
                    <label for="discount">Discount</label>
                    <input type="number" step="0.01" id="discount" name="discount" value="0">
                </div>
                <div>
                    <label for="paid">Paid Amount</label>
                    <input type="number" step="0.01" id="paid" name="paid" value="0" required>
                </div>
            </div>
            <div style="margin-top:20px; display:flex; gap:10px; flex-wrap:wrap;">
                <button type="submit" name="add_to_cart">Add to Cart</button>
                <button type="submit" name="save_purchase">Save Purchase</button>
                <button type="submit" name="clear_cart" formaction="save_purchase.php">Clear Cart</button>
            </div>
        </form>

        <?php if(!empty($cartItems)): ?>
            <h2 style="margin-top:30px;">Purchase Cart</h2>
            <table>
                <thead>
                    <tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr>
                </thead>
                <tbody>
                    <?php $total = 0; foreach($cartItems as $item): $total += $item['subtotal']; ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td style="text-align:right;"><?php echo $item['qty']; ?></td>
                            <td style="text-align:right;"><?php echo number_format($item['price'],2); ?></td>
                            <td style="text-align:right;"><?php echo number_format($item['subtotal'],2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align:right;"><strong>Total</strong></td>
                        <td style="text-align:right;"><strong><?php echo number_format($total,2); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        <?php endif; ?>

        <p><a href="list.php">View Purchases</a></p>
    </div>
</body>
</html>
