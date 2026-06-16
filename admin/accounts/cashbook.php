<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

$result = $conn->query("SELECT * FROM cashbook ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cashbook - Sohoj Hishab</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; padding:30px; background:#f4f4f4; }
        .container { max-width:1100px; margin:0 auto; background:#fff; padding:20px; border-radius:8px; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:10px; border:1px solid #ddd; }
        th { background:#f1f1f1; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Cashbook</h1>
        <table>
            <thead>
                <tr><th>Date</th><th>Type</th><th>Reference</th><th>Amount</th><th>Note</th></tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['created_at']; ?></td>
                        <td><?php echo htmlspecialchars($row['type']); ?></td>
                        <td><?php echo htmlspecialchars($row['reference_type'] . ' #' . $row['reference_id']); ?></td>
                        <td><?php echo number_format($row['amount'],2); ?></td>
                        <td><?php echo htmlspecialchars($row['note']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
