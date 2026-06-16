<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers/security.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "POST required"]);
    exit;
}

$email = AuthSecurity::sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validate input
if (!$email || !$password) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Email and password required"]);
    exit;
}

// Check brute force (max 5 attempts in 15 minutes)
$stmt = $conn->prepare("SELECT COUNT(*) as attempts FROM login_attempts WHERE email = ? AND is_successful = 0 AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
$stmt->bind_param("s", $email);
$stmt->execute();
$attempts = $stmt->get_result()->fetch_assoc()['attempts'];

if ($attempts >= 5) {
    http_response_code(429);
    echo json_encode(["success" => false, "error" => "Too many login attempts. Try again in 15 minutes."]);
    
    // Log attempt
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $conn->prepare("INSERT INTO login_attempts(email, ip_address, is_successful) VALUES(?, ?, 0)");
    $stmt->bind_param("ss", $email, $ip);
    $stmt->execute();
    exit;
}

// Get user
$stmt = $conn->prepare("SELECT id, name, email, phone, password, role, status FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Check if user exists and status is active
if (!$user || $user['status'] !== 'active') {
    http_response_code(401);
    echo json_encode(["success" => false, "error" => "Invalid email or password"]);
    
    // Log failed attempt
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $conn->prepare("INSERT INTO login_attempts(email, ip_address, is_successful) VALUES(?, ?, 0)");
    $stmt->bind_param("ss", $email, $ip);
    $stmt->execute();
    exit;
}

// Verify password
if (!AuthSecurity::verifyPassword($password, $user['password'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "error" => "Invalid email or password"]);
    
    // Log failed attempt
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $conn->prepare("INSERT INTO login_attempts(email, ip_address, is_successful) VALUES(?, ?, 0)");
    $stmt->bind_param("ss", $email, $ip);
    $stmt->execute();
    exit;
}

// Update last login
$stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
$stmt->bind_param("i", $user['id']);
$stmt->execute();

// Log successful attempt
$ip = $_SERVER['REMOTE_ADDR'];
$stmt = $conn->prepare("INSERT INTO login_attempts(email, ip_address, is_successful) VALUES(?, ?, 1)");
$stmt->bind_param("ss", $email, $ip);
$stmt->execute();

// Set secure session
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

session_regenerate_id(true);

$_SESSION['user_id'] = $user['id'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_role'] = $user['role'];

// Log activity
$action = 'login_success';
$stmt = $conn->prepare("INSERT INTO activity_logs(user_id, action, module, ip_address) VALUES(?, ?, 'auth', ?)");
$stmt->bind_param("iss", $user['id'], $action, $ip);
$stmt->execute();

http_response_code(200);
echo json_encode([
    "success" => true,
    "message" => "Login successful",
    "user" => [
        "id" => $user['id'],
        "name" => $user['name'],
        "email" => $user['email'],
        "role" => $user['role']
    ]
]);
