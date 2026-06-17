-- ============================================================
-- SMS SERVICE CONFIGURATION & LOGGING
-- ============================================================

-- SMS Provider Configuration
CREATE TABLE IF NOT EXISTS sms_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    provider VARCHAR(50) UNIQUE NOT NULL, -- twilio, nexmo, local
    api_key VARCHAR(255),
    api_secret VARCHAR(255),
    sender_id VARCHAR(20),
    account_sid VARCHAR(255),
    is_active BOOLEAN DEFAULT FALSE,
    config_json JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SMS Logs (track all sent SMS)
CREATE TABLE IF NOT EXISTS sms_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50), -- payment, subscription, trial, promotion, general
    message_id VARCHAR(255), -- Provider's message ID
    status ENUM('sent', 'delivered', 'failed', 'pending') DEFAULT 'pending',
    provider VARCHAR(50),
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    delivered_at TIMESTAMP NULL,
    INDEX idx_phone (phone),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SMS Campaigns (bulk SMS tracking)
CREATE TABLE IF NOT EXISTS sms_campaigns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message TEXT NOT NULL,
    total_count INT DEFAULT 0,
    sent_count INT DEFAULT 0,
    failed_count INT DEFAULT 0,
    campaign_data JSON, -- Success/failed phone list
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SMS Templates (pre-defined messages)
CREATE TABLE IF NOT EXISTS sms_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_key VARCHAR(100) UNIQUE NOT NULL, -- payment_confirmation, trial_expiring, etc
    template_name VARCHAR(150),
    template_text TEXT NOT NULL,
    variables JSON, -- {business_name, amount, plan_name, etc}
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Link SMS to Subscriptions (track SMS per subscription)
CREATE TABLE IF NOT EXISTS subscription_sms_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_subscription_id INT NOT NULL,
    business_id INT NOT NULL,
    phone_number VARCHAR(20),
    sms_type VARCHAR(50), -- payment_sent, payment_approved, subscription_activated, trial_warning
    message TEXT,
    status VARCHAR(50),
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (business_subscription_id) REFERENCES business_subscriptions(id),
    INDEX idx_business_id (business_id),
    INDEX idx_sent_at (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Local Gateway Configuration (Default)
INSERT IGNORE INTO sms_config (provider, sender_id, is_active)
VALUES (
    'local',
    'SOHOJ_HISHAB',
    TRUE
);

-- Insert SMS Templates
INSERT IGNORE INTO sms_templates (template_key, template_name, template_text, variables) VALUES
(
    'payment_received',
    'Payment Received',
    'Sohoj Hishab: Payment ৳{amount} received for {plan_name} plan (Ref: {payment_number}). Your subscription will be activated soon. Thank you!',
    JSON_OBJECT('amount', 'currency', 'plan_name', 'string', 'payment_number', 'string')
),
(
    'subscription_activated',
    'Subscription Activated',
    'Sohoj Hishab: Your {plan_name} subscription is now active! Valid until {expiry_date}. Start using all features now. Thank you!',
    JSON_OBJECT('plan_name', 'string', 'expiry_date', 'date')
),
(
    'trial_expiring',
    'Trial Expiring',
    'Sohoj Hishab: Your trial expires in {days_left} days. Upgrade now to continue enjoying all features!',
    JSON_OBJECT('days_left', 'number')
),
(
    'payment_approved',
    'Payment Approved',
    'Sohoj Hishab: Your payment has been approved! Your {plan_name} subscription is now active. Enjoy!',
    JSON_OBJECT('plan_name', 'string')
),
(
    'trial_expired',
    'Trial Expired',
    'Sohoj Hishab: Your free trial has expired. Upgrade to {plan_name} plan now to continue using our service.',
    JSON_OBJECT('plan_name', 'string')
);
