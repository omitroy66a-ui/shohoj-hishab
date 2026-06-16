<?php
require_once "../../config/database.php";
require_once "../../modules/business/middleware.php";

$business_id = businessId();

// Get customers with pending due
$res = $conn->query("
    SELECT id, name, phone, opening_due
    FROM customers
    WHERE business_id='$business_id' AND opening_due > 0
    ORDER BY opening_due DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Customer Due Reminders</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
        .container { max-width: 1000px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #007bff; color: white; }
        .whatsapp-btn { background: #25D366; color: white; padding: 8px 12px; border-radius: 4px; text-decoration: none; display: inline-block; }
        .whatsapp-btn:hover { background: #1BAD51; }
        a { color: #007bff; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Customer Due Reminders</h1>
        <p>Send payment reminders to customers with pending dues</p>
        
        <table>
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Phone</th>
                    <th>Due Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $count = 0;
                while($row = $res->fetch_assoc()): 
                    $count++;
                    $message = "Dear " . $row['name'] . ", your pending payment is ৳" . number_format($row['opening_due'], 2) . ". Please pay at your earliest convenience. Thank you!";
                    $whatsapp_link = "https://wa.me/88" . preg_replace('/[^0-9]/', '', $row['phone']) . "?text=" . urlencode($message);
                ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td>৳<?= number_format($row['opening_due'], 2) ?></td>
                    <td>
                        <a href="<?= $whatsapp_link ?>" target="_blank" class="whatsapp-btn">Send via WhatsApp</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <?php if($count == 0): ?>
        <p style="text-align: center; color: #666; margin-top: 20px;">No pending dues to remind</p>
        <?php else: ?>
        <p style="margin-top: 20px; color: #666;">Total customers with pending dues: <strong><?= $count ?></strong></p>
        <?php endif; ?>
    </div>
</body>
</html>
