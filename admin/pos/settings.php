<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../modules/business/middleware.php';

$business_id = businessId();

if (!$business_id) {
    http_response_code(401);
    exit('Unauthorized');
}

// Get or create printer settings
$stmt = $conn->prepare("SELECT * FROM printer_settings WHERE business_id = ?");
$stmt->bind_param("i", $business_id);
$stmt->execute();
$settings = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shop_name = $_POST['shop_name'] ?? '';
    $thermal_size = $_POST['thermal_size'] ?? '58';
    $page_size = $_POST['page_size'] ?? 'a4';
    $language = $_POST['language'] ?? 'en';
    $footer_text = $_POST['footer_text'] ?? 'Thank You!';
    $show_customer_info = isset($_POST['show_customer_info']) ? 1 : 0;

    if ($settings) {
        $stmt = $conn->prepare("UPDATE printer_settings SET shop_name = ?, thermal_size = ?, page_size = ?, language = ?, footer_text = ?, show_customer_info = ? WHERE business_id = ?");
        $stmt->bind_param("sssssii", $shop_name, $thermal_size, $page_size, $language, $footer_text, $show_customer_info, $business_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO printer_settings(business_id, shop_name, thermal_size, page_size, language, footer_text, show_customer_info) VALUES(?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssii", $business_id, $shop_name, $thermal_size, $page_size, $language, $footer_text, $show_customer_info);
    }
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Printer settings saved successfully!';
        header('Location: settings.php');
        exit;
    }
}

// Set defaults
if (!$settings) {
    $settings = [
        'shop_name' => 'Your Shop',
        'thermal_size' => '58',
        'page_size' => 'a4',
        'language' => 'en',
        'footer_text' => 'Thank You!',
        'show_customer_info' => 1
    ];
}

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Printer Settings - POS</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; }
        h1 { margin-bottom: 20px; color: #333; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        textarea { resize: vertical; }
        select option { padding: 8px; }
        .checkbox-group { display: flex; align-items: center; }
        .checkbox-group input { width: auto; margin-right: 10px; }
        .info-box { background: #e7f3ff; padding: 12px; border-left: 4px solid #007bff; margin-bottom: 20px; border-radius: 4px; }
        .info-box strong { color: #004085; }
        .btn { padding: 12px 30px; margin-right: 10px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        .btn-primary { background: #007bff; color: #fff; }
        .btn-secondary { background: #6c757d; color: #fff; }
        .btn:hover { opacity: 0.9; }
        .success { background: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 20px; }
        .size-preview { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-top: 10px; }
        .size-box { padding: 10px; border: 1px solid #ddd; border-radius: 4px; text-align: center; cursor: pointer; }
        .size-box.active { background: #007bff; color: #fff; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🖨️ Printer Settings</h1>
        
        <?php if ($success): ?>
            <div class="success">✅ <?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <div class="info-box">
            <strong>📌 Note:</strong> These settings will apply to all thermal and PDF invoices generated in the POS system.
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label for="shop_name">Shop/Business Name</label>
                <input type="text" id="shop_name" name="shop_name" value="<?php echo htmlspecialchars($settings['shop_name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="thermal_size">🖥️ Thermal Printer Size (mm)</label>
                <select id="thermal_size" name="thermal_size" required onchange="updatePreview()">
                    <option value="48" <?php echo $settings['thermal_size'] == '48' ? 'selected' : ''; ?>>48mm (Compact)</option>
                    <option value="58" <?php echo $settings['thermal_size'] == '58' ? 'selected' : ''; ?>>58mm (Standard)</option>
                    <option value="72" <?php echo $settings['thermal_size'] == '72' ? 'selected' : ''; ?>>72mm (Wide)</option>
                    <option value="80" <?php echo $settings['thermal_size'] == '80' ? 'selected' : ''; ?>>80mm (Extra Wide)</option>
                </select>
                <small style="color: #666; display: block; margin-top: 5px;">
                    Recommended: 58mm for standard thermal printers
                </small>
            </div>
            
            <div class="form-group">
                <label for="page_size">📄 PDF/A4 Page Size</label>
                <select id="page_size" name="page_size" required>
                    <option value="a4" <?php echo $settings['page_size'] == 'a4' ? 'selected' : ''; ?>>A4 (210×297mm)</option>
                    <option value="a5" <?php echo $settings['page_size'] == 'a5' ? 'selected' : ''; ?>>A5 (148×210mm)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="language">🌐 Auto Detect Language</label>
                <select id="language" name="language" required>
                    <option value="en" <?php echo $settings['language'] == 'en' ? 'selected' : ''; ?>>English (EN)</option>
                    <option value="bn" <?php echo $settings['language'] == 'bn' ? 'selected' : ''; ?>>Bengali (বাংলা)</option>
                    <option value="ar" <?php echo $settings['language'] == 'ar' ? 'selected' : ''; ?>>Arabic (العربية)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="footer_text">📋 Footer Message</label>
                <textarea id="footer_text" name="footer_text" rows="2" placeholder="e.g., Thank You! Come Again"><?php echo htmlspecialchars($settings['footer_text']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label class="checkbox-group">
                    <input type="checkbox" name="show_customer_info" <?php echo $settings['show_customer_info'] ? 'checked' : ''; ?>>
                    <span>Show Customer Info on Invoice (Name, Phone, Address)</span>
                </label>
            </div>
            
            <div style="margin-top: 30px;">
                <button type="submit" class="btn btn-primary">💾 Save Settings</button>
                <a href="index.php" class="btn btn-secondary" style="text-decoration: none;">← Back to POS</a>
            </div>
        </form>
    </div>
</body>
</html>
