<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$email = isset($_POST['email']) ? $conn->real_escape_string($_POST['email']) : '';
$user = $conn->query("SELECT * FROM customers WHERE email='$email' LIMIT 1")->fetch_assoc();

echo json_encode([
    'success' => true,
    'user' => $user
]);
