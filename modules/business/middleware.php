<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function businessId()
{
    return isset($_SESSION['business_id']) ? (int) $_SESSION['business_id'] : 0;
}

function currentBusiness($conn, $user_id)
{
    $user_id = $conn->real_escape_string($user_id);
    $q = $conn->query("SELECT * FROM businesses WHERE owner_id='$user_id' LIMIT 1");
    return $q ? $q->fetch_assoc() : null;
}

function checkSubscription($conn, $business_id)
{
    $business_id = $conn->real_escape_string($business_id);
    $q = $conn->query("SELECT * FROM subscriptions WHERE business_id='$business_id' AND end_date >= CURDATE() LIMIT 1");
    if (!$q || $q->num_rows == 0) {
        header('Content-Type: application/json');
        echo json_encode(["error" => "Subscription Expired"]);
        exit;
    }
    return $q->fetch_assoc();
}
