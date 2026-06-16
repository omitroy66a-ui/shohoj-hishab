<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../modules/business/middleware.php';

$business_id = businessId();

if (!$business_id) {
    http_response_code(401);
    exit('Unauthorized');
}

// Get printer settings
$stmt = $conn->prepare("SELECT * FROM printer_settings WHERE business_id = ?");
$stmt->bind_param("i", $business_id);
$stmt->execute();
$settings = $stmt->get_result()->fetch_assoc() ?? ['shop_name' => 'POS System', 'thermal_size' => '58', 'page_size' => 'a4', 'language' => 'en'];

$cart = $_SESSION['cart'] ?? [];
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .container { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; padding: 20px; height: 100vh; }
        .left { background: #fff; border-radius: 8px; overflow: hidden; display: flex; flex-direction: column; }
        .right { background: #fff; border-radius: 8px; padding: 20px; overflow-y: auto; }
        
        .header { background: #007bff; color: #fff; padding: 15px 20px; }
        .header h1 { margin: 0; }
        
        .search-section { padding: 15px; border-bottom: 1px solid #ddd; }
        .search-section input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        
        .products-section { flex: 1; overflow-y: auto; padding: 15px; }
        .product-item { padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 10px; cursor: pointer; }
        .product-item:hover { background: #f9f9f9; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .product-name { font-weight: bold; }
        .product-info { font-size: 12px; color: #666; }
        
        .right h2 { margin-bottom: 15px; color: #333; }
        .cart-item { padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 10px; background: #f9f9f9; }
        .cart-item-header { display: flex; justify-content: space-between; align-items: center; }
        .remove-btn { background: #dc3545; color: #fff; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; }
        
        .summary { background: #f0f0f0; padding: 15px; border-radius: 4px; margin: 15px 0; }
        .summary-row { display: flex; justify-content: space-between; margin: 8px 0; }
        .total { font-weight: bold; font-size: 18px; color: #28a745; }
        
        .customer-section { background: #e7f3ff; padding: 15px; border-radius: 4px; margin-bottom: 15px; }
        .form-group { margin-bottom: 10px; }
        .form-group label { display: block; margin-bottom: 3px; font-size: 12px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px; }
        
        .note-section { background: #fff3cd; padding: 15px; border-radius: 4px; margin-bottom: 15px; border: 1px dashed #ffc107; }
        .note-section textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 3px; }
        
        .buttons { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 15px; }
        .btn { padding: 12px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-primary { background: #28a745; color: #fff; grid-column: 1 / -1; }
        .btn-secondary { background: #6c757d; color: #fff; }
        .btn-settings { background: #007bff; color: #fff; }
        .btn:hover { opacity: 0.9; }
        
        .stats { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px; }
        .stat-box { background: #f0f0f0; padding: 10px; border-radius: 4px; text-align: center; }
        .stat-label { font-size: 12px; color: #666; }
        .stat-value { font-weight: bold; font-size: 18px; color: #007bff; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Left Side: Product Search -->
        <div class="left">
            <div class="header">
                <h1>🛍️ POS System</h1>
            </div>
            
            <div class="search-section">
                <input type="text" id="barcode_input" placeholder="📱 Scan barcode or type product code..." autofocus>
            </div>
            
            <div class="products-section" id="products_list">
                <p style="text-align: center; color: #999;">Scan or search for products...</p>
            </div>
        </div>
        
        <!-- Right Side: Cart & Checkout -->
        <div class="right">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h2>🛒 Shopping Cart</h2>
                <a href="settings.php" style="text-decoration: none;" title="Printer Settings">
                    <button class="btn btn-settings" style="width: auto; padding: 8px 15px;">⚙️ Settings</button>
                </a>
            </div>
            
            <div class="stats">
                <div class="stat-box">
                    <div class="stat-label">Items</div>
                    <div class="stat-value" id="item_count"><?php echo count($cart); ?></div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">Subtotal</div>
                    <div class="stat-value">৳<?php echo number_format($subtotal, 0); ?></div>
                </div>
            </div>
            
            <!-- Customer Section -->
            <div class="customer-section">
                <div class="form-group">
                    <label>👤 Customer (Optional)</label>
                    <input type="text" id="customer_name" placeholder="Customer name">
                </div>
                <div class="form-group">
                    <label>📱 Phone (Optional)</label>
                    <input type="tel" id="customer_phone" placeholder="Mobile number">
                </div>
                <div class="form-group">
                    <label>📍 Address (Optional)</label>
                    <input type="text" id="customer_address" placeholder="Address">
                </div>
            </div>
            
            <!-- Note Section -->
            <div class="note-section">
                <label style="display: block; margin-bottom: 8px; font-weight: bold;">📝 Print Note</label>
                <textarea id="print_note" placeholder="Add any note to print on invoice..." rows="3"></textarea>
                <small style="display: block; margin-top: 5px; color: #666;">This note will appear on the thermal/PDF invoice</small>
            </div>
            
            <!-- Cart Items -->
            <div id="cart_items">
                <?php if (empty($cart)): ?>
                    <p style="text-align: center; color: #999;">Cart is empty</p>
                <?php else: ?>
                    <?php foreach ($cart as $index => $item): ?>
                        <div class="cart-item">
                            <div class="cart-item-header">
                                <span><strong><?php echo htmlspecialchars($item['name']); ?></strong> × <?php echo $item['qty']; ?></span>
                                <button class="remove-btn" onclick="removeItem(<?php echo $index; ?>)">Remove</button>
                            </div>
                            <div class="product-info">৳<?php echo number_format($item['price'], 0); ?> = ৳<?php echo number_format($item['total'], 0); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Summary -->
            <div class="summary">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span id="sub_total">৳<?php echo number_format($subtotal, 0); ?></span>
                </div>
                <div class="summary-row">
                    <label>Discount:</label>
                    <input type="number" id="discount" value="0" step="0.01" style="width: 100px; padding: 5px;">
                </div>
                <div class="summary-row total">
                    <span>TOTAL:</span>
                    <span id="grand_total">৳<?php echo number_format($subtotal, 0); ?></span>
                </div>
                <div class="summary-row">
                    <label>Paid:</label>
                    <input type="number" id="paid" value="<?php echo $subtotal; ?>" step="0.01" style="width: 100px; padding: 5px;">
                </div>
            </div>
            
            <!-- Buttons -->
            <div class="buttons">
                <button class="btn btn-settings" onclick="clearCart()">🗑️ Clear Cart</button>
                <button class="btn btn-settings" onclick="location.href='view_cart.php'">👁️ View Cart</button>
                <button class="btn btn-primary" onclick="checkout()">💰 Checkout & Print</button>
            </div>
        </div>
    </div>
    
    <script>
        const barcode_input = document.getElementById('barcode_input');
        const products_list = document.getElementById('products_list');
        const discount_input = document.getElementById('discount');
        const paid_input = document.getElementById('paid');
        const subtotal = <?php echo $subtotal; ?>;
        
        barcode_input.addEventListener('change', async function() {
            const barcode = this.value.trim();
            if (!barcode) return;
            
            const res = await fetch('search_product.php?barcode=' + encodeURIComponent(barcode));
            const data = await res.json();
            
            if (data.success) {
                await addToCart(data.data);
                this.value = '';
                this.focus();
            } else {
                alert('❌ Product not found: ' + barcode);
                this.value = '';
                this.focus();
            }
        });
        
        async function addToCart(product) {
            const res = await fetch('cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    product_id: product.id,
                    qty: 1
                })
            });
            
            const data = await res.json();
            if (data.success) {
                location.reload();
            } else {
                alert('❌ ' + data.error);
            }
        }
        
        function removeItem(index) {
            fetch('cart.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ index: index })
            }).then(() => location.reload());
        }
        
        function clearCart() {
            if (confirm('Clear entire cart?')) {
                fetch('cart.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ clear: true })
                }).then(() => location.reload());
            }
        }
        
        discount_input.addEventListener('change', updateTotal);
        paid_input.addEventListener('change', updateTotal);
        
        function updateTotal() {
            const discount = parseFloat(discount_input.value) || 0;
            const grandTotal = subtotal - discount;
            document.getElementById('grand_total').textContent = '৳' + grandTotal.toFixed(0);
        }
        
        async function checkout() {
            <?php if (empty($cart)): ?>
                alert('Cart is empty!');
                return;
            <?php endif; ?>
            
            const discount = parseFloat(document.getElementById('discount').value) || 0;
            const paid = parseFloat(document.getElementById('paid').value) || 0;
            const note = document.getElementById('print_note').value;
            
            const cart = <?php echo json_encode($cart); ?>;
            
            const res = await fetch('checkout.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    customer_id: 0,
                    subtotal: subtotal,
                    discount: discount,
                    paid: paid,
                    cart: cart
                })
            });
            
            const data = await res.json();
            if (data.success) {
                const size = '<?php echo $settings['thermal_size']; ?>';
                const lang = '<?php echo $settings['language']; ?>';
                window.open(`thermal_invoice.php?id=${data.sale_id}&size=${size}&lang=${lang}&note=${encodeURIComponent(note)}`, '_blank');
                location.reload();
            } else {
                alert('❌ Checkout failed: ' + data.error);
            }
        }
    </script>
</body>
</html>
