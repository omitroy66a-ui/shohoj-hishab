<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Not authenticated"]);
    exit;
}

require_once __DIR__ . '/../../config/database.php';
$userId = (int) $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id=$userId")->fetch_assoc();

if ($user) {
    echo json_encode(["success" => true, "user" => $user]);
} else {
    echo json_encode(["success" => false, "message" => "User not found"]);
}
