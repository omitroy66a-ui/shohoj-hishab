<?php

/**
 * ============================================================
 * CRON JOB: Automatic Subscription Send/Start
 * ============================================================
 * Processes subscription queue - sends to users, activates plans
 * Run this every hour via cron: 0 * * * * php /path/to/cron_send_subscriptions.php
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/services/SubscriptionQueueService.php';

// Security: Check token
$secret_token = 'your_secure_cron_token_here'; // Same as cron_expire_subscriptions.php
$provided_token = isset($_GET['token']) ? $_GET['token'] : '';

if ($provided_token !== $secret_token) {
    http_response_code(401);
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

header('Content-Type: application/json');

try {
    $queueService = new SubscriptionQueueService($conn);
    
    // Process up to 100 items from queue
    $result = $queueService->processQueue(100);
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Queue processed',
        'processed' => $result['processed'],
        'errors' => $result['errors'],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
    // Log to file
    $log_file = __DIR__ . '/logs/cron_send_subscriptions.log';
    if (!is_dir(__DIR__ . '/logs')) {
        mkdir(__DIR__ . '/logs', 0755, true);
    }
    
    file_put_contents(
        $log_file,
        date('Y-m-d H:i:s') . " - Processed {$result['processed']} items\n",
        FILE_APPEND
    );
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

?>
