<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../modules/business/middleware.php';

$business_id = businessId();

if (!$business_id) {
    http_response_code(401);
    exit('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currency = $_POST['currency'] ?? 'BDT';
    $tax_rate = (float) ($_POST['tax_rate'] ?? 0);
    $invoice_prefix = $_POST['invoice_prefix'] ?? 'INV';
    $language = $_POST['language'] ?? 'en';

    $stmt = $conn->prepare("SELECT * FROM business_settings WHERE business_id = ?");
    $stmt->bind_param("i", $business_id);
    $stmt->execute();
    $check = $stmt->get_result();
    
    if ($check->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE business_settings SET currency = ?, tax_rate = ?, invoice_prefix = ?, language = ? WHERE business_id = ?");
        $stmt->bind_param("sdssi", $currency, $tax_rate, $invoice_prefix, $language, $business_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO business_settings(business_id, currency, tax_rate, invoice_prefix, language) VALUES(?, ?, ?, ?, ?)");
        $stmt->bind_param("isdss", $business_id, $currency, $tax_rate, $invoice_prefix, $language);
    }
    $stmt->execute();
    
    header('Location: index.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM business_settings WHERE business_id = ?");
$stmt->bind_param("i", $business_id);
$stmt->execute();
$result = $stmt->get_result();
$settings = $result->fetch_assoc();

if (!$settings) {
    $settings = ['currency' => 'BDT', 'tax_rate' => 0, 'invoice_prefix' => 'INV', 'language' => 'en'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Settings</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
        .container { max-width: 700px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; }
        label { display: block; margin: 12px 0 5px 0; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 15px; box-sizing: border-box; }
        button { padding: 12px 18px; background: #007bff; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Business Settings</h1>
        <form method="POST">
            <label>Currency</label>
            <input type="text" name="currency" value="<?php echo htmlspecialchars($settings['currency']); ?>">

            <label>Tax Rate (%)</label>
            <input type="number" step="0.01" name="tax_rate" value="<?php echo $settings['tax_rate']; ?>">

            <label>Invoice Prefix</label>
            <input type="text" name="invoice_prefix" value="<?php echo htmlspecialchars($settings['invoice_prefix']); ?>">

            <label>Language</label>
            <select name="language">
                <option value="en" <?php echo $settings['language'] === 'en' ? 'selected' : ''; ?>>English</option>
                <option value="bn" <?php echo $settings['language'] === 'bn' ? 'selected' : ''; ?>>Bengali</option>
            </select>

            <button type="submit">Save Settings</button>
        </form>
    </div>
</body>
</html>
