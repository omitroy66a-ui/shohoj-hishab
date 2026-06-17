-- ============================================================
-- SUBSCRIPTION SYSTEM DATABASE SCHEMA
-- ============================================================

-- 1. Subscription Plans Table
CREATE TABLE IF NOT EXISTS subscription_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    plan_type ENUM('trial', 'standard', 'advanced') NOT NULL UNIQUE,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    duration_days INT NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Plan Pricing Options (Monthly, 6 Months, Yearly)
CREATE TABLE IF NOT EXISTS plan_pricing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plan_id INT NOT NULL,
    duration_type ENUM('monthly', 'six_months', 'yearly') NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    discount_percentage DECIMAL(5,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (plan_id) REFERENCES subscription_plans(id) ON DELETE CASCADE,
    UNIQUE KEY unique_plan_duration (plan_id, duration_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Business Subscriptions
CREATE TABLE IF NOT EXISTS business_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_id INT NOT NULL,
    plan_id INT NOT NULL,
    start_date DATE NOT NULL,
    expiry_date DATE NOT NULL,
    status ENUM('active', 'expired', 'cancelled', 'pending') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (plan_id) REFERENCES subscription_plans(id) ON DELETE RESTRICT,
    INDEX idx_business_id (business_id),
    INDEX idx_status (status),
    INDEX idx_expiry_date (expiry_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Payment Transactions
CREATE TABLE IF NOT EXISTS subscription_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_subscription_id INT NOT NULL,
    business_id INT NOT NULL,
    payment_number VARCHAR(100) UNIQUE NOT NULL,
    transaction_id VARCHAR(100) UNIQUE NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50),
    payment_status ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    reviewed_by INT,
    approved_at TIMESTAMP NULL,
    rejection_reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (business_subscription_id) REFERENCES business_subscriptions(id) ON DELETE CASCADE,
    INDEX idx_business_id (business_id),
    INDEX idx_payment_status (payment_status),
    INDEX idx_transaction_id (transaction_id),
    INDEX idx_payment_number (payment_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Feature Permissions
CREATE TABLE IF NOT EXISTS feature_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plan_type VARCHAR(30) NOT NULL,
    feature_key VARCHAR(100) NOT NULL,
    feature_name VARCHAR(150),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_plan_feature (plan_type, feature_key),
    INDEX idx_plan_type (plan_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Subscription History (Audit Log)
CREATE TABLE IF NOT EXISTS subscription_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_id INT NOT NULL,
    old_plan_id INT,
    new_plan_id INT,
    old_status VARCHAR(50),
    new_status VARCHAR(50),
    action VARCHAR(100),
    changed_by INT,
    reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_business_id (business_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- INSERT DEFAULT DATA
-- ============================================================

-- Insert Subscription Plans
INSERT IGNORE INTO subscription_plans (id, name, plan_type, price, duration_days, description, is_active)
VALUES 
(1, 'Free Trial', 'trial', 0, 3, 'সব Feature Unlock - 3 দিনের ট্রায়াল', TRUE),
(2, 'Standard', 'standard', 60, 30, 'POS, Inventory, Customer, Supplier, Expense, Basic Reports', TRUE),
(3, 'Advanced', 'advanced', 199, 30, 'সব Feature Unlock - সম্পূর্ণ প্যাকেজ', TRUE);

-- Insert Plan Pricing
INSERT IGNORE INTO plan_pricing (plan_id, duration_type, price, discount_percentage)
VALUES 
(2, 'monthly', 60, 0),
(2, 'six_months', 219, 0),
(2, 'yearly', 699, 0),
(3, 'monthly', 199, 0),
(3, 'six_months', 999, 0),
(3, 'yearly', 1999, 0);

-- Insert Feature Permissions for Trial
INSERT IGNORE INTO feature_permissions (plan_type, feature_key, feature_name)
VALUES 
('trial', 'all_features', 'সব Feature Unlock'),
('trial', 'pos', 'POS'),
('trial', 'inventory', 'Inventory'),
('trial', 'customer', 'Customer'),
('trial', 'supplier', 'Supplier'),
('trial', 'expense', 'Expense'),
('trial', 'accounting', 'Accounting'),
('trial', 'reports', 'Reports'),
('trial', 'multi_branch', 'Multi Branch'),
('trial', 'mobile_app_api', 'Mobile App API'),
('trial', 'ecommerce', 'E-commerce');

-- Insert Feature Permissions for Standard
INSERT IGNORE INTO feature_permissions (plan_type, feature_key, feature_name)
VALUES 
('standard', 'pos', 'POS'),
('standard', 'products', 'Product'),
('standard', 'customer', 'Customer'),
('standard', 'supplier', 'Supplier'),
('standard', 'expense', 'Expense'),
('standard', 'basic_reports', 'Basic Reports');

-- Insert Feature Permissions for Advanced
INSERT IGNORE INTO feature_permissions (plan_type, feature_key, feature_name)
VALUES 
('advanced', 'all_features', 'সব Feature Unlock'),
('advanced', 'pos', 'POS'),
('advanced', 'inventory', 'Inventory'),
('advanced', 'customer', 'Customer'),
('advanced', 'supplier', 'Supplier'),
('advanced', 'expense', 'Expense'),
('advanced', 'accounting', 'Accounting'),
('advanced', 'balance_sheet', 'Balance Sheet'),
('advanced', 'trial_balance', 'Trial Balance'),
('advanced', 'forecasting', 'Forecasting'),
('advanced', 'ecommerce', 'E-commerce'),
('advanced', 'api', 'API Access'),
('advanced', 'flutter_sync', 'Flutter Sync'),
('advanced', 'multi_branch', 'Multi Branch'),
('advanced', 'real_time_dashboard', 'Real-time Dashboard'),
('advanced', 'monitoring', 'Monitoring'),
('advanced', 'whatsapp', 'WhatsApp'),
('advanced', 'sms', 'SMS'),
('advanced', 'payments', 'Payments');
