-- ============================================================
-- SOHOJ HISHAB - Main Database Schema
-- MySQL Syntax (Not MSSQL/T-SQL)
-- ============================================================

CREATE DATABASE IF NOT EXISTS sohoj_hishab;
USE sohoj_hishab;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','staff') DEFAULT 'staff',
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users(name,email,password,role)
VALUES(
'Administrator',
'admin@sohojhishab.com',
MD5('123456'),
'admin'
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(200) NOT NULL,
    barcode VARCHAR(100),
    purchase_price DECIMAL(10,2) DEFAULT 0,
    sale_price DECIMAL(10,2) DEFAULT 0,
    stock INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE customers(
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(100),
phone VARCHAR(20),
email VARCHAR(100),
opening_due DECIMAL(10,2) DEFAULT 0,
address TEXT,
due_amount DECIMAL(10,2) DEFAULT 0
);

CREATE TABLE suppliers(
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(100),
phone VARCHAR(20),
address TEXT,
due_amount DECIMAL(10,2) DEFAULT 0
);

CREATE TABLE sales(
id INT AUTO_INCREMENT PRIMARY KEY,
invoice_no VARCHAR(50),
customer_id INT,
subtotal DECIMAL(10,2),
discount DECIMAL(10,2),
grand_total DECIMAL(10,2),
paid DECIMAL(10,2),
due DECIMAL(10,2),
payment_method VARCHAR(30),
share_token VARCHAR(255),
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE sale_items(
id INT AUTO_INCREMENT PRIMARY KEY,
sale_id INT,
product_id INT,
qty INT,
price DECIMAL(10,2),
subtotal DECIMAL(10,2)
);

CREATE TABLE customer_ledger(
id INT AUTO_INCREMENT PRIMARY KEY,
customer_id INT,
sale_id INT,
debit DECIMAL(10,2),
credit DECIMAL(10,2),
note TEXT,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE payments(
id INT AUTO_INCREMENT PRIMARY KEY,
sale_id INT,
amount DECIMAL(10,2),
method VARCHAR(30),
note TEXT,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE supplier_ledger(
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT,
    purchase_id INT,
    debit DECIMAL(10,2) DEFAULT 0,
    credit DECIMAL(10,2) DEFAULT 0,
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE purchases(
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_no VARCHAR(50),
    supplier_id INT,
    subtotal DECIMAL(10,2),
    discount DECIMAL(10,2),
    grand_total DECIMAL(10,2),
    paid DECIMAL(10,2),
    due DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE purchase_items(
    id INT AUTO_INCREMENT PRIMARY KEY,
    purchase_id INT,
    product_id INT,
    qty INT,
    price DECIMAL(10,2),
    subtotal DECIMAL(10,2)
);

CREATE TABLE cashbook(
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('income','expense'),
    reference_type VARCHAR(50),
    reference_id INT,
    amount DECIMAL(10,2),
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Removed orphan ); 
CREATE TABLE stock_logs(
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    change_qty INT,
    type VARCHAR(50),
    reference_id INT,
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE expenses(
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(100),
    amount DECIMAL(10,2),
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE daily_closing(
    id INT AUTO_INCREMENT PRIMARY KEY,
    closing_date DATE,
    total_sales DECIMAL(10,2),
    total_expenses DECIMAL(10,2),
    total_profit DECIMAL(10,2),
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE profits(
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT,
    revenue DECIMAL(10,2),
    cost DECIMAL(10,2),
    profit_amount DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE businesses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150),
    owner_id INT,
    phone VARCHAR(20),
    plan VARCHAR(50),
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_id INT,
    plan VARCHAR(50),
    start_date DATE,
    end_date DATE,
    status VARCHAR(20)
);

CREATE TABLE online_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_id INT,
    customer_name VARCHAR(150),
    phone VARCHAR(20),
    address TEXT,
    total DECIMAL(10,2),
    status VARCHAR(30),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_id INT,
    title VARCHAR(255),
    body TEXT,
    seen TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150)
);

CREATE TABLE role_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT,
    permission_id INT
);

CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150),
    phone VARCHAR(20),
    email VARCHAR(100),
    password VARCHAR(255),
    role_id INT,
    branch_id INT DEFAULT 0,
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE super_admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100),
    password VARCHAR(255)
);

ALTER TABLE products ADD COLUMN business_id INT DEFAULT NULL;
ALTER TABLE sales ADD COLUMN business_id INT DEFAULT NULL;
ALTER TABLE purchases ADD COLUMN business_id INT DEFAULT NULL;
ALTER TABLE customers ADD COLUMN business_id INT DEFAULT NULL;
ALTER TABLE expenses ADD COLUMN business_id INT DEFAULT NULL;
ALTER TABLE expenses ADD COLUMN employee_id INT DEFAULT NULL;
ALTER TABLE expenses ADD COLUMN expense_date DATE DEFAULT NULL;

CREATE TABLE expense_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_en VARCHAR(150),
    name_bn VARCHAR(150),
    type VARCHAR(50),
    business_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO expense_categories(name_en,name_bn,type,business_id) VALUES
('Salary','বেতন','salary',1),
('Rent','ভাড়া','rent',1),
('Bill','বিল','bill',1),
('Purchase Expense','ক্রয় খরচ','purchase',1),
('Other','অন্যান্য','other',1);

-- NOTE: employees table already created above
-- Replaced duplicate with ALTER statements
ALTER TABLE employees ADD COLUMN salary DECIMAL(10,2) AFTER phone;
ALTER TABLE employees ADD COLUMN business_id INT DEFAULT NULL AFTER salary;

CREATE TABLE business_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_id INT,
    name VARCHAR(150),
    currency VARCHAR(10) DEFAULT 'BDT',
    tax_rate DECIMAL(5,2) DEFAULT 0,
    invoice_prefix VARCHAR(50) DEFAULT 'INV',
    language VARCHAR(10) DEFAULT 'en',
    logo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
