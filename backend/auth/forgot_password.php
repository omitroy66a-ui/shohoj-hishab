<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers/security.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// FORGOT PASSWORD STEP 1: Request password reset
if ($method === 'POST' && $_GET['action'] === 'request') {
    $email = AuthSecurity::sanitize($_POST['email'] ?? '');
    
    if (!$email || !AuthSecurity::validateEmail($email)) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Invalid email"]);
        exit;
    }
    
    // Check if user exists
    $stmt = $conn->prepare("SELECT id, phone FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    if (!$user) {
        http_response_code(404);
        echo json_encode(["success" => false, "error" => "Email not found"]);
        exit;
    }
    
    // Generate verification codes
    $email_code = AuthSecurity::generateVerificationCode();
    $sms_code = AuthSecurity::generateVerificationCode();
    
    // Clear old codes
    $stmt = $conn->prepare("DELETE FROM verification_codes WHERE email = ? AND code_type = 'forgot_password' AND expires_at < NOW()");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    // Store new verification codes
    $code_type = 'forgot_password';
    $phone = $user['phone'];
    
    $contact_type = 'email';
    $stmt = $conn->prepare("INSERT INTO verification_codes(user_id, email, phone, code, code_type, contact_type, expires_at) VALUES(?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE))");
    $stmt->bind_param("isssss", $user['id'], $email, $phone, $email_code, $code_type, $contact_type);
    $stmt->execute();
    
    // SMS code
    $contact_type = 'sms';
    $stmt->bind_param("isssss", $user['id'], $email, $phone, $sms_code, $code_type, $contact_type);
    $stmt->execute();
    
    // Send codes
    AuthSecurity::sendEmailVerification($email, $email_code, 'forgot_password');
    AuthSecurity::sendSMSVerification($phone, $sms_code);
    
    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "Verification codes sent to email and SMS",
        "next_step" => "verify_reset"
    ]);
    exit;
}

// FORGOT PASSWORD STEP 2: Verify codes and reset password
elseif ($method === 'POST' && $_GET['action'] === 'verify_and_reset') {
    $email = AuthSecurity::sanitize($_POST['email'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $email_code = $_POST['email_code'] ?? '';
    $sms_code = $_POST['sms_code'] ?? '';
    
    // Validate password strength
    $pwd_check = AuthSecurity::validatePasswordStrength($new_password);
    if (!$pwd_check['valid']) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "error" => "Password must be at least 8 characters with uppercase, lowercase, and numbers"
        ]);
        exit;
    }
    
    // Get user
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    if (!$user) {
        http_response_code(404);
        echo json_encode(["success" => false, "error" => "User not found"]);
        exit;
    }
    
    // Verify email code
    $stmt = $conn->prepare("SELECT id FROM verification_codes WHERE email = ? AND code = ? AND code_type = 'forgot_password' AND contact_type = 'email' AND expires_at > NOW() AND is_verified = 0");
    $stmt->bind_param("ss", $email, $email_code);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Invalid or expired email verification code"]);
        exit;
    }
    
    // Verify SMS code
    $stmt = $conn->prepare("SELECT id FROM verification_codes WHERE email = ? AND code = ? AND code_type = 'forgot_password' AND contact_type = 'sms' AND expires_at > NOW() AND is_verified = 0");
    $stmt->bind_param("ss", $email, $sms_code);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Invalid or expired SMS verification code"]);
        exit;
    }
    
    // Hash new password
    $password_hash = AuthSecurity::hashPassword($new_password);
    
    // Update password
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $password_hash, $user['id']);
    $stmt->execute();
    
    // Mark codes as verified
    $stmt = $conn->prepare("UPDATE verification_codes SET is_verified = 1, verified_at = NOW() WHERE email = ? AND code_type = 'forgot_password'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    // Invalidate all existing sessions for this user
    $stmt = $conn->prepare("UPDATE user_sessions SET expires_at = NOW() WHERE user_id = ?");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    
    // Log activity
    $ip = $_SERVER['REMOTE_ADDR'];
    $action = 'password_reset';
    $stmt = $conn->prepare("INSERT INTO activity_logs(user_id, action, module, ip_address) VALUES(?, ?, 'auth', ?)");
    $stmt->bind_param("iss", $user['id'], $action, $ip);
    $stmt->execute();
    
    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "Password reset successful. Please login with your new password."
    ]);
    exit;
}

// Invalid action
http_response_code(400);
echo json_encode(["success" => false, "error" => "Invalid action"]);
