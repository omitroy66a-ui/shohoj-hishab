<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
session_start();

$customer_id = isset($_POST['customer_id']) ? (int) $_POST['customer_id'] : 0;
$product_id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
$qty = isset($_POST['qty']) ? (int) $_POST['qty'] : 1;
$price = isset($_POST['price']) ? (float) $_POST['price'] : 0;
$invoice_no = trim($_POST['invoice_no'] ?? '');
if ($invoice_no === '') {
    $last = $conn->query("SELECT id FROM sales ORDER BY id DESC LIMIT 1")->fetch_assoc();
    $next = isset($last['id']) ? $last['id'] + 1 : 1;
    $invoice_no = "INV-" . str_pad($next, 5, "0", STR_PAD_LEFT);
}
$invoice_no = $conn->real_escape_string($invoice_no);
$payment_method = $conn->real_escape_string(trim($_POST['payment_method'] ?? ''));
$discount = (float) ($_POST['discount'] ?? 0);
$paid = (float) ($_POST['paid'] ?? 0);
$share_token = md5(uniqid('sale_', true));

if (isset($_POST['clear_cart'])) {
    unset($_SESSION['cart']);
    header('Location: pos.php');
    exit;
}

if (isset($_POST['add_to_cart'])) {
    if ($product_id > 0 && $qty > 0 && $price >= 0) {
        $product = $conn->query("SELECT name, stock FROM products WHERE id=$product_id")->fetch_assoc();
        if ($product) {
            $_SESSION['cart'][] = [
                'product_id' => $product_id,
                'product_name' => $product['name'],
                'qty' => $qty,
                'price' => $price,
                'subtotal' => $qty * $price,
                'stock' => $product['stock']
            ];
        }
    }
    header('Location: pos.php');
    exit;
}

$cartItems = $_SESSION['cart'] ?? [];
$hasCart = !empty($cartItems);

if (isset($_POST['save_sale'])) {
    if ($hasCart) {
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += $item['subtotal'];
        }
        $grand_total = max(0, $subtotal - $discount);
        $due = max(0, $grand_total - $paid);

        $conn->query("INSERT INTO sales(customer_id, invoice_no, subtotal, discount, grand_total, paid, due, payment_method, share_token) VALUES($customer_id, '$invoice_no', '$subtotal', '$discount', '$grand_total', '$paid', '$due', '$payment_method', '$share_token')");
        $sale_id = $conn->insert_id;

        foreach ($cartItems as $item) {
            $prod_id = (int) $item['product_id'];
            $item_qty = (int) $item['qty'];
            $item_price = (float) $item['price'];
            $item_subtotal = (float) $item['subtotal'];
            $conn->query("INSERT INTO sale_items(sale_id, product_id, qty, price, subtotal) VALUES($sale_id, $prod_id, $item_qty, '$item_price', '$item_subtotal')");
            $conn->query("UPDATE products SET stock = stock - $item_qty WHERE id = $prod_id");
            if ($conn->query("SHOW TABLES LIKE 'stock_logs'")->num_rows > 0) {
                $conn->query("INSERT INTO stock_logs(product_id, change_qty, type, reference_id, note) VALUES($prod_id, -$item_qty, 'sale', $sale_id, 'Sale {$invoice_no}')");
            }
            if ($conn->query("SHOW TABLES LIKE 'profits'")->num_rows > 0) {
                $productCost = $conn->query("SELECT purchase_price FROM products WHERE id=$prod_id")->fetch_assoc()['purchase_price'];
                $costAmount = (float) $productCost * $item_qty;
                $profitAmount = $item_subtotal - $costAmount;
                $conn->query("INSERT INTO profits(sale_id, revenue, cost, profit_amount) VALUES($sale_id, '$item_subtotal', '$costAmount', '$profitAmount')");
            }
        }

        if ($customer_id > 0 && $due > 0) {
            $conn->query("INSERT INTO customer_ledger(customer_id, sale_id, debit, credit, note) VALUES($customer_id, $sale_id, '$due', 0, 'Sale Due')");
        }

        $conn->query("INSERT INTO payments(sale_id, amount, method, note) VALUES($sale_id, '$paid', '$payment_method', 'Sale Payment')");
        if ($conn->query("SHOW TABLES LIKE 'cashbook'")->num_rows > 0 && $paid > 0) {
            $conn->query("INSERT INTO cashbook(type, reference_type, reference_id, amount, note) VALUES('income', 'sale', $sale_id, '$paid', 'Sales Income')");
        }
        unset($_SESSION['cart']);
        header('Location: invoice.php?id=' . $sale_id);
        exit;
    }

    if ($product_id > 0) {
        $subtotal = $qty * $price;
        $grand_total = max(0, $subtotal - $discount);
        $due = max(0, $grand_total - $paid);

        $conn->query("INSERT INTO sales(customer_id, invoice_no, subtotal, discount, grand_total, paid, due, payment_method, share_token) VALUES($customer_id, '$invoice_no', '$subtotal', '$discount', '$grand_total', '$paid', '$due', '$payment_method', '$share_token')");
        $sale_id = $conn->insert_id;

        $conn->query("INSERT INTO sale_items(sale_id, product_id, qty, price, subtotal) VALUES($sale_id, $product_id, $qty, '$price', '$subtotal')");
        $conn->query("UPDATE products SET stock = stock - $qty WHERE id = $product_id");
        if ($conn->query("SHOW TABLES LIKE 'stock_logs'")->num_rows > 0) {
            $conn->query("INSERT INTO stock_logs(product_id, change_qty, type, reference_id, note) VALUES($product_id, -$qty, 'sale', $sale_id, 'Sale {$invoice_no}')");
        }
        if ($conn->query("SHOW TABLES LIKE 'profits'")->num_rows > 0) {
            $productCost = $conn->query("SELECT purchase_price FROM products WHERE id=$product_id")->fetch_assoc()['purchase_price'];
            $costAmount = (float) $productCost * $qty;
            $profitAmount = $subtotal - $costAmount;
            $conn->query("INSERT INTO profits(sale_id, revenue, cost, profit_amount) VALUES($sale_id, '$subtotal', '$costAmount', '$profitAmount')");
        }

        if ($customer_id > 0 && $due > 0) {
            $conn->query("INSERT INTO customer_ledger(customer_id, sale_id, debit, credit, note) VALUES($customer_id, $sale_id, '$due', 0, 'Sale Due')");
        }

        $conn->query("INSERT INTO payments(sale_id, amount, method, note) VALUES($sale_id, '$paid', '$payment_method', 'Sale Payment')");
        if ($conn->query("SHOW TABLES LIKE 'cashbook'")->num_rows > 0 && $paid > 0) {
            $conn->query("INSERT INTO cashbook(type, reference_type, reference_id, amount, note) VALUES('income', 'sale', $sale_id, '$paid', 'Sales Income')");
        }
        header('Location: invoice.php?id=' . $sale_id);
        exit;
    }
}

header('Location: pos.php');
exit;
