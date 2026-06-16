<?php

require_once __DIR__ . '/config/database.php';
session_start();

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($email) || empty($password) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: login.php?error=1");
    exit();
}

$hashedPassword = md5($password);

$stmt = $conn->prepare("SELECT id, name, role FROM users WHERE email = ? AND password = ? LIMIT 1");
if (!$stmt) {
    header("Location: login.php?error=1");
    exit();
}

$stmt->bind_param("ss", $email, $hashedPassword);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['role'] = $user['role'];

    $businessStmt = $conn->prepare("SELECT id FROM businesses WHERE owner_id = ? LIMIT 1");
    if ($businessStmt) {
        $businessStmt->bind_param("i", $user['id']);
        $businessStmt->execute();
        $businessResult = $businessStmt->get_result();
        $_SESSION['business_id'] = ($businessResult && $businessResult->num_rows > 0)
            ? $businessResult->fetch_assoc()['id']
            : 0;
        $businessStmt->close();
    } else {
        $_SESSION['business_id'] = 0;
    }

    $stmt->close();
    header("Location: admin/dashboard.php");
    exit();
}

$stmt->close();
header("Location: login.php?error=1");
exit();
