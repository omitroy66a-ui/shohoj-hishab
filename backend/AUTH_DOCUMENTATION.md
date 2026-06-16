# 🔐 Advanced Authentication System

## Overview

Secure authentication system with:
- ✅ Password hashing (BCRYPT)
- ✅ Email & SMS verification codes
- ✅ Forgot password with verification
- ✅ Brute force protection
- ✅ Session management
- ✅ Audit logging
- ✅ CSRF protection

---

## 📁 File Structure

```
backend/
├── auth/
│   ├── register.php         (Registration with verification)
│   ├── login.php           (Secure login)
│   ├── forgot_password.php (Password recovery)
│   └── logout.php          (Session destruction)
│
├── middleware/
│   └── auth.php            (Session & CSRF functions)
│
└── helpers/
    └── security.php        (Password & verification functions)
```

---

## 🗄️ Database Schema

```sql
-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255),      -- BCRYPT hash (NOT plaintext)
    role VARCHAR(50),
    status VARCHAR(20),
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Verification codes (email & SMS)
CREATE TABLE verification_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    email VARCHAR(255),
    phone VARCHAR(20),
    code VARCHAR(10),           -- 6-digit code
    code_type VARCHAR(50),      -- 'register', 'forgot_password'
    contact_type VARCHAR(20),   -- 'email', 'sms'
    is_verified TINYINT,
    attempts INT,
    expires_at TIMESTAMP
);

-- Login attempt logs (brute force protection)
CREATE TABLE login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255),
    ip_address VARCHAR(45),
    is_successful TINYINT,
    created_at TIMESTAMP
);

-- Activity audit logs
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100),        -- 'login', 'logout', 'register'
    module VARCHAR(100),
    ip_address VARCHAR(45),
    created_at TIMESTAMP
);
```

---

## 📝 Registration Flow

### Step 1: Send Verification Codes

```bash
POST /backend/auth/register.php?action=register_request
Content-Type: application/x-www-form-urlencoded

name=John Doe
email=john@example.com
phone=01712345678
```

**Response:**
```json
{
    "success": true,
    "message": "Verification codes sent to email and SMS",
    "next_step": "verify_registration"
}
```

**What happens:**
- ✅ Validates email and phone
- ✅ Generates 6-digit code for email
- ✅ Generates 6-digit code for SMS
- ✅ Sends email with code
- ✅ Sends SMS with code
- ✅ Codes expire in 15 minutes

---

### Step 2: Verify Codes & Create Account

```bash
POST /backend/auth/register.php?action=register_verify
Content-Type: application/x-www-form-urlencoded

email=john@example.com
phone=01712345678
name=John Doe
password=SecurePass123!
email_code=123456
sms_code=654321
```

**Response:**
```json
{
    "success": true,
    "message": "Registration successful! Please login.",
    "user_id": 42
}
```

**What happens:**
- ✅ Validates both codes (email & SMS)
- ✅ Checks code expiry (15 min)
- ✅ Validates password strength (8+ chars, uppercase, lowercase, numbers)
- ✅ Hashes password with BCRYPT (cost 12)
- ✅ Creates user account
- ✅ Marks codes as verified

---

## 🔑 Login Flow

```bash
POST /backend/auth/login.php
Content-Type: application/x-www-form-urlencoded

email=john@example.com
password=SecurePass123!
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "user": {
        "id": 42,
        "name": "John Doe",
        "email": "john@example.com",
        "role": "user"
    }
}
```

**Security features:**
- ✅ Brute force protection (max 5 failed attempts in 15 min)
- ✅ Constant-time password comparison
- ✅ Session regeneration
- ✅ Login attempt logging
- ✅ Activity audit trail
- ✅ Last login timestamp

---

## 🔄 Forgot Password Flow

### Step 1: Request Password Reset

```bash
POST /backend/auth/forgot_password.php?action=request
Content-Type: application/x-www-form-urlencoded

email=john@example.com
```

**Response:**
```json
{
    "success": true,
    "message": "Verification codes sent to email and SMS",
    "next_step": "verify_reset"
}
```

---

### Step 2: Verify Codes & Reset Password

```bash
POST /backend/auth/forgot_password.php?action=verify_and_reset
Content-Type: application/x-www-form-urlencoded

email=john@example.com
new_password=NewSecurePass456!
email_code=123456
sms_code=654321
```

**Response:**
```json
{
    "success": true,
    "message": "Password reset successful. Please login with your new password."
}
```

**What happens:**
- ✅ Verifies both email & SMS codes
- ✅ Validates new password strength
- ✅ Hashes new password with BCRYPT
- ✅ Updates password in database
- ✅ Invalidates all existing sessions
- ✅ Logs password reset activity

---

## 🚪 Logout

```bash
POST /backend/auth/logout.php
```

**Response:**
```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

**What happens:**
- ✅ Logs logout activity
- ✅ Destroys session
- ✅ Clears session cookies

---

## 🛡️ Security Features

### 1. Password Hashing
- Uses BCRYPT with cost 12
- Passwords NEVER stored in plaintext
- Passwords NEVER sent in emails/SMS
- Verification codes used instead

### 2. Verification Codes
- 6-digit random code
- Different codes for email & SMS
- Expires in 15 minutes
- Max 5 attempts per code
- Can't reuse verified codes

### 3. Brute Force Protection
- Max 5 failed login attempts in 15 minutes
- IP-based rate limiting
- Temporary account lockout
- All attempts logged

### 4. Session Security
- HttpOnly cookies (no JavaScript access)
- Secure flag (HTTPS only)
- SameSite=Strict
- Session regeneration after login
- Session invalidation on password reset

### 5. Audit Logging
- All authentication activities logged
- IP address recorded
- User agent logged
- Timestamps for all events
- Queryable for security analysis

### 6. CSRF Protection
- Token generation
- Token validation on form submission
- Double-submit cookie pattern ready

---

## 🔌 Using in Your Application

### Check if user is logged in

```php
require_once 'backend/middleware/auth.php';

