<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/SubscriptionService.php';

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
    $user_id = $insertStmt->insert_id;
    $insertStmt->close();
    
    // Create auto business/shop for user (if needed)
    // Assuming users have associated businesses. Adjust as per your schema
    $business_name = $name . "'s Shop";
    $businessStmt = $conn->prepare('INSERT INTO businesses (user_id, name) VALUES (?, ?)');
    $businessStmt->bind_param('is', $user_id, $business_name);
    
    if ($businessStmt->execute()) {
        $business_id = $businessStmt->insert_id;
        $businessStmt->close();
        
        // AUTO TRIAL ACTIVATION
        $subscriptionService = new SubscriptionService($conn);
        $trial_result = $subscriptionService->createTrialSubscription($business_id);
        
        if ($trial_result['success']) {
            // Redirect to login with success message
            header('Location: ../login.php?registered=1&trial=activated');
            exit;
        }
    } else {
        $businessStmt->close();
        // Still redirect even if business creation fails - user can be created manually
        header('Location: ../login.php?registered=1');
        exit;
    }
}

$insertStmt->close();
echo 'Registration failed. Please try again.';
