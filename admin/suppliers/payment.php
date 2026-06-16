<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

$supplier_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$supplier = $conn->query("SELECT * FROM suppliers WHERE id=$supplier_id")->fetch_assoc();
if (!$supplier) {
    echo 'Supplier not found.';
    exit;
}

$due = 0;
if ($conn->query("SHOW TABLES LIKE 'supplier_ledger'")->num_rows > 0) {
    $due = $conn->query("SELECT IFNULL(SUM(debit-credit),0) AS t FROM supplier_ledger WHERE supplier_id=$supplier_id")->fetch_assoc()['t'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (float) ($_POST['amount'] ?? 0);
    $note = $conn->real_escape_string(trim($_POST['note']));
    if ($amount > 0) {
        $conn->query("INSERT INTO supplier_ledger(supplier_id, purchase_id, debit, credit, note) VALUES($supplier_id, 0, 0, '$amount', '$note')");
        if ($conn->query("SHOW TABLES LIKE 'cashbook'")->num_rows > 0) {
            $conn->query("INSERT INTO cashbook(type, reference_type, reference_id, amount, note) VALUES('expense', 'supplier_payment', $supplier_id, '$amount', 'Supplier Payment')");
        }
        header('Location: list.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Supplier Payment - Sohoj Hishab</title>
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
        <h1>Pay Supplier: <?php echo htmlspecialchars($supplier['name']); ?></h1>
        <p>Current Due: <?php echo number_format($due,2); ?></p>
        <form method="post">
            <label for="amount">Amount</label>
            <input type="number" step="0.01" id="amount" name="amount" required>
            <label for="note">Note</label>
            <textarea id="note" name="note">Supplier Payment</textarea>
            <button type="submit">Save Payment</button>
        </form>
        <p><a href="list.php">Back to Supplier List</a></p>
    </div>
</body>
</html>
