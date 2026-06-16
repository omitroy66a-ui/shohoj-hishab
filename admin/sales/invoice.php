<?php
require_once __DIR__ . '/../../config/database.php';
session_start();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$token = isset($_GET['token']) ? $conn->real_escape_string($_GET['token']) : '';

$sale = null;
if($id > 0){
    $sale = $conn->query("SELECT sales.*, customers.name AS customer_name FROM sales LEFT JOIN customers ON customers.id = sales.customer_id WHERE sales.id=$id")->fetch_assoc();
}

if(!$sale && $token !== ''){
    $sale = $conn->query("SELECT sales.*, customers.name AS customer_name FROM sales LEFT JOIN customers ON customers.id = sales.customer_id WHERE sales.share_token='$token'")->fetch_assoc();
}

if(!$sale){
    if($token === '' && empty($_SESSION['user_id'])){
        header('Location: ../../login.php');
        exit;
    }

    echo 'Sale not found.';
    exit;
}

$scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$invoice_url = $scheme . '://' . $host . dirname($_SERVER['REQUEST_URI']) . '/invoice.php?token=' . urlencode($sale['share_token']);
$share_text = rawurlencode("Invoice {$sale['invoice_no']} for " . ($sale['customer_name'] ?? 'Customer') . ". Amount: {$sale['grand_total']}.");
$whatsapp_url = "https://wa.me/?text={$share_text}%20" . rawurlencode($invoice_url);
$telegram_url = "https://t.me/share/url?url=" . rawurlencode($invoice_url) . "&text=" . $share_text;
$facebook_url = "https://www.facebook.com/sharer/sharer.php?u=" . rawurlencode($invoice_url);

$qr_image_src = 'https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=' . urlencode($invoice_url);
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
    if (class_exists('Endroid\QrCode\Builder\Builder')) {
        try {
            $result = 
                Endroid\QrCode\Builder\Builder::create()
                    ->data($invoice_url)
                    ->size(200)
                    ->margin(10)
                    ->build();
            $qr_image_src = 'data:' . $result->getMimeType() . ';base64,' . base64_encode($result->getString());
        } catch (Exception $e) {
            // fallback to Google Charts
        }
    }
}

$sale_items = $conn->query("SELECT sale_items.*, products.name AS product_name FROM sale_items LEFT JOIN products ON products.id = sale_items.product_id WHERE sale_items.sale_id = {$sale['id']}")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #<?php echo htmlspecialchars($sale['invoice_no']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f7f7f7; padding: 30px; }
        .invoice-box { max-width: 800px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 6px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: center; }
        .header h1 { margin: 0; }
        .details { margin-top: 20px; }
        .details div { margin-bottom: 10px; }
        .qr { margin-top: 20px; }
        .actions { margin-top: 20px; display: flex; flex-wrap: wrap; gap: 10px; }
        .actions a, .actions button { padding: 10px 14px; border-radius: 4px; text-decoration: none; border: none; cursor: pointer; color: #fff; background: #007bff; }
        .actions a.btn-success { background: #25D366; }
        .actions a.btn-info { background: #0088cc; }
        .actions a.btn-primary { background: #3b5998; }
        .actions button { background: #6c757d; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <div>
                <h1>Invoice</h1>
                <div>Invoice Number: <?php echo htmlspecialchars($sale['invoice_no']); ?></div>
            </div>
            <div class="qr">
                <img src="<?php echo htmlspecialchars($qr_image_src); ?>" alt="Invoice QR" />
            </div>
        </div>
        <div class="details">
            <div><strong>Customer Name:</strong> <?php echo htmlspecialchars($sale['customer_name'] ?? 'Walk-in Customer'); ?></div>
            <div><strong>Payment Method:</strong> <?php echo htmlspecialchars($sale['payment_method']); ?></div>
            <div><strong>Invoice URL:</strong> <a href="<?php echo htmlspecialchars($invoice_url); ?>"><?php echo htmlspecialchars($invoice_url); ?></a></div>
        </div>
        <div style="margin-top:20px;">
            <h3>Items</h3>
            <table style="width:100%; border-collapse: collapse; margin-top:10px;">
                <thead>
                    <tr>
                        <th style="border:1px solid #ddd; padding:8px; text-align:left;">Product</th>
                        <th style="border:1px solid #ddd; padding:8px; text-align:right;">Qty</th>
                        <th style="border:1px solid #ddd; padding:8px; text-align:right;">Price</th>
                        <th style="border:1px solid #ddd; padding:8px; text-align:right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sale_items as $item): ?>
                        <tr>
                            <td style="border:1px solid #ddd; padding:8px;"><?php echo htmlspecialchars($item['product_name'] ?? 'Product'); ?></td>
                            <td style="border:1px solid #ddd; padding:8px; text-align:right;"><?php echo htmlspecialchars($item['qty']); ?></td>
                            <td style="border:1px solid #ddd; padding:8px; text-align:right;"><?php echo htmlspecialchars($item['price']); ?></td>
                            <td style="border:1px solid #ddd; padding:8px; text-align:right;"><?php echo htmlspecialchars($item['subtotal']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="details" style="margin-top:20px;">
            <div><strong>Subtotal:</strong> <?php echo htmlspecialchars($sale['subtotal']); ?></div>
            <div><strong>Discount:</strong> <?php echo htmlspecialchars($sale['discount']); ?></div>
            <div><strong>Grand Total:</strong> <?php echo htmlspecialchars($sale['grand_total']); ?></div>
            <div><strong>Paid:</strong> <?php echo htmlspecialchars($sale['paid']); ?></div>
            <div><strong>Due:</strong> <?php echo htmlspecialchars($sale['due']); ?></div>
        </div>
        <div class="actions">
            <a href="<?php echo $whatsapp_url; ?>" target="_blank" class="btn btn-success">WhatsApp</a>
            <a href="<?php echo $telegram_url; ?>" target="_blank" class="btn btn-info">Telegram</a>
            <a href="<?php echo $facebook_url; ?>" target="_blank" class="btn btn-primary">Facebook</a>
            <button type="button" class="btn btn-secondary" onclick="shareInvoice()">Mobile Share</button>
            <a href="print_invoice.php?id=<?php echo $sale['id']; ?>">Print</a>
            <a href="download_pdf.php?id=<?php echo $sale['id']; ?>">Download PDF</a>
            <a href="thermal_invoice.php?id=<?php echo $sale['id']; ?>">Thermal</a>
        </div>
    </div>
    <script>
        function shareInvoice() {
            if (navigator.share) {
                navigator.share({
                    title: 'Invoice <?php echo htmlspecialchars($sale['invoice_no']); ?>',
                    text: 'Invoice <?php echo htmlspecialchars($sale['invoice_no']); ?> for <?php echo htmlspecialchars($sale['customer_name'] ?? 'Customer'); ?>.',
                    url: '<?php echo htmlspecialchars($invoice_url); ?>'
                }).catch(function(error) {
                    console.error('Share failed:', error);
                });
            } else {
                alert('Native sharing is not supported on this device.');
            }
        }
    </script>
</body>
</html>
