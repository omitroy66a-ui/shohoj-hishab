<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

$token = isset($_GET['token']) ? $conn->real_escape_string($_GET['token']) : '';

$sale = null;
if($token !== ''){
    $sale = $conn->query("SELECT sales.*, customers.name AS customer_name FROM sales LEFT JOIN customers ON customers.id = sales.customer_id WHERE sales.share_token='$token'")->fetch_assoc();
}

if(!$sale){
    echo 'Invoice share link is invalid.';
    exit;
}

$scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$share_url = $scheme . '://' . $host . dirname($_SERVER['REQUEST_URI']) . '/invoice.php?token=' . urlencode($token);
$share_text = rawurlencode("Invoice {$sale['invoice_no']} for " . ($sale['customer_name'] ?? 'Customer') . ". View invoice: ");
$whatsapp_url = "https://wa.me/?text={$share_text}" . rawurlencode($share_url);
$telegram_url = "https://t.me/share/url?url=" . rawurlencode($share_url) . "&text=" . $share_text;
$facebook_url = "https://www.facebook.com/sharer/sharer.php?u=" . rawurlencode($share_url);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Share Invoice - <?php echo htmlspecialchars($sale['invoice_no']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f9f9f9; padding: 30px; }
        .container { max-width: 800px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 6px; box-shadow: 0 0 10px rgba(0,0,0,0.08); }
        input { width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; }
        .buttons { margin-top: 20px; display: flex; flex-wrap: wrap; gap: 10px; }
        .buttons a, .buttons button { padding: 10px 16px; border: none; border-radius: 4px; color: #fff; text-decoration: none; cursor: pointer; }
        .btn-success { background: #25D366; }
        .btn-info { background: #0088cc; }
        .btn-primary { background: #3b5998; }
        .btn-default { background: #6c757d; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Share Invoice</h1>
        <p><strong>Invoice Number:</strong> <?php echo htmlspecialchars($sale['invoice_no']); ?></p>
        <p><strong>Customer:</strong> <?php echo htmlspecialchars($sale['customer_name'] ?? 'Unknown'); ?></p>
        <label for="share_url">Invoice URL</label>
        <input type="text" id="share_url" value="<?php echo htmlspecialchars($share_url); ?>" readonly>
        <div class="buttons">
            <a href="<?php echo $whatsapp_url; ?>" target="_blank" class="btn-success">WhatsApp</a>
            <a href="<?php echo $telegram_url; ?>" target="_blank" class="btn-info">Telegram</a>
            <a href="<?php echo $facebook_url; ?>" target="_blank" class="btn-primary">Facebook</a>
            <button type="button" class="btn-default" onclick="shareInvoice()">Mobile Share</button>
            <button type="button" class="btn-default" onclick="copyLink()">Copy Link</button>
        </div>
    </div>
    <script>
        function copyLink() {
            const input = document.getElementById('share_url');
            input.select();
            document.execCommand('copy');
            alert('Link copied to clipboard');
        }
        function shareInvoice() {
            if (navigator.share) {
                navigator.share({
                    title: 'Invoice <?php echo htmlspecialchars($sale['invoice_no']); ?>',
                    text: 'Invoice for <?php echo htmlspecialchars($sale['customer_name'] ?? 'Customer'); ?>',
                    url: '<?php echo htmlspecialchars($share_url); ?>'
                }).catch(function(err){ console.error(err); });
            } else {
                alert('Native sharing is not supported on this device.');
            }
        }
    </script>
</body>
</html> 
