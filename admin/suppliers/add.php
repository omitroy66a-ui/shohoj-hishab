<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string(trim($_POST['name']));
    $phone = $conn->real_escape_string(trim($_POST['phone']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $address = $conn->real_escape_string(trim($_POST['address']));
    $opening_due = (float) ($_POST['opening_due'] ?? 0);

    $stmt = $conn->prepare("INSERT INTO suppliers(name, phone, email, address, opening_due) VALUES(?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param('ssssd', $name, $phone, $email, $address, $opening_due);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: list.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Add Supplier - Sohoj Hishab</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; padding:30px; background:#f4f4f4; }
        .container { max-width:700px; margin:0 auto; background:#fff; padding:20px; border-radius:8px; }
        label { display:block; margin-top:15px; }
        input, textarea { width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; }
        button { margin-top:20px; padding:12px 18px; border:none; border-radius:4px; color:#fff; background:#007bff; cursor:pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add Supplier</h1>
        <form method="post">
            <label for="name">Supplier Name</label>
            <input type="text" id="name" name="name" required>
            <label for="phone">Phone</label>
            <input type="text" id="phone" name="phone">
            <label for="email">Email</label>
            <input type="email" id="email" name="email">
            <label for="address">Address</label>
            <textarea id="address" name="address"></textarea>
            <label for="opening_due">Opening Due</label>
            <input type="number" step="0.01" id="opening_due" name="opening_due" value="0">
            <button type="submit">Save Supplier</button>
        </form>
        <p><a href="list.php">Back to Supplier List</a></p>
    </div>
</body>
</html>
