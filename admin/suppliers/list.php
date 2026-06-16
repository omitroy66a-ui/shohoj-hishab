<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

$result = $conn->query("SELECT * FROM suppliers ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Supplier List - Sohoj Hishab</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; padding:30px; background:#f4f4f4; }
        .container { max-width:1100px; margin:0 auto; background:#fff; padding:20px; border-radius:8px; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:10px; border:1px solid #ddd; }
        th { background:#f1f1f1; }
        a.button { display:inline-block; margin-bottom:15px; padding:10px 16px; background:#007bff; color:#fff; border-radius:4px; text-decoration:none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Suppliers</h1>
        <a class="button" href="add.php">Add Supplier</a>
        <table>
            <thead>
                <tr><th>#</th><th>Name</th><th>Phone</th><th>Email</th><th>Opening Due</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php while($supplier = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $supplier['id']; ?></td>
                        <td><?php echo htmlspecialchars($supplier['name']); ?></td>
                        <td><?php echo htmlspecialchars($supplier['phone']); ?></td>
                        <td><?php echo htmlspecialchars($supplier['email']); ?></td>
                        <td><?php echo number_format($supplier['opening_due'],2); ?></td>
                        <td>
                            <a href="edit.php?id=<?php echo $supplier['id']; ?>">Edit</a> |
                            <a href="ledger.php?id=<?php echo $supplier['id']; ?>">Ledger</a> |
                            <a href="payment.php?id=<?php echo $supplier['id']; ?>">Payment</a> |
                            <a href="delete.php?id=<?php echo $supplier['id']; ?>" onclick="return confirm('Delete supplier?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
