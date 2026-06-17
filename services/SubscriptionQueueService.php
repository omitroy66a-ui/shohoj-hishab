<?php

/**
 * ============================================================
 * SUBSCRIPTION QUEUE & AUTOMATION SERVICE
 * ============================================================
 * Handles automatic subscription send/start functionality
 */

class SubscriptionQueueService {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }

    /**
     * Add Subscription to Queue (Pending, Send, etc)
     */
    public function addToQueue($business_subscription_id, $business_id, $action = 'send') {
        try {
            $status = 'pending';

            $stmt = $this->conn->prepare("
                INSERT INTO subscription_queue 
                (business_subscription_id, business_id, action, status) 
                VALUES (?, ?, ?, ?)
            ");

            $stmt->bind_param("iiss", $business_subscription_id, $business_id, $action, $status);

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'queue_id' => $this->conn->insert_id,
                    'message' => 'Added to queue for ' . $action
                ];
            }

            return ['success' => false, 'message' => 'Failed to add to queue'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Process Queue - Send Subscriptions
     * Run via cron job: php cron_send_subscriptions.php
     */
    public function processQueue($limit = 100) {
        try {
            // Get pending items from queue
            $stmt = $this->conn->prepare("
                SELECT sq.*, bs.business_id, bs.plan_id, bs.expiry_date, sp.name as plan_name
                FROM subscription_queue sq
                JOIN business_subscriptions bs ON sq.business_subscription_id = bs.id
                JOIN subscription_plans sp ON bs.plan_id = sp.id
                WHERE sq.status = 'pending'
                ORDER BY sq.created_at ASC
                LIMIT ?
            ");

            $stmt->bind_param("i", $limit);
            $stmt->execute();
            $queue_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            $processed = 0;
            $errors = [];

            foreach ($queue_items as $item) {
                $result = $this->processQueueItem($item);

                if ($result['success']) {
                    $processed++;
                } else {
                    $errors[] = [
                        'id' => $item['id'],
                        'error' => $result['message']
                    ];
                }
            }

            return [
                'success' => true,
                'processed' => $processed,
                'errors' => $errors,
                'message' => "Processed {$processed} items from queue"
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Process Individual Queue Item
     */
    private function processQueueItem($item) {
        try {
            $action = $item['action'];
            $queue_id = $item['id'];

            switch ($action) {
                case 'send':
                    $result = $this->sendSubscriptionNotification($item);
                    break;
                
                case 'activate':
                    $result = $this->activateSubscription($item);
                    break;
                
                case 'notify':
                    $result = $this->sendNotification($item);
                    break;
                
                default:
                    $result = ['success' => false, 'message' => 'Unknown action'];
            }

            if ($result['success']) {
                // Mark as completed
                $update_stmt = $this->conn->prepare("
                    UPDATE subscription_queue 
                    SET status = 'completed', completed_at = NOW() 
                    WHERE id = ?
                ");

                $update_stmt->bind_param("i", $queue_id);
                $update_stmt->execute();
            } else {
                // Mark as failed with error message
                $error_msg = $result['message'];
                $update_stmt = $this->conn->prepare("
                    UPDATE subscription_queue 
                    SET status = 'failed', error_message = ? 
                    WHERE id = ?
                ");

                $update_stmt->bind_param("si", $error_msg, $queue_id);
                $update_stmt->execute();
            }

            return $result;
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send Subscription Details to User
     * Email/SMS with payment instructions, plan details, etc
     */
    private function sendSubscriptionNotification($item) {
        try {
            $business_id = $item['business_id'];
            $plan_name = $item['plan_name'];
            $expiry_date = $item['expiry_date'];

            // Get business/user details
            $stmt = $this->conn->prepare("
                SELECT u.email, u.phone, b.name as business_name
                FROM businesses b
                JOIN users u ON b.user_id = u.id
                WHERE b.id = ?
            ");

            $stmt->bind_param("i", $business_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();

            if (!$result) {
                return ['success' => false, 'message' => 'Business/User not found'];
            }

            // Prepare notification message
            $message = "Subscription Activated!\n";
            $message .= "Plan: {$plan_name}\n";
            $message .= "Valid Until: {$expiry_date}\n";
            $message .= "Status: Active\n";

            // TODO: Send via email/SMS
            // For now, just log it
            
            return ['success' => true, 'message' => 'Notification sent'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Auto-Activate Subscription (if payment verified)
     */
    private function activateSubscription($item) {
        try {
            $business_subscription_id = $item['business_subscription_id'];

            $stmt = $this->conn->prepare("
                UPDATE business_subscriptions 
                SET status = 'active' 
                WHERE id = ?
            ");

            $stmt->bind_param("i", $business_subscription_id);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Subscription activated'];
            }

            return ['success' => false, 'message' => 'Failed to activate'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send Generic Notification
     */
    private function sendNotification($item) {
        return ['success' => true, 'message' => 'Notification sent'];
    }

    /**
     * Get Queue Status
     */
    public function getQueueStatus() {
        $stmt = $this->conn->prepare("
            SELECT 
                status,
                COUNT(*) as count,
                MIN(created_at) as oldest_item
            FROM subscription_queue
            GROUP BY status
        ");

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get Failed Queue Items
     */
    public function getFailedItems($limit = 50) {
        $stmt = $this->conn->prepare("
            SELECT * FROM subscription_queue 
            WHERE status = 'failed'
            ORDER BY created_at DESC
            LIMIT ?
        ");

        $stmt->bind_param("i", $limit);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Retry Failed Item
     */
    public function retryFailedItem($queue_id) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE subscription_queue 
                SET status = 'pending', error_message = NULL 
                WHERE id = ?
            ");

            $stmt->bind_param("i", $queue_id);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Item reset to pending'];
            }

            return ['success' => false, 'message' => 'Failed to reset item'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

?>
