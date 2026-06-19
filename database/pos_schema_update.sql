-- ============================================================
-- Database Schema Update for Enhanced POS System
-- MySQL Syntax (Not MSSQL/T-SQL)
-- ============================================================

-- Add columns to sales table for customer info
ALTER TABLE sales ADD COLUMN customer_phone VARCHAR(20) AFTER customer_id;
ALTER TABLE sales ADD COLUMN customer_address TEXT AFTER customer_phone;

-- Create printer_settings table
CREATE TABLE IF NOT EXISTS printer_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_id INT NOT NULL,
    shop_name VARCHAR(255) DEFAULT 'Your Shop',
    thermal_size VARCHAR(10) DEFAULT '58',
    page_size VARCHAR(10) DEFAULT 'a4',
    language VARCHAR(10) DEFAULT 'en',
    footer_text TEXT,
    show_customer_info TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_business (business_id)
);

-- Create sales_notes table for additional notes
CREATE TABLE IF NOT EXISTS sales_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    note_text TEXT,
    note_type VARCHAR(50) DEFAULT 'print',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE
);

-- Create product_variants table (optional for future use)
CREATE TABLE IF NOT EXISTS product_variants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    variant_name VARCHAR(255),
    barcode VARCHAR(100) UNIQUE,
    price DECIMAL(12,2),
    stock DECIMAL(12,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Indexing for better performance
CREATE INDEX idx_sales_invoice ON sales(invoice_no);
CREATE INDEX idx_sales_business ON sales(business_id);
CREATE INDEX idx_sale_items_sale ON sale_items(sale_id);
CREATE INDEX idx_products_barcode ON products(barcode);
CREATE INDEX idx_products_business ON products(business_id);
