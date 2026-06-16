<?php
function sendNotification($conn, $business_id, $title, $body)
{
    $business_id = (int) $business_id;
    $title = trim($title);
    $body = trim($body);

    $stmt = $conn->prepare("INSERT INTO notifications(business_id, title, body) VALUES (?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param('iss', $business_id, $title, $body);
        $stmt->execute();
        $stmt->close();
    }
}
