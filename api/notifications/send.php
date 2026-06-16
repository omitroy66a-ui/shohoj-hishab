<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

$business_id = isset($_POST['business_id']) ? (int) $_POST['business_id'] : 0;
$title = isset($_POST['title']) ? $conn->real_escape_string($_POST['title']) : '';
$body = isset($_POST['body']) ? $conn->real_escape_string($_POST['body']) : '';

if ($business_id <= 0 || $title === '' || $body === '') {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit;
}

$conn->query("INSERT INTO notifications(business_id, title, body) VALUES($business_id, '$title', '$body')");

if ($conn->affected_rows > 0) {
    echo json_encode(["success" => true, "notification_id" => $conn->insert_id]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to send notification"]);
}
