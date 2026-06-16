<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../modules/business/middleware.php';

$business_id = businessId();

if (!$business_id) {
    http_response_code(401);
    exit('Unauthorized');
}

$sale_id = (int)($_GET['id'] ?? 0);
$size = $_GET['size'] ?? '58';  // 48, 58, 72, 80mm
$language = $_GET['lang'] ?? 'en'; // en, bn, ar
$note = $_GET['note'] ?? '';

// Get sale details with customer info
$stmt = $conn->prepare("SELECT s.*, c.name as customer_name, c.phone as customer_phone, c.address as customer_address FROM sales s LEFT JOIN customers c ON s.customer_id = c.id WHERE s.id = ? AND s.business_id = ?");
$stmt->bind_param("ii", $sale_id, $business_id);
$stmt->execute();
$sale = $stmt->get_result()->fetch_assoc();

if (!$sale) {
    http_response_code(404);
    exit('Sale not found');
}

// Get sale items
$stmt = $conn->prepare("SELECT si.*, p.name as product_name FROM sale_items si LEFT JOIN products p ON si.product_id = p.id WHERE si.sale_id = ?");
$stmt->bind_param("i", $sale_id);
$stmt->execute();
$items = $stmt->get_result();

// Get printer settings
$stmt = $conn->prepare("SELECT * FROM printer_settings WHERE business_id = ?");
$stmt->bind_param("i", $business_id);
$stmt->execute();
$settings = $stmt->get_result()->fetch_assoc() ?? ['shop_name' => 'Your Shop', 'footer_text' => 'Thank You!'];

// Get printer size in mm
$width_mm = $size;
$char_width = [
    '48' => 18,   // Characters per line
    '58' => 22,
    '72' => 30,
    '80' => 32
];
$chars_per_line = $char_width[$size] ?? 22;

// Translations
$translations = [
    'en' => [
        'invoice' => 'INVOICE',
        'invoice_no' => 'Invoice No',
        'date' => 'Date',
        'customer' => 'Customer',
        'phone' => 'Phone',
        'address' => 'Address',
        'product' => 'Product',
        'qty' => 'Qty',
        'price' => 'Price',
        'total' => 'Total',
        'subtotal' => 'Subtotal',
        'discount' => 'Discount',
        'grand_total' => 'Grand Total',
        'paid' => 'Paid',
        'due' => 'Due',
        'note' => 'Note',
        'thank_you' => 'Thank You!'
    ],
    'bn' => [
        'invoice' => 'চালান',
        'invoice_no' => 'চালান নম্বর',
        'date' => 'তারিখ',
        'customer' => 'গ্রাহক',
        'phone' => 'ফোন',
        'address' => 'ঠিকানা',
        'product' => 'পণ্য',
        'qty' => 'পরিমাণ',
        'price' => 'মূল্য',
        'total' => 'মোট',
        'subtotal' => 'উপমোট',
        'discount' => 'ছাড়',
        'grand_total' => 'গ্র্যান্ড টোটাল',
        'paid' => 'প্রদত্ত',
        'due' => 'বাকি',
        'note' => 'নোট',
        'thank_you' => 'ধন্যবাদ!'
    ],
    'ar' => [
        'invoice' => 'الفاتورة',
        'invoice_no' => 'رقم الفاتورة',
        'date' => 'التاريخ',
        'customer' => 'العميل',
        'phone' => 'الهاتف',
        'address' => 'العنوان',
        'product' => 'المنتج',
        'qty' => 'الكمية',
        'price' => 'السعر',
        'total' => 'الإجمالي',
        'subtotal' => 'المجموع الفرعي',
        'discount' => 'الخصم',
        'grand_total' => 'الإجمالي الكلي',
        'paid' => 'المدفوع',
        'due' => 'المستحق',
        'note' => 'ملاحظة',
        'thank_you' => 'شكرا لك!'
    ]
];

$t = $translations[$language] ?? $translations['en'];

