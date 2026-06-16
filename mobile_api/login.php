<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../modules/business/middleware.php';

$email = isset($_POST['email']) ? $conn->real_escape_string($_POST['email']) : '';
$business_id = businessId();

$user = $conn->query("SELECT * FROM customers WHERE email='$email' AND business_id='$business_id' LIMIT 1")->fetch_assoc();

if ($user) {
    echo json_encode([
        'success' => true,
        'user' => $user
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid login or business'
    ]);
}
