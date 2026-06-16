<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../modules/business/middleware.php';

$business_id = businessId();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $opening_due = (float) ($_POST['opening_due'] ?? 0);

    $stmt = $conn->prepare("INSERT INTO customers(name, phone, address, opening_due, business_id) VALUES(?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param('sssdi', $name, $phone, $address, $opening_due, $business_id);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Add Customer</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
        .container { max-width: 700px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; }
        label { display: block; margin: 12px 0 5px 0; }
        input, textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 15px; box-sizing: border-box; }
        button { padding: 12px 18px; background: #007bff; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add Customer</h1>
        <form method="POST">
            <label>Name</label>
            <input type="text" name="name" required>

            <label>Phone</label>
            <input type="text" name="phone">

            <label>Address</label>
            <textarea name="address" rows="3"></textarea>

            <label>Opening Due</label>
            <input type="number" step="0.01" name="opening_due" value="0">

            <button type="submit">Add Customer</button>
        </form>
        <p><a href="index.php">Back to Customers</a></p>
    </div>
</body>
</html>
