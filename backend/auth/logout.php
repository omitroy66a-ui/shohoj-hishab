<?php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'] ?? null;
$ip = $_SERVER['REMOTE_ADDR'];

// Log logout activity
if ($user_id) {
    $action = 'logout';
    $stmt = $conn->prepare("INSERT INTO activity_logs(user_id, action, module, ip_address) VALUES(?, ?, 'auth', ?)");
    $stmt->bind_param("iss", $user_id, $action, $ip);
    $stmt->execute();
}

// Destroy session
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

http_response_code(200);
echo json_encode([
    "success" => true,
    "message" => "Logged out successfully"
]);
