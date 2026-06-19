# 🗄️ Sohoj Hishab Database Setup Guide

## Database Files Overview

All database schema files are **MySQL syntax** (Not MSSQL/T-SQL). They are properly configured for VS Code.

### 📋 Main Database Files

| File | Purpose | Status |
|------|---------|--------|
| `database/sohoj_hishab.sql` | Main POS & Business Database | ✅ Fixed |
| `database/auth_schema.sql` | User Authentication & Sessions | ✅ Fixed |
| `database/subscription_schema.sql` | Subscription Plans & Payments | ✅ Fixed |
| `database/subscription_updates.sql` | Subscription Features (Nagad, Discounts) | ✅ Fixed |
| `database/sms_config.sql` | SMS Configuration & Templates | ✅ Fixed |
| `database/pos_schema_update.sql` | POS System Enhancements | ✅ Fixed |

---

## 🚀 Quick Setup (Recommended Order)

Run these SQL files in **MySQL** in this exact order:

```sql
-- 1. Main POS Database
mysql -u root -p < database/sohoj_hishab.sql

-- 2. Authentication System
mysql -u root -p sohoj_hishab < database/auth_schema.sql

-- 3. Subscription System (Core)
mysql -u root -p sohoj_hishab < database/subscription_schema.sql

-- 4. Subscription Features (Nagad, Discounts)
mysql -u root -p sohoj_hishab < database/subscription_updates.sql

-- 5. SMS Configuration & Templates
mysql -u root -p sohoj_hishab < database/sms_config.sql

-- 6. POS Enhancements
mysql -u root -p sohoj_hishab < database/pos_schema_update.sql
```

Or run all at once:
```sql
mysql -u root -p sohoj_hishab < database/sohoj_hishab.sql && \
mysql -u root -p sohoj_hishab < database/auth_schema.sql && \
mysql -u root -p sohoj_hishab < database/subscription_schema.sql && \
mysql -u root -p sohoj_hishab < database/subscription_updates.sql && \
mysql -u root -p sohoj_hishab < database/sms_config.sql && \
mysql -u root -p sohoj_hishab < database/pos_schema_update.sql
```

---

## 📊 Database Schema Summary

### Core Tables

#### Users & Authentication
- `users` - User accounts with roles (admin, staff)
- `verification_codes` - Email/SMS verification codes
- `password_reset_tokens` - Password reset token management
- `login_attempts` - Brute force protection logs
- `activity_logs` - Audit trail for all user actions
- `user_sessions` - Database-backed session management

#### POS System
- `categories` - Product categories
- `products` - Products with stock tracking
- `customers` - Customer information & credit tracking
- `suppliers` - Supplier information
- `sales` - Sales transactions
- `sale_items` - Line items for each sale
- `customer_ledger` - Customer credit ledger
- `payments` - Payment tracking
- `purchases` - Purchase orders
- `purchase_items` - Purchase line items
- `supplier_ledger` - Supplier credit ledger

#### Business Management
- `businesses` - Business accounts
- `employees` - Employee directory with salaries
- `business_settings` - Per-business settings
- `expense_categories` - Configurable expense types
- `expenses` - Business expense tracking
- `cashbook` - Cash flow tracking
- `stock_logs` - Stock movement history
- `daily_closing` - Daily financial closing
- `profits` - Profit calculations

#### Subscriptions & Payments
- `subscription_plans` - Available plans (trial, standard, advanced)
- `plan_pricing` - Plan pricing options (monthly, 6-month, yearly)
- `business_subscriptions` - Customer subscriptions
- `subscription_payments` - Payment transactions
- `subscription_history` - Subscription audit log
- `subscription_discounts` - Discount tracking
- `feature_permissions` - Features per plan
- `payment_gateways` - Payment gateway configs (Nagad, bKash, Rocket)
- `subscription_queue` - Subscription automation queue
- `subscription_sms_history` - SMS delivery tracking for subscriptions

#### SMS System
- `sms_config` - SMS provider configuration
- `sms_logs` - SMS delivery logs
- `sms_campaigns` - Bulk SMS campaigns
- `sms_templates` - Pre-defined message templates

#### Additional
- `online_orders` - E-commerce orders
- `notifications` - User notifications
- `roles` - Role definitions
- `permissions` - Permission definitions
- `role_permissions` - Role-permission mapping
- `super_admin` - Super admin accounts
- `printer_settings` - Thermal printer configurations
- `sales_notes` - Additional notes for sales
- `product_variants` - Product variant management

---

## ✅ Fixed Issues

