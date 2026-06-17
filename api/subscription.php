<?php

/**
 * ============================================================
 * SUBSCRIPTION API ENDPOINTS
 * ============================================================
 * Handles subscription upgrade, payment, and management
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/SubscriptionService.php';
require_once __DIR__ . '/../services/SubscriptionMiddleware.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

$subscriptionService = new SubscriptionService($conn);
$subscriptionMiddleware = new SubscriptionMiddleware($conn);

// Get business_id from session
if (!isset($_SESSION['business_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$business_id = $_SESSION['business_id'];

// ============================================================
// ENDPOINT: Get Current Subscription Status
// ============================================================
if ($action === 'get_status' && $method === 'GET') {
    $status = $subscriptionMiddleware->getSubscriptionStatus($business_id);
    echo json_encode(['success' => true, 'data' => $status]);
    exit;
}

// ============================================================
// ENDPOINT: Get All Available Plans
// ============================================================
if ($action === 'get_plans' && $method === 'GET') {
    $plans = $subscriptionService->getAllPlans();
    
    // Format plans with pricing
    $formatted_plans = [];
    foreach ($plans as $plan) {
        $pricing = [];
        if ($plan['pricing']) {
            foreach (explode('|', $plan['pricing']) as $p) {
                list($type, $price) = explode(':', $p);
                $pricing[$type] = $price;
            }
        }
        $plan['pricing_options'] = $pricing;
        unset($plan['pricing']);
        $formatted_plans[] = $plan;
    }
    
    echo json_encode(['success' => true, 'data' => $formatted_plans]);
    exit;
}

// ============================================================
// ENDPOINT: Upgrade Subscription (Create pending subscription)
// ============================================================
if ($action === 'upgrade' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $plan_id = $input['plan_id'] ?? null;
    $duration_type = $input['duration_type'] ?? 'monthly'; // monthly, six_months, yearly

    if (!$plan_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Plan ID required']);
        exit;
    }

    $result = $subscriptionService->upgradeSubscription($business_id, $plan_id, $duration_type);
    
    if ($result['success']) {
        http_response_code(200);
    } else {
        http_response_code(400);
    }
    
    echo json_encode($result);
    exit;
}

// ============================================================
// ENDPOINT: Process Payment (Step 1: Record payment)
// ============================================================
if ($action === 'pay' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $business_subscription_id = $input['subscription_id'] ?? null;
    $payment_number = $input['payment_number'] ?? null;
    $transaction_id = $input['transaction_id'] ?? null;
    $amount = $input['amount'] ?? null;
    $payment_method = $input['payment_method'] ?? 'online';

    // Validate
    if (!$business_subscription_id || !$payment_number || !$transaction_id || !$amount) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Missing required fields: subscription_id, payment_number, transaction_id, amount'
        ]);
        exit;
    }

    // Verify amount matches subscription
    $subscription = $subscriptionService->getSubscription($business_subscription_id);
    if (!$subscription) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Subscription not found']);
        exit;
    }

    // Process payment
    $result = $subscriptionService->processPayment(
        $business_subscription_id,
        $payment_number,
        $transaction_id,
        $amount,
        $payment_method
    );

    if ($result['success']) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'payment_id' => $result['payment_id'],
            'message' => 'Payment recorded. Pending admin review for activation.',
            'status' => 'pending'
        ]);
    } else {
        http_response_code(400);
        echo json_encode($result);
    }
    exit;
}

// ============================================================
// ENDPOINT: Check Subscription Status
// ============================================================
if ($action === 'check' && $method === 'GET') {
    $check_result = $subscriptionMiddleware->checkSubscription($business_id);
    
    if (!$check_result['has_subscription']) {
        http_response_code(403);
    } else {
        http_response_code(200);
    }
    
    echo json_encode($check_result);
    exit;
}

// ============================================================
// ENDPOINT: Check Feature Access
// ============================================================
if ($action === 'check_feature' && $method === 'GET') {
    $feature_key = $_GET['feature'] ?? null;
    
    if (!$feature_key) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Feature parameter required']);
        exit;
    }

    $result = $subscriptionMiddleware->canAccessFeature($business_id, $feature_key);
    
    if (!$result['can_access']) {
        http_response_code(403);
        $result['message'] = $subscriptionMiddleware->getRestrictedFeatureMessage($business_id, $feature_key);
    }
    
    echo json_encode($result);
    exit;
}

// ============================================================
// ENDPOINT: Get Trial Expiry Info
// ============================================================
if ($action === 'trial_expiry' && $method === 'GET') {
    $trial_info = $subscriptionMiddleware->checkTrialExpiry($business_id);
    
    if ($trial_info === null) {
        echo json_encode(['success' => false, 'message' => 'Not on trial plan']);
    } else {
        echo json_encode(['success' => true, 'data' => $trial_info]);
    }
    exit;
}

// ============================================================
// ENDPOINT: Get Available Features
// ============================================================
if ($action === 'features' && $method === 'GET') {
    $features = $subscriptionMiddleware->getAvailableFeatures($business_id);
    echo json_encode(['success' => true, 'data' => $features]);
    exit;
}

// ADMIN ENDPOINTS (Only for super admin)
// ============================================================

// Check if user is admin
$is_admin = isset($_SESSION['role']) && in_array($_SESSION['role'], ['super_admin', 'admin']);

// ============================================================
// ENDPOINT: Get Pending Payments (Admin)
// ============================================================
if ($action === 'pending_payments' && $method === 'GET' && $is_admin) {
    $limit = $_GET['limit'] ?? 50;
    $payments = $subscriptionService->getPendingPayments($limit);
    echo json_encode(['success' => true, 'data' => $payments]);
    exit;
}

// ============================================================
// ENDPOINT: Approve Payment and Auto-Activate (Admin)
// ============================================================
if ($action === 'approve_payment' && $method === 'POST' && $is_admin) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $payment_id = $input['payment_id'] ?? null;
    $reviewed_by = $_SESSION['user_id'] ?? null;

    if (!$payment_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Payment ID required']);
        exit;
    }

    // IMPORTANT: Auto-activate subscription
    $result = $subscriptionService->approvePaymentAndActivate($payment_id, $reviewed_by);

    if ($result['success']) {
        http_response_code(200);
    } else {
        http_response_code(400);
    }
    
    echo json_encode($result);
    exit;
}

// ============================================================
// ENDPOINT: Reject Payment (Admin)
// ============================================================
if ($action === 'reject_payment' && $method === 'POST' && $is_admin) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $payment_id = $input['payment_id'] ?? null;
    $rejection_reason = $input['reason'] ?? 'Admin rejection';
    $reviewed_by = $_SESSION['user_id'] ?? null;

    if (!$payment_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Payment ID required']);
        exit;
    }

    $result = $subscriptionService->rejectPayment($payment_id, $rejection_reason, $reviewed_by);

    if ($result['success']) {
        http_response_code(200);
    } else {
        http_response_code(400);
    }
    
    echo json_encode($result);
    exit;
}

// Default: No action found
http_response_code(404);
echo json_encode(['success' => false, 'message' => 'Action not found']);

?>
