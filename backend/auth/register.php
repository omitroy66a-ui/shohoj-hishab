<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers/security.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// REGISTER STEP 1: Send verification code
if ($method === 'POST' && $_GET['action'] === 'register_request') {
    $email = AuthSecurity::sanitize($_POST['email'] ?? '');
    $phone = AuthSecurity::sanitize($_POST['phone'] ?? '');
    $name = AuthSecurity::sanitize($_POST['name'] ?? '');
    
    // Validate inputs
    if (!$email || !AuthSecurity::validateEmail($email)) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Invalid email"]);
        exit;
    }
    
    if (!$phone || !AuthSecurity::validatePhone($phone)) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Invalid phone"]);
        exit;
    }
    
    // Check if user already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
    $stmt->bind_param("ss", $email, $phone);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Email or phone already registered"]);
        exit;
    }
    
    // Generate verification codes
    $email_code = AuthSecurity::generateVerificationCode();
    $sms_code = AuthSecurity::generateVerificationCode();
    
    // Store verification codes
    $code_type = 'register';
    $stmt = $conn->prepare("INSERT INTO verification_codes(email, phone, code, code_type, contact_type, expires_at) VALUES(?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE))");
    
    // Email code
    $contact_type = 'email';
    $stmt->bind_param("sssss", $email, $phone, $email_code, $code_type, $contact_type);
    $stmt->execute();
    
    // SMS code
    $contact_type = 'sms';
    $stmt->bind_param("sssss", $email, $phone, $sms_code, $code_type, $contact_type);
    $stmt->execute();
    
    // Send verification codes
    AuthSecurity::sendEmailVerification($email, $email_code, 'register');
    AuthSecurity::sendSMSVerification($phone, $sms_code);
    
    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "Verification codes sent to email and SMS",
        "next_step" => "verify_registration"
    ]);
    exit;
}

// REGISTER STEP 2: Verify codes and create account
elseif ($method === 'POST' && $_GET['action'] === 'register_verify') {
    $email = AuthSecurity::sanitize($_POST['email'] ?? '');
    $phone = AuthSecurity::sanitize($_POST['phone'] ?? '');
    $name = AuthSecurity::sanitize($_POST['name'] ?? '');
    $password = $_POST['password'] ?? '';
    $email_code = $_POST['email_code'] ?? '';
    $sms_code = $_POST['sms_code'] ?? '';
    
    // Validate password strength
    $pwd_check = AuthSecurity::validatePasswordStrength($password);
    if (!$pwd_check['valid']) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "error" => "Password must be at least 8 characters with uppercase, lowercase, and numbers"
        ]);
        exit;
    }
    
    // Verify email code
    $stmt = $conn->prepare("SELECT id, is_verified FROM verification_codes WHERE email = ? AND code = ? AND code_type = 'register' AND contact_type = 'email' AND expires_at > NOW()");
    $stmt->bind_param("ss", $email, $email_code);
    $stmt->execute();
    $email_result = $stmt->get_result()->fetch_assoc();
    
    if (!$email_result) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Invalid or expired email verification code"]);
        exit;
    }
    
    // Verify SMS code
    $stmt = $conn->prepare("SELECT id, is_verified FROM verification_codes WHERE phone = ? AND code = ? AND code_type = 'register' AND contact_type = 'sms' AND expires_at > NOW()");
    $stmt->bind_param("ss", $phone, $sms_code);
    $stmt->execute();
    $sms_result = $stmt->get_result()->fetch_assoc();
    
    if (!$sms_result) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Invalid or expired SMS verification code"]);
        exit;
    }
    
    // Hash password
    $password_hash = AuthSecurity::hashPassword($password);
    
    // Create user
    $stmt = $conn->prepare("INSERT INTO users(name, email, phone, password, status) VALUES(?, ?, ?, ?, 'active')");
    $stmt->bind_param("ssss", $name, $email, $phone, $password_hash);
    $stmt->execute();
    $user_id = $conn->insert_id;
    
    // Mark codes as verified
    $stmt = $conn->prepare("UPDATE verification_codes SET is_verified = 1, verified_at = NOW() WHERE (id = ? OR id = ?)");
    $stmt->bind_param("ii", $email_result['id'], $sms_result['id']);
    $stmt->execute();
    
    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "Registration successful! Please login.",
        "user_id" => $user_id
    ]);
    exit;
}

// Invalid action
http_response_code(400);
echo json_encode(["success" => false, "error" => "Invalid action"]);