$user = getCurrentUser();
if ($user) {
    echo "Logged in as " . $user['name'];
} else {
    echo "Not logged in";
}
```

### Require authentication for page

```php
require_once 'backend/middleware/auth.php';

requireAuth();  // Exits if not logged in
// Code here only runs if authenticated
```

### Check user role

```php
require_once 'backend/middleware/auth.php';

if (hasRole(['admin', 'manager'])) {
    // Show admin panel
}

// Or require role
requireRole(['admin']);  // Exits if not admin
```

### Generate CSRF token for forms

```php
require_once 'backend/middleware/auth.php';

?>
<form method="POST">
    <?php echo getCSRFTokenField(); ?>
    <input type="text" name="product_name">
    <button type="submit">Save</button>
</form>
```

---

## 📧 Email Configuration

Edit `backend/helpers/security.php` to configure your mail server:

```php
// Option 1: Using PHP mail() function
mail($email, $subject, $message, $headers);

// Option 2: Using SMTP (Recommended)
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'smtp.mailtrap.io';
$mail->Port = 2525;
$mail->Username = 'YOUR_USERNAME';
$mail->Password = 'YOUR_PASSWORD';
$mail->setFrom('noreply@example.com', 'Sohoj Hishab');
$mail->addAddress($email);
$mail->Subject = $subject;
$mail->isHTML(true);
$mail->Body = $message;
$mail->send();
```

---

## 📱 SMS Configuration

Edit `backend/helpers/security.php` to configure SMS provider:

```php
// Using Twilio
require_once 'vendor/autoload.php';
use Twilio\Rest\Client;

$account_sid = 'YOUR_ACCOUNT_SID';
$auth_token = 'YOUR_AUTH_TOKEN';
$client = new Client($account_sid, $auth_token);

$message = $client->messages->create(
    $phone,
    [
        'from' => '+1234567890',
        'body' => "Your verification code is: $code"
    ]
);

// Using Nexmo/Vonage
$basic = new \Vonage\Client\Credentials\Basic('API_KEY', 'API_SECRET');
$client = new \Vonage\Client($basic);
$response = $client->sms()->sendMessage(
    new \Vonage\SMS\Message\SMS('44...', 'Sohoj Hishab', $message)
);
```

---

## 🧪 Testing

### Test Registration
```bash
# Step 1: Request codes
curl -X POST http://localhost/backend/auth/register.php?action=register_request \
  -d "name=Test&email=test@example.com&phone=01712345678"

# Step 2: Verify (use codes from email/SMS output)
curl -X POST http://localhost/backend/auth/register.php?action=register_verify \
  -d "email=test@example.com&phone=01712345678&name=Test&password=TestPass123&email_code=123456&sms_code=654321"
```

### Test Login
```bash
curl -X POST http://localhost/backend/auth/login.php \
  -d "email=test@example.com&password=TestPass123"
```

### Test Forgot Password
```bash
# Step 1: Request reset
curl -X POST http://localhost/backend/auth/forgot_password.php?action=request \
  -d "email=test@example.com"

# Step 2: Reset with codes
curl -X POST http://localhost/backend/auth/forgot_password.php?action=verify_and_reset \
  -d "email=test@example.com&new_password=NewPass456&email_code=123456&sms_code=654321"
```

---

## ⚠️ Important Security Notes

1. **ALWAYS use HTTPS** in production
2. **Never log sensitive data** (passwords, codes)
3. **Rate limit API endpoints** to prevent abuse
4. **Monitor failed login attempts** for suspicious activity
5. **Implement 2FA** for admin accounts (optional future enhancement)
6. **Keep BCRYPT cost high** (12 is recommended for production)
7. **Regularly rotate secrets** and security keys
8. **Use environment variables** for sensitive config

---

## 🐛 Troubleshooting

### Emails not sending?
- Check mail server configuration
- Verify SMTP credentials
- Check spam folder
- Enable "Less secure apps" if using Gmail

### SMS not sending?
- Verify API credentials
- Check phone number format
- Ensure API account has SMS credits
- Check rate limiting

### Verification codes not working?
- Verify codes are within 15-minute window
- Check codes match exactly
- Ensure database has tables created
- Check code_type matches (register vs forgot_password)

### Login always fails?
- Verify email exists in database
- Check password is correct
- Check user status is "active"
- Look in activity_logs for errors

---

## 📊 Queries for Analysis

### Check failed login attempts
```sql
SELECT email, COUNT(*) as attempts, MAX(created_at) as last_attempt
FROM login_attempts
WHERE is_successful = 0 AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY email
ORDER BY attempts DESC;
```

### Check recent logins
```sql
SELECT user_id, email, COUNT(*) as login_count, MAX(last_login) as latest
FROM users
WHERE last_login IS NOT NULL
GROUP BY user_id
ORDER BY latest DESC
LIMIT 20;
```

### Check activity trail for user
```sql
SELECT * FROM activity_logs
WHERE user_id = 42
ORDER BY created_at DESC
LIMIT 50;
```

---

**Version:** 1.0.0  
**Last Updated:** 2024  
**Status:** Production Ready ✅
