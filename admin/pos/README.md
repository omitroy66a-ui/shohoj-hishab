# 🛍️ Enhanced POS System - Documentation

## 📁 File Structure

```
admin/pos/
├── index.php              (Main POS Dashboard)
├── search_product.php     (Barcode Search API)
├── cart.php              (Add to Cart API)
├── view_cart.php         (View Cart Page)
├── checkout.php          (Checkout API)
├── thermal_invoice.php   (Thermal Invoice with Multi-Size Support)
├── settings.php          (Printer Settings)
└── sql_schema_update.sql (Database Updates)

assets/
├── css/
│   └── pos.css          (Styles)
└── js/
    ├── pos.js           (POS Logic)
    └── barcode.js       (Barcode Scanner)
```

---

## 🚀 Features

### 1. **Multi-Size Thermal Printing**
- ✅ 48mm, 58mm, 72mm, 80mm printer support
- ✅ Auto-adjusted font and layout
- ✅ Configurable in Printer Settings

### 2. **Customer Information**
- ✅ Customer Name
- ✅ Phone Number
- ✅ Address
- ✅ Toggle visibility in settings

### 3. **Multi-Language Support**
- ✅ English (EN)
- ✅ Bengali (বাংলা - BN)
- ✅ Arabic (العربية - AR)
- ✅ Auto-detect based on settings

### 4. **Print Notes**
- ✅ Add custom notes before printing
- ✅ Notes appear on thermal invoice
- ✅ Separate field for each transaction

### 5. **Page Size Options**
- ✅ A4 (210×297mm) for standard printers
- ✅ A5 (148×210mm) for compact printing

### 6. **Printer Settings Panel**
- ✅ Shop name configuration
- ✅ Thermal printer size selection
- ✅ Page size selection
- ✅ Language preference
- ✅ Footer message customization
- ✅ Customer info visibility toggle

---

## 📊 Database Changes

### New Tables

#### `printer_settings`
```sql
CREATE TABLE printer_settings (
    id INT PRIMARY KEY,
    business_id INT UNIQUE,
    shop_name VARCHAR(255),
    thermal_size VARCHAR(10),      -- 48, 58, 72, 80
    page_size VARCHAR(10),         -- a4, a5
    language VARCHAR(10),          -- en, bn, ar
    footer_text TEXT,
    show_customer_info TINYINT
);
```

#### `sales_notes`
```sql
CREATE TABLE sales_notes (
    id INT PRIMARY KEY,
    sale_id INT,
    note_text TEXT,
    note_type VARCHAR(50),
    created_at TIMESTAMP
);
```

#### `product_variants` (Optional)
```sql
CREATE TABLE product_variants (
    id INT PRIMARY KEY,
    product_id INT,
    variant_name VARCHAR(255),
    barcode VARCHAR(100) UNIQUE,
    price DECIMAL(12,2),
    stock DECIMAL(12,2)
);
```

---

## 🖨️ Thermal Invoice API

### URL
```
/admin/pos/thermal_invoice.php?id=SALE_ID&size=SIZE&lang=LANG&note=NOTE
```

### Parameters
| Parameter | Values | Description |
|-----------|--------|-------------|
| `id` | Integer | Sale/Invoice ID |
| `size` | 48, 58, 72, 80 | Printer width in mm |
| `lang` | en, bn, ar | Language |
| `note` | String | Optional print note |

### Examples
```
thermal_invoice.php?id=123&size=58&lang=en&note=Special%20Order
thermal_invoice.php?id=456&size=80&lang=bn
thermal_invoice.php?id=789&size=48&lang=ar&note=VIP%20Customer
```

---

## 🛒 POS Workflow

### Step 1: Barcode Scanning
```
1. Open /admin/pos/index.php
2. Scan product barcode
3. Product auto-adds to cart
```

### Step 2: Add Customer Info (Optional)
```
1. Enter customer name
2. Enter phone number
3. Enter address
```

### Step 3: Add Print Note (Optional)
```
1. Type note in "Print Note" section
2. Note will appear on thermal invoice
```

### Step 4: Checkout
```
1. Apply discount if needed
2. Enter paid amount
3. Click "Checkout & Print"
4. Invoice opens in new window
5. Print from browser (Ctrl+P)
```

---

## 🖨️ Printing Instructions

### Thermal Printer (58mm)
1. Go to Printer Settings
2. Select "58mm" as Thermal Size
3. Go to POS → Checkout
4. Thermal invoice opens
5. Press Ctrl+P → Select thermal printer → Print

### A4 Printer
1. Go to Printer Settings
2. Select "A4" as Page Size
3. Use PDF invoice option
4. Press Ctrl+P → Select A4 printer → Print

### Printer Size Reference
```
48mm  → 18 characters per line (compact)
58mm  → 22 characters per line (standard) ⭐ RECOMMENDED
72mm  → 30 characters per line (wide)
80mm  → 32 characters per line (extra wide)
```

---

## 🌐 Language Support

### English
- Default language
- All features available

### Bengali (বাংলা)
- Full Bengali translation
- Character limit per line adjusted
- RTL support ready

### Arabic (العربية)
- Full Arabic translation
- RTL text direction
- Number formatting as Arabic numerals

---

## 💾 API Endpoints

### Search Product
```
GET /admin/pos/search_product.php?barcode=CODE
```

### Add to Cart
```
POST /admin/pos/cart.php
Body: {"product_id": 123, "qty": 1}
```

### Checkout
```
POST /admin/pos/checkout.php
Body: {
    "customer_id": 0,
    "subtotal": 1000,
    "discount": 0,
    "paid": 1000,
    "cart": [...]
}
```

---

## ⚙️ Configuration

### Database Setup
```bash
mysql> USE sohoj_hishab;
mysql> SOURCE database/pos_schema_update.sql;
```

### Initial Settings
```
Shop Name: Your Shop Name
Thermal Size: 58mm (Default)
Page Size: A4 (Default)
Language: English (Default)
Footer: Thank You!
Show Customer: Yes (Default)
```

---

## 🐛 Troubleshooting

### Barcode not scanning?
- Check barcode format matches database
- Ensure barcode input field is focused
- Try manual product search

### Thermal text too small?
- Increase printer size (48mm → 58mm → 80mm)
- Adjust browser zoom (Ctrl + Mouse Wheel)
- Check printer driver settings

### Customer info not showing?
- Check "Show Customer Info" in Printer Settings
- Ensure customer data is entered before checkout
- Refresh invoice page

### Wrong language?
- Change language in Printer Settings
- Refresh invoice page
- Clear browser cache

---

## 📱 Mobile Support
- Responsive design (90% tested)
- Touch-friendly buttons
- Works on tablets
- Small screen optimization

---

## 🔐 Security Features
- ✅ Prepared statements (SQL Injection prevention)
- ✅ Business ID validation
- ✅ Session-based cart
- ✅ Input sanitization
- ✅ CSRF token support (when implemented)

---

## 📈 Future Enhancements
- [ ] Barcode generation
- [ ] Customer loyalty points
- [ ] Inventory tracking
- [ ] Sales analytics dashboard
- [ ] WhatsApp invoice sharing
- [ ] Email receipt option
- [ ] Multi-currency support
- [ ] Payment gateway integration

---

## 📞 Support
For issues or questions, contact your administrator.

**Last Updated:** 2024
**Version:** 1.0.0
