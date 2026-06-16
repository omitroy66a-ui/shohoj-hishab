<?php

require_once __DIR__ . '/../config/database.php';

$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

if ($name === '' || $email === '' || $password === '') {
    echo 'All fields are required.';
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo 'Please provide a valid email address.';
    exit;
}

$checkStmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$checkStmt->bind_param('s', $email);
$checkStmt->execute();
$checkStmt->store_result();
if ($checkStmt->num_rows > 0) {
    $checkStmt->close();
    echo 'This email is already registered.';
    exit;
}
$checkStmt->close();

$hashedPassword = md5($password);
$insertStmt = $conn->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
$role = 'staff';
$insertStmt->bind_param('ssss', $name, $email, $hashedPassword, $role);

if ($insertStmt->execute()) {
    $insertStmt->close();
    header('Location: ../login.php?registered=1');
    exit;
}

$insertStmt->close();
echo 'Registration failed. Please try again.';
