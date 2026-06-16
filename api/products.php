<?php

include '../config/database.php';

// Add stock to products
$product_id = isset($_POST['product_id']) ? $_POST['product_id'] : 1;
$qty = isset($_POST['qty']) ? $_POST['qty'] : 0;

if(isset($_POST['action']) && $_POST['action'] == 'add_stock'){
    $conn->query("
    UPDATE products
    SET stock = stock + $qty
    WHERE id=$product_id
    ");
}

if(isset($_POST['action']) && $_POST['action'] == 'reduce_stock'){
    $conn->query("
    UPDATE products
    SET stock = stock - $qty
    WHERE id=$product_id
    ");
}

?>
