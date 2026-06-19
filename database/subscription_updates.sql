-- ============================================================
-- SUBSCRIPTION SYSTEM UPDATES
-- MySQL Syntax (Not MSSQL/T-SQL)
-- ============================================================
-- Add Nagad payment config and discount system

-- Update subscription_plans with discount column
ALTER TABLE subscription_plans ADD COLUMN discount_percentage DECIMAL(5,2) DEFAULT 0 AFTER is_active;

-- Update business_subscriptions with discount tracking
ALTER TABLE business_subscriptions 
ADD COLUMN original_price DECIMAL(10,2) DEFAULT 0 AFTER expiry_date,
ADD COLUMN discounted_price DECIMAL(10,2) DEFAULT 0 AFTER original_price,
ADD COLUMN discount_reason VARCHAR(255) AFTER discounted_price,
ADD COLUMN discount_applied_by INT AFTER discount_reason,
ADD COLUMN discount_applied_at TIMESTAMP NULL AFTER discount_applied_by;

-- Update subscription_payments with payment status tracking
ALTER TABLE subscription_payments 
ADD COLUMN payment_gateway VARCHAR(50) DEFAULT 'online' AFTER payment_method,
ADD COLUMN gateway_reference VARCHAR(255) AFTER payment_gateway;

-- Create payment gateway config table
CREATE TABLE IF NOT EXISTS payment_gateways (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gateway_name VARCHAR(50) UNIQUE NOT NULL,
    phone_number VARCHAR(20),
    account_name VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    config_data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create subscription discount history
CREATE TABLE IF NOT EXISTS subscription_discounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_subscription_id INT NOT NULL,
    business_id INT NOT NULL,
    original_amount DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) NOT NULL,
    discount_percentage DECIMAL(5,2),
    discount_reason VARCHAR(255),
    applied_by INT,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (business_subscription_id) REFERENCES business_subscriptions(id) ON DELETE CASCADE,
    INDEX idx_business_id (business_id),
    INDEX idx_applied_at (applied_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Nagad Payment Gateway
INSERT IGNORE INTO payment_gateways (gateway_name, phone_number, account_name, is_active, config_data)
VALUES (
    'Nagad',
    '01763206165',
    'Sohoj Hishab',
    TRUE,
    JSON_OBJECT('status', 'active', 'merchant_id', 'SOHOJ_HISHAB')
);

-- Insert other payment gateways
INSERT IGNORE INTO payment_gateways (gateway_name, phone_number, account_name, is_active, config_data)
VALUES 
(
    'bKash',
    '01700000000',
    'Sohoj Hishab',
    TRUE,
    JSON_OBJECT('status', 'active')
),
(
    'Rocket',
    '01700000000',
    'Sohoj Hishab',
    TRUE,
    JSON_OBJECT('status', 'active')
);

-- Create subscription queue/log table for automation
CREATE TABLE IF NOT EXISTS subscription_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_subscription_id INT NOT NULL,
    business_id INT NOT NULL,
    action VARCHAR(50), -- 'send', 'activate', 'notify'
    status VARCHAR(50) DEFAULT 'pending', -- pending, sent, completed, failed
    error_message TEXT,
    sent_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (business_subscription_id) REFERENCES business_subscriptions(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