// Calculate dimensions based on width
$font_size = [
    '48' => '11px',
    '58' => '12px',
    '72' => '13px',
    '80' => '14px'
];

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Thermal Invoice</title>
    <style>
        @media print {
            * { margin: 0; padding: 0; }
            body { margin: 0; }
            .no-print { display: none; }
        }
        
        body {
            margin: 0;
            padding: 5mm;
            font-family: 'Courier New', monospace;
            font-size: <?php echo $font_size[$size]; ?>;
            width: <?php echo $width_mm; ?>mm;
            line-height: 1.2;
        }
        
        .receipt {
            width: 100%;
            text-align: center;
        }
        
        .header {
            border-bottom: 1px dashed #000;
            padding: 5px 0;
            margin-bottom: 5px;
        }
        
        .shop-name {
            font-weight: bold;
            font-size: 1.2em;
            margin-bottom: 3px;
        }
        
        .receipt-title {
            font-weight: bold;
            margin: 5px 0;
        }
        
        .receipt-info {
            text-align: left;
            font-size: 0.9em;
            margin: 5px 0;
            padding: 0 5px;
        }
        
        .receipt-info-row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
        }
        
        .items {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 5px 0;
            margin: 5px 0;
            text-align: left;
            font-size: 0.9em;
        }
        
        .item-header {
            font-weight: bold;
            display: grid;
            grid-template-columns: 1fr 0.5fr 0.5fr 0.8fr;
            gap: 3px;
            margin-bottom: 3px;
            padding-bottom: 2px;
            border-bottom: 1px dashed #000;
        }
        
        .item-row {
            display: grid;
            grid-template-columns: 1fr 0.5fr 0.5fr 0.8fr;
            gap: 3px;
            margin: 2px 0;
        }
        
        .summary {
            text-align: right;
            padding: 5px 0;
            margin: 5px 0;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
        }
        
        .summary-row.total {
            font-weight: bold;
            border-top: 1px solid #000;
            border-bottom: 2px solid #000;
            padding: 2px 0;
        }
        
        .note-section {
            text-align: left;
            margin: 10px 0;
            padding: 5px;
            border: 1px dashed #000;
            font-size: 0.85em;
        }
        
        .footer {
            text-align: center;
            margin-top: 10px;
            font-weight: bold;
        }
        
        .no-print {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #007bff;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .no-print:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <div class="shop-name"><?php echo htmlspecialchars($settings['shop_name']); ?></div>
            <div class="receipt-title"><?php echo $t['invoice']; ?></div>
        </div>
        
        <div class="receipt-info">
            <div class="receipt-info-row">
                <span><?php echo $t['invoice_no']; ?>:</span>
                <span><?php echo htmlspecialchars($sale['invoice_no']); ?></span>
            </div>
            <div class="receipt-info-row">
                <span><?php echo $t['date']; ?>:</span>
                <span><?php echo date('d/m/Y H:i', strtotime($sale['created_at'])); ?></span>
            </div>
        </div>
        
        <?php if ($sale['customer_name']): ?>
            <div class="receipt-info" style="border: 1px dashed #000; padding: 5px; margin: 5px 0;">
                <div class="receipt-info-row">
                    <strong><?php echo $t['customer']; ?>:</strong>
                    <span><?php echo htmlspecialchars($sale['customer_name']); ?></span>
                </div>
                <?php if ($sale['customer_phone']): ?>
                    <div class="receipt-info-row">
                        <strong><?php echo $t['phone']; ?>:</strong>
                        <span><?php echo htmlspecialchars($sale['customer_phone']); ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($sale['customer_address']): ?>
                    <div class="receipt-info-row">
                        <strong><?php echo $t['address']; ?>:</strong>
                        <span><?php echo htmlspecialchars(substr($sale['customer_address'], 0, 30)); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="items">
            <div class="item-header">
                <span><?php echo $t['product']; ?></span>
                <span><?php echo $t['qty']; ?></span>
                <span><?php echo $t['price']; ?></span>
                <span><?php echo $t['total']; ?></span>
            </div>
            
            <?php while ($item = $items->fetch_assoc()): ?>
                <div class="item-row">
                    <span><?php echo htmlspecialchars(substr($item['product_name'], 0, 12)); ?></span>
                    <span><?php echo $item['qty']; ?></span>
                    <span><?php echo number_format($item['price'], 0); ?></span>
                    <span><?php echo number_format($item['total'], 0); ?></span>
                </div>
            <?php endwhile; ?>
        </div>
        
        <div class="summary">
            <div class="summary-row">
                <span><?php echo $t['subtotal']; ?>:</span>
                <span><?php echo number_format($sale['subtotal'], 0); ?></span>
            </div>
            <?php if ($sale['discount'] > 0): ?>
                <div class="summary-row">
                    <span><?php echo $t['discount']; ?>:</span>
                    <span>-<?php echo number_format($sale['discount'], 0); ?></span>
                </div>
            <?php endif; ?>
            <div class="summary-row total">
                <span><?php echo $t['grand_total']; ?>:</span>
                <span><?php echo number_format($sale['grand_total'], 0); ?></span>
            </div>
            <div class="summary-row">
                <span><?php echo $t['paid']; ?>:</span>
                <span><?php echo number_format($sale['paid'], 0); ?></span>
            </div>
            <?php if ($sale['due_amount'] > 0): ?>
                <div class="summary-row" style="color: red; font-weight: bold;">
                    <span><?php echo $t['due']; ?>:</span>
                    <span><?php echo number_format($sale['due_amount'], 0); ?></span>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($note)): ?>
            <div class="note-section">
                <strong><?php echo $t['note']; ?>:</strong><br>
                <?php echo htmlspecialchars($note); ?>
            </div>
        <?php endif; ?>
        
        <div class="footer">
            <?php echo $settings['footer_text'] ?? $t['thank_you']; ?>
        </div>
    </div>
    
    <button class="no-print" onclick="window.print()">🖨️ Print</button>
</body>
</html>
