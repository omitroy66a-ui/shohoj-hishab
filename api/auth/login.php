<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

if ($email === '' || $password === '') {
    echo json_encode(["success" => false, "message" => "Email and password are required"]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Please provide a valid email address"]);
    exit;
}

$hashedPassword = md5($password);
$stmt = $conn->prepare('SELECT id, name FROM users WHERE email = ? AND password = ? LIMIT 1');
$stmt->bind_param('ss', $email, $hashedPassword);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];

    echo json_encode([
        "success" => true,
        "user" => $user
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid login"
    ]);
}
$stmt->close();