### 1. **Syntax Errors (Now Resolved)**
- ✅ Removed orphan `);` from cashbook table in sohoj_hishab.sql
- ✅ Removed duplicate `employees` table definition
- ✅ Added proper MySQL syntax headers to all files
- ✅ All CREATE TABLE IF NOT EXISTS statements now valid

### 2. **Linter Configuration (Now Resolved)**
- ✅ Created `.sqlfluff` configuration for MySQL dialect
- ✅ Created `.vscode/settings.json` to disable MSSQL linting
- ✅ Associated all .sql files with MySQL language mode
- ✅ All syntax error squiggles will disappear after VS Code restart

---

## 🔧 Configuration Files Created

### `.sqlfluff`
Enforces MySQL dialect for SQL linting with consistent formatting rules.

### `.vscode/settings.json`
```json
{
  "[sql]": {
    "editor.defaultFormatter": "esbenp.prettier-vscode"
  },
  "sql.lintingEnabled": false,
  "mssql.lintingEnabled": false,
  "sqltools.dialects": ["MySQL"],
  "files.associations": {
    "**/*.sql": "mysql"
  },
  "[mysql]": {
    "editor.formatOnSave": false
  }
}
```

---

## 🎯 Key Features

### Authentication System
- Email/SMS verification
- Password reset tokens
- Brute force protection
- Session management
- Activity audit logging

### Subscription Management
- Multiple plans (trial, standard, advanced)
- Flexible pricing (monthly, 6-month, yearly)
- Discount system
- Multiple payment gateways (Nagad, bKash, Rocket)
- Subscription automation queue
- SMS notifications

### POS System
- Complete inventory management
- Multi-customer credit system
- Supplier management
- Purchase orders
- Sales transactions
- Daily financial closing

### SMS Integration
- SMS provider configuration (Twilio, Nexmo, Local)
- SMS templates with variables
- Campaign tracking
- Delivery logs

---

## 📝 Default Data

### Admin User (sohoj_hishab.sql)
```
Name: Administrator
Email: admin@sohojhishab.com
Password: 123456 (MD5 hashed)
Role: admin
```

### Subscription Plans (subscription_schema.sql)
1. **Free Trial** - 3 days, all features
2. **Standard** - ৳60/month, basic features
3. **Advanced** - ৳199/month, all features

### SMS Templates (sms_config.sql)
- Payment Received
- Subscription Activated
- Trial Expiring
- Payment Approved
- Trial Expired

### Payment Gateways (subscription_updates.sql)
- Nagad (01763206165)
- bKash
- Rocket

### Expense Categories (sohoj_hishab.sql)
- Salary
- Rent
- Bills
- Purchase Expenses
- Other

---

## 🚨 Important Notes

⚠️ **DO NOT** rename files to `.mssql.sql` - they are MySQL format!

✅ All files are now properly configured for:
- MySQL 5.7+ or MySQL 8.0+
- MariaDB 10.3+
- AWS RDS MySQL
- Google Cloud SQL
- Azure MySQL

---

## 🔍 Verification Checklist

After running setup, verify:

```sql
-- Check database exists
SHOW DATABASES;

-- Check main tables
USE sohoj_hishab;
SHOW TABLES;

-- Check table structure
DESCRIBE users;
DESCRIBE subscription_plans;
DESCRIBE sms_config;

-- Check indexes are created
SHOW INDEX FROM users;
SHOW INDEX FROM business_subscriptions;

-- Check default data
SELECT * FROM subscription_plans;
SELECT * FROM sms_templates;
SELECT * FROM payment_gateways;
```

---

## 📞 Troubleshooting

### "Error: CREATE TABLE IF NOT EXISTS"
- **Cause**: MSSQL linter is active
- **Solution**: Restart VS Code, check `.vscode/settings.json` is in place

### "Duplicate entry for key"
- **Cause**: Running INSERT IGNORE multiple times
- **Solution**: Use `INSERT IGNORE` (already in scripts) or clear old data first

### "Foreign key constraint fails"
- **Cause**: Tables referenced before they're created
- **Solution**: Run files in the recommended order above

### "Unknown character set"
- **Cause**: UTF-8 encoding issues
- **Solution**: Ensure MySQL is configured for UTF-8MB4:
  ```sql
  ALTER DATABASE sohoj_hishab CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  ```

---

## 📦 Next Steps

1. ✅ Run database setup scripts
2. ✅ Configure MySQL connection in backend (`api/config/database.php`)
3. ✅ Run database seeder scripts (`database/seeders/`)
4. ✅ Update frontend API endpoints
5. ✅ Test subscription system workflows
6. ✅ Configure SMS gateways in admin panel
7. ✅ Setup payment gateway credentials

---

**Last Updated**: 2025-06-20  
**Status**: All database schemas fixed and optimized ✅
