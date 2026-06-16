<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
session_start();

$customers = $conn->query("SELECT * FROM customers ORDER BY name ASC");
$products = $conn->query("SELECT * FROM products WHERE stock > 0 ORDER BY name ASC");

$last = $conn->query("SELECT id FROM sales ORDER BY id DESC LIMIT 1")->fetch_assoc();
$next = isset($last['id']) ? $last['id'] + 1 : 1;
$invoice_no = "INV-" . str_pad($next, 5, "0", STR_PAD_LEFT);
$cartItems = $_SESSION['cart'] ?? [];
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>POS - Sohoj Hishab</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f9f9f9; padding: 30px; }
        .container { max-width: 900px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 6px; box-shadow: 0 0 10px rgba(0,0,0,0.08); }
        label { display:block; margin: 15px 0 5px; color:#333; }
        select, input { width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; }
        button { margin-top:20px; padding:12px 18px; background:#007bff; color:#fff; border:none; border-radius:4px; cursor:pointer; }
        .field-row { display: grid; grid-template-columns: repeat(2, 1fr); gap:20px; }
        .inline-text { margin-top: 6px; color: #555; font-size: 13px; }
        a { color:#007bff; text-decoration:none; }
        table { width:100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border:1px solid #ddd; padding:8px; }
        th { background:#f1f1f1; }
    </style>
    <script>
        function updateProductDetails() {
            const productSelect = document.getElementById('product_id');
            const selected = productSelect.options[productSelect.selectedIndex];
            const priceInput = document.getElementById('price');
            const stockInfo = document.getElementById('stock_info');
            priceInput.value = selected.dataset.price || '';
            stockInfo.textContent = selected.dataset.stock ? 'Stock: ' + selected.dataset.stock : '';
            updateSubtotal();
        }
        function updateSubtotal() {
            const qty = parseFloat(document.getElementById('qty').value) || 0;
            const price = parseFloat(document.getElementById('price').value) || 0;
            const amount = qty * price;
            document.getElementById('amount').value = amount.toFixed(2);
        }
        let searchProductMap = {};
        function searchProducts() {
            const query = document.getElementById('product_search').value.trim();
            if (!query) {
                return;
            }
            fetch('search_products.php?q=' + encodeURIComponent(query))
                .then(res => res.json())
                .then(data => {
                    const datalist = document.getElementById('product_list');
                    datalist.innerHTML = '';
                    searchProductMap = {};
                    data.forEach(product => {
                        const option = document.createElement('option');
                        option.value = product.name;
                        option.dataset.id = product.id;
                        option.dataset.price = product.sale_price;
                        option.dataset.stock = product.stock;
                        datalist.appendChild(option);
                        searchProductMap[product.name] = product;
                    });
                });
        }
        function selectProductByName(name) {
            if (!searchProductMap[name]) {
                return;
            }
            const product = searchProductMap[name];
            const productSelect = document.getElementById('product_id');
            for (let i = 0; i < productSelect.options.length; i++) {
                if (parseInt(productSelect.options[i].value) === product.id) {
                    productSelect.selectedIndex = i;
                    updateProductDetails();
                    return;
                }
            }
            const option = document.createElement('option');
            option.value = product.id;
            option.text = product.name + ' (Stock: ' + product.stock + ')';
            option.dataset.price = product.sale_price;
            option.dataset.stock = product.stock;
            productSelect.appendChild(option);
            productSelect.value = product.id;
            updateProductDetails();
        }
        function searchBarcode() {
            const barcode = document.getElementById('barcode').value.trim();
            if (!barcode) return;
            fetch('search_products.php?barcode=' + encodeURIComponent(barcode))
                .then(res => res.json())
                .then(data => {
                    if (!data || data.length === 0) {
                        alert('Product not found for this barcode.');
                        return;
                    }
                    const product = data[0];
                    const productSelect = document.getElementById('product_id');
                    for (let i = 0; i < productSelect.options.length; i++) {
                        if (parseInt(productSelect.options[i].value) === product.id) {
                            productSelect.selectedIndex = i;
                            updateProductDetails();
                            return;
                        }
                    }
                    const option = document.createElement('option');
                    option.value = product.id;
                    option.text = product.name + ' (Stock: ' + product.stock + ')';
                    option.dataset.price = product.sale_price;
                    option.dataset.stock = product.stock;
                    productSelect.appendChild(option);
                    productSelect.value = product.id;
                    updateProductDetails();
                });
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Point of Sale</h1>
        <form method="post" action="save_sale.php">
            <div class="field-row">
                <div>
                    <label for="customer_id">Customer</label>
                    <select id="customer_id" name="customer_id" required>
                        <option value="0">Walk In Customer</option>
                        <?php while($customer = $customers->fetch_assoc()): ?>
                            <option value="<?php echo $customer['id']; ?>"><?php echo htmlspecialchars($customer['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label for="payment_method">Payment Method</label>
                    <select id="payment_method" name="payment_method" required>
                        <option value="cash">Cash</option>
                        <option value="bkash">Bkash</option>
                        <option value="nagad">Nagad</option>
                        <option value="bank">Bank</option>
                    </select>
                </div>
            </div>
            <div class="field-row">
                <div>
                    <label for="product_search">Search Product</label>
                    <input type="text" id="product_search" placeholder="Type product name or barcode" oninput="searchProducts()" onchange="selectProductByName(this.value)" autocomplete="off" list="product_list">
                    <datalist id="product_list"></datalist>
                </div>
                <div>
                    <label for="barcode">Scan Barcode</label>
                    <div style="display:flex; gap:10px;">
                        <input type="text" id="barcode" placeholder="Barcode" style="flex:1;" />
                        <button type="button" onclick="searchBarcode()" style="flex:0 0 auto;">Find</button>
                    </div>
                </div>
            </div>
            <div class="field-row">
                <div>
                    <label for="product_id">Product</label>
                    <select id="product_id" name="product_id" onchange="updateProductDetails()">
                        <option value="">Select Product</option>
                        <?php while($product = $products->fetch_assoc()): ?>
                            <option value="<?php echo $product['id']; ?>" data-price="<?php echo $product['sale_price']; ?>" data-stock="<?php echo $product['stock']; ?>"><?php echo htmlspecialchars($product['name']); ?> (Stock: <?php echo $product['stock']; ?>)</option>
                        <?php endwhile; ?>
                    </select>
                    <div class="inline-text" id="stock_info"></div>
                </div>
                <div>
                    <label for="qty">Qty</label>
                    <input type="number" id="qty" name="qty" value="1" min="1" onchange="updateSubtotal()" required>
                </div>
            </div>
            <div class="field-row">
                <div>
                    <label for="price">Price</label>
                    <input type="number" step="0.01" id="price" name="price" onchange="updateSubtotal()">
                </div>
                <div>
                    <label for="amount">Subtotal</label>
                    <input type="number" step="0.01" id="amount" name="amount" value="0.00" readonly>
                </div>
            </div>
            <div class="field-row">
                <div>
                    <label for="discount">Discount</label>
                    <input type="number" step="0.01" id="discount" name="discount" value="0" onchange="updateSubtotal()">
                </div>
                <div>
                    <label for="paid">Paid Amount</label>
                    <input type="number" step="0.01" id="paid" name="paid" value="0" required>
                </div>
            </div>
            <label for="invoice_no">Invoice Number</label>
            <input type="text" id="invoice_no" name="invoice_no" value="<?php echo $invoice_no; ?>" readonly required>
            <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:20px;">
                <button type="submit" name="add_to_cart" style="background:#28a745;">Add to Cart</button>
                <button type="submit" name="save_sale">Save Sale</button>
                <button type="submit" name="clear_cart" style="background:#dc3545;">Clear Cart</button>
            </div>
        </form>

        <?php if (!empty($cartItems)): ?>
            <h2 style="margin-top:30px;">Cart</h2>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th style="text-align:right;">Qty</th>
                        <th style="text-align:right;">Price</th>
                        <th style="text-align:right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $cart_total = 0; foreach($cartItems as $item): $cart_total += $item['subtotal']; ?>
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
                        <td style="text-align:right;"><strong><?php echo number_format($cart_total,2); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        <?php endif; ?>

        <p style="margin-top: 20px;"><a href="history.php">View Sale History</a></p>
    </div>
</body>
</html>
