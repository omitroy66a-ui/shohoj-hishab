<?php

/**
 * ============================================================
 * CRON JOB: Auto-Expire Subscriptions
 * ============================================================
 * Run this daily via cron: 0 0 * * * php /path/to/cron_expire_subscriptions.php
 * Or call via: curl https://yoursite.com/cron_expire_subscriptions.php?token=your_secret_token
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/services/SubscriptionService.php';

// Security: Check token
$secret_token = 'your_secure_cron_token_here'; // Change this
$provided_token = isset($_GET['token']) ? $_GET['token'] : '';

if ($provided_token !== $secret_token) {
    http_response_code(401);
    die('Unauthorized');
}

try {
    $subscriptionService = new SubscriptionService($conn);
    
    // Auto-expire subscriptions
    $result = $subscriptionService->autoExpireSubscriptions();
    
    if ($result) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Subscriptions expired successfully',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
        // Log to file (optional)
        $log_file = __DIR__ . '/logs/cron_subscription.log';
        if (!is_dir(__DIR__ . '/logs')) {
            mkdir(__DIR__ . '/logs', 0755, true);
        }
        file_put_contents(
            $log_file, 
            date('Y-m-d H:i:s') . " - Cron job executed successfully\n",
            FILE_APPEND
        );
    } else {
        http_response_code(200);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to expire subscriptions'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

?>
