<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
session_start();

$supplier_id = isset($_POST['supplier_id']) ? (int) $_POST['supplier_id'] : 0;
$product_id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
$qty = isset($_POST['qty']) ? (int) $_POST['qty'] : 1;
$price = isset($_POST['price']) ? (float) $_POST['price'] : 0;
$invoice_no = trim($_POST['invoice_no'] ?? '');
if ($invoice_no === '') {
    $last = $conn->query("SELECT id FROM purchases ORDER BY id DESC LIMIT 1")->fetch_assoc();
    $next = isset($last['id']) ? $last['id'] + 1 : 1;
    $invoice_no = "PUR-" . str_pad($next, 5, "0", STR_PAD_LEFT);
}
$invoice_no = $conn->real_escape_string($invoice_no);
$discount = (float) ($_POST['discount'] ?? 0);
$paid = (float) ($_POST['paid'] ?? 0);

if (isset($_POST['clear_cart'])) {
    unset($_SESSION['purchase_cart']);
    header('Location: add.php');
    exit;
}

if (isset($_POST['add_to_cart'])) {
    if ($product_id > 0 && $qty > 0 && $price >= 0) {
        $product = $conn->query("SELECT name, stock FROM products WHERE id=$product_id")->fetch_assoc();
        if ($product) {
            $_SESSION['purchase_cart'][] = [
                'product_id' => $product_id,
                'product_name' => $product['name'],
                'qty' => $qty,
                'price' => $price,
                'subtotal' => $qty * $price,
                'stock' => $product['stock']
            ];
        }
    }
    header('Location: add.php');
    exit;
}

$cartItems = $_SESSION['purchase_cart'] ?? [];
$hasCart = !empty($cartItems);

if (isset($_POST['save_purchase']) && $hasCart && $supplier_id > 0) {
    $subtotal = 0;
    foreach ($cartItems as $item) {
        $subtotal += $item['subtotal'];
    }
    $grand_total = max(0, $subtotal - $discount);
    $due = max(0, $grand_total - $paid);

    $conn->query("INSERT INTO purchases(invoice_no, supplier_id, subtotal, discount, grand_total, paid, due) VALUES('$invoice_no', $supplier_id, '$subtotal', '$discount', '$grand_total', '$paid', '$due')");
    $purchase_id = $conn->insert_id;

    foreach ($cartItems as $item) {
        $prod_id = (int) $item['product_id'];
        $item_qty = (int) $item['qty'];
        $item_price = (float) $item['price'];
        $item_subtotal = (float) $item['subtotal'];
        $conn->query("INSERT INTO purchase_items(purchase_id, product_id, qty, price, subtotal) VALUES($purchase_id, $prod_id, $item_qty, '$item_price', '$item_subtotal')");
        $conn->query("UPDATE products SET stock = stock + $item_qty WHERE id = $prod_id");

        if ($conn->query("SHOW TABLES LIKE 'stock_logs'")->num_rows > 0) {
            $conn->query("INSERT INTO stock_logs(product_id, change_qty, type, reference_id, note) VALUES($prod_id, $item_qty, 'purchase', $purchase_id, 'Purchase {$invoice_no}')");
        }
    }

    if ($due > 0) {
        $conn->query("INSERT INTO supplier_ledger(supplier_id, purchase_id, debit, credit, note) VALUES($supplier_id, $purchase_id, '$due', 0, 'Purchase Due')");
    }

    if ($conn->query("SHOW TABLES LIKE 'cashbook' ")->num_rows > 0 && $paid > 0) {
        $conn->query("INSERT INTO cashbook(type, reference_type, reference_id, amount, note) VALUES('expense', 'purchase', $purchase_id, '$paid', 'Purchase Payment')");
    }

    unset($_SESSION['purchase_cart']);
    header('Location: invoice.php?id=' . $purchase_id);
    exit;
}

header('Location: add.php');
exit;
