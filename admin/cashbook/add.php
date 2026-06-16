<?php
require_once "../../config/database.php";
require_once "../../modules/business/middleware.php";
require_once "../../modules/accounting/cashbook.php";

$business_id = businessId();
$error = '';
$success = '';

if($_POST) {
    $type = $conn->real_escape_string($_POST['type'] ?? '');
    $amount = (float)($_POST['amount'] ?? 0);
    $note = $conn->real_escape_string($_POST['note'] ?? '');
    
    if(empty($type)) {
        $error = "Please select type (Income/Expense)";
    } elseif($amount <= 0) {
        $error = "Amount must be greater than 0";
    } elseif(empty($note)) {
        $error = "Note is required";
    } else {
        $result = addCashEntry($conn, $business_id, $type, $amount, $note, 'manual', 0);
        if($result) {
            $success = "Entry recorded successfully";
        } else {
            $error = "Failed to record entry";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Add Cashbook Entry</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; }
        label { display: block; margin: 15px 0 5px 0; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 15px; box-sizing: border-box; }
        button { padding: 12px 18px; background: #007bff; color: #fff; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        .error { color: red; padding: 10px; background: #ffe6e6; border-radius: 4px; margin-bottom: 15px; }
        .success { color: green; padding: 10px; background: #e6ffe6; border-radius: 4px; margin-bottom: 15px; }
        a { color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add Cashbook Entry</h1>
        
        <?php if($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <form method="post">
            <label>Type</label>
            <select name="type" required>
                <option value="">Select Type</option>
                <option value="income">Income</option>
                <option value="expense">Expense</option>
            </select>
            
            <label>Amount</label>
            <input type="number" step="0.01" name="amount" placeholder="Amount" required>
            
            <label>Note/Description</label>
            <textarea name="note" placeholder="Describe the transaction" required></textarea>
            
            <button type="submit">Record Entry</button>
            <p><a href="index.php">Back to Cashbook</a></p>
        </form>
    </div>
</body>
</html>
