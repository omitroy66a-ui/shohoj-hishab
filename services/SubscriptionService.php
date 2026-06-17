<?php

/**
 * ============================================================
 * SUBSCRIPTION SERVICE CLASS
 * ============================================================
 * Handles all subscription related operations
 */

class SubscriptionService {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }

    /**
     * Auto Trial Activation on User Registration
     * Called when new business/user registers
     */
    public function createTrialSubscription($business_id) {
        try {
            $plan_id = 1; // Trial Plan ID
            $start_date = date('Y-m-d');
            $expiry_date = date('Y-m-d', strtotime('+3 days'));

            $stmt = $this->conn->prepare("
                INSERT INTO business_subscriptions 
                (business_id, plan_id, start_date, expiry_date, status) 
                VALUES (?, ?, ?, ?, 'active')
            ");

            $stmt->bind_param("iiss", $business_id, $plan_id, $start_date, $expiry_date);
            
            if ($stmt->execute()) {
                // Log subscription history
                $this->logSubscriptionHistory(
                    $business_id, 
                    null, 
                    $plan_id, 
                    'Created', 
                    'auto',
                    'Auto trial created on registration'
                );
                return ['success' => true, 'message' => 'Trial subscription created'];
            }
            return ['success' => false, 'message' => 'Failed to create trial'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Upgrade Subscription Plan
     */
    public function upgradeSubscription($business_id, $plan_id, $duration_type = 'monthly') {
        try {
            // Get plan pricing
            $pricing = $this->getPlanPricing($plan_id, $duration_type);
            if (!$pricing) {
                return ['success' => false, 'message' => 'Invalid plan or duration'];
            }

            // Get plan details
            $plan = $this->getPlanDetails($plan_id);
            
            // Calculate dates
            $days = $this->getDurationDays($duration_type);
            $start_date = date('Y-m-d');
            $expiry_date = date('Y-m-d', strtotime("+$days days"));

            // Insert new subscription (initially PENDING until payment)
            $stmt = $this->conn->prepare("
                INSERT INTO business_subscriptions 
                (business_id, plan_id, start_date, expiry_date, status) 
                VALUES (?, ?, ?, ?, 'pending')
            ");

            $stmt->bind_param("iiss", $business_id, $plan_id, $start_date, $expiry_date);
            
            if ($stmt->execute()) {
                $subscription_id = $this->conn->insert_id;
                return [
                    'success' => true, 
                    'subscription_id' => $subscription_id,
                    'amount' => $pricing['price'],
                    'message' => 'Subscription created. Awaiting payment.'
                ];
            }
            return ['success' => false, 'message' => 'Failed to create subscription'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Process Payment and Activate Subscription
     * Called when payment is received
     */
    public function processPayment($business_subscription_id, $payment_number, $transaction_id, $amount, $payment_method = 'online') {
        try {
            // Verify subscription exists
            $subscription = $this->getSubscription($business_subscription_id);
            if (!$subscription) {
                return ['success' => false, 'message' => 'Subscription not found'];
            }

            // Create payment record
            $stmt = $this->conn->prepare("
                INSERT INTO subscription_payments 
                (business_subscription_id, business_id, payment_number, transaction_id, amount, payment_method, payment_status) 
                VALUES (?, ?, ?, ?, ?, ?, 'pending')
            ");

            $stmt->bind_param(
                "iissd s",
                $business_subscription_id,
                $subscription['business_id'],
                $payment_number,
                $transaction_id,
                $amount,
                $payment_method
            );

            if ($stmt->execute()) {
                $payment_id = $this->conn->insert_id;
                return [
                    'success' => true,
                    'payment_id' => $payment_id,
                    'status' => 'pending',
                    'message' => 'Payment recorded. Pending admin review.'
                ];
            }
            return ['success' => false, 'message' => 'Failed to process payment'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Auto-Activate Subscription After Payment Approval
     * Admin manually approves payment, then this activates the plan
     */
    public function approvePaymentAndActivate($payment_id, $reviewed_by = null) {
        try {
            // Get payment details
            $payment = $this->getPaymentDetails($payment_id);
            if (!$payment) {
                return ['success' => false, 'message' => 'Payment not found'];
            }

            // Approve payment
            $stmt = $this->conn->prepare("
                UPDATE subscription_payments 
                SET payment_status = 'completed', reviewed_by = ?, approved_at = NOW() 
                WHERE id = ?
            ");

            $stmt->bind_param("ii", $reviewed_by, $payment_id);
            $stmt->execute();

            // Activate subscription
            $sub_stmt = $this->conn->prepare("
                UPDATE business_subscriptions 
                SET status = 'active' 
                WHERE id = ?
            ");

            $sub_stmt->bind_param("i", $payment['business_subscription_id']);
            
            if ($sub_stmt->execute()) {
                // Log history
                $subscription = $this->getSubscription($payment['business_subscription_id']);
                $this->logSubscriptionHistory(
                    $subscription['business_id'],
                    null,
                    $subscription['plan_id'],
                    'Activated',
                    'payment_approved',
                    'Payment approved and subscription activated'
                );
                
                return [
                    'success' => true,
                    'message' => 'Payment approved and subscription activated successfully'
                ];
            }
            return ['success' => false, 'message' => 'Failed to activate subscription'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Reject Payment
     */
    public function rejectPayment($payment_id, $rejection_reason, $reviewed_by = null) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE subscription_payments 
                SET payment_status = 'failed', rejection_reason = ?, reviewed_by = ?, approved_at = NOW()
                WHERE id = ?
            ");

            $stmt->bind_param("sii", $rejection_reason, $reviewed_by, $payment_id);
            
            if ($stmt->execute()) {
                // Cancel subscription
                $payment = $this->getPaymentDetails($payment_id);
                $cancel_stmt = $this->conn->prepare("
                    UPDATE business_subscriptions 
                    SET status = 'cancelled' 
                    WHERE id = ?
                ");
                $cancel_stmt->bind_param("i", $payment['business_subscription_id']);
                $cancel_stmt->execute();

                return ['success' => true, 'message' => 'Payment rejected and subscription cancelled'];
            }
            return ['success' => false, 'message' => 'Failed to reject payment'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get Active Subscription for Business
     */
    public function getActiveSubscription($business_id) {
        $stmt = $this->conn->prepare("
            SELECT bs.*, sp.plan_type, sp.name as plan_name, sp.duration_days
            FROM business_subscriptions bs
            JOIN subscription_plans sp ON bs.plan_id = sp.id
            WHERE bs.business_id = ? AND bs.status = 'active'
            ORDER BY bs.id DESC
            LIMIT 1
        ");

        $stmt->bind_param("i", $business_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }

    /**
     * Get Subscription Details
     */
    public function getSubscription($subscription_id) {
        $stmt = $this->conn->prepare("
            SELECT bs.*, sp.plan_type, sp.name as plan_name
            FROM business_subscriptions bs
            JOIN subscription_plans sp ON bs.plan_id = sp.id
            WHERE bs.id = ?
        ");

        $stmt->bind_param("i", $subscription_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }

    /**
     * Check if Subscription is Expired
     */
    public function isSubscriptionExpired($business_id) {
        $today = date('Y-m-d');
        
        $stmt = $this->conn->prepare("
            SELECT id 
            FROM business_subscriptions
            WHERE business_id = ? AND expiry_date < ? AND status = 'active'
            LIMIT 1
        ");

        $stmt->bind_param("is", $business_id, $today);
        $stmt->execute();
        
        return $stmt->get_result()->num_rows > 0;
    }

    /**
     * Get Days Remaining in Subscription
     */
    public function getDaysRemaining($business_id) {
        $subscription = $this->getActiveSubscription($business_id);
        
        if (!$subscription) {
            return 0;
        }

        $today = new DateTime();
        $expiry = new DateTime($subscription['expiry_date']);
        $interval = $today->diff($expiry);
        
        return max(0, $interval->days);
    }

    /**
     * Get Plan Pricing
     */
    public function getPlanPricing($plan_id, $duration_type) {
        $stmt = $this->conn->prepare("
            SELECT * FROM plan_pricing 
            WHERE plan_id = ? AND duration_type = ?
        ");

        $stmt->bind_param("is", $plan_id, $duration_type);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }

    /**
     * Get Plan Details
     */
    public function getPlanDetails($plan_id) {
        $stmt = $this->conn->prepare("
            SELECT * FROM subscription_plans WHERE id = ?
        ");

        $stmt->bind_param("i", $plan_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }

    /**
     * Get Payment Details
     */
    public function getPaymentDetails($payment_id) {
        $stmt = $this->conn->prepare("
            SELECT * FROM subscription_payments WHERE id = ?
        ");

        $stmt->bind_param("i", $payment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }

    /**
     * Get Duration Days from Duration Type
     */
    private function getDurationDays($duration_type) {
        $days = [
            'monthly' => 30,
            'six_months' => 180,
            'yearly' => 365
        ];
        
        return $days[$duration_type] ?? 30;
    }

    /**
     * Get All Plans with Pricing
     */
    public function getAllPlans() {
        $stmt = $this->conn->prepare("
            SELECT sp.*, 
                   GROUP_CONCAT(CONCAT(pp.duration_type, ':', pp.price) SEPARATOR '|') as pricing
            FROM subscription_plans sp
            LEFT JOIN plan_pricing pp ON sp.id = pp.plan_id
            WHERE sp.is_active = TRUE
            GROUP BY sp.id
            ORDER BY sp.id
        ");

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get Pending Payments (for admin review)
     */
    public function getPendingPayments($limit = 50) {
        $stmt = $this->conn->prepare("
            SELECT sp.*, bs.business_id, sp_plan.name as plan_name, sp_plan.plan_type
            FROM subscription_payments sp
            JOIN business_subscriptions bs ON sp.business_subscription_id = bs.id
            JOIN subscription_plans sp_plan ON bs.plan_id = sp_plan.id
            WHERE sp.payment_status = 'pending'
            ORDER BY sp.created_at DESC
            LIMIT ?
        ");

        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Auto-expire Subscriptions (Run as cron job)
     */
    public function autoExpireSubscriptions() {
        $today = date('Y-m-d');
        
        $stmt = $this->conn->prepare("
            UPDATE business_subscriptions 
            SET status = 'expired' 
            WHERE expiry_date <= ? AND status = 'active'
        ");

        $stmt->bind_param("s", $today);
        return $stmt->execute();
    }

    /**
     * Log Subscription History
     */
    private function logSubscriptionHistory($business_id, $old_plan_id, $new_plan_id, $action, $reason, $description = '') {
        $stmt = $this->conn->prepare("
            INSERT INTO subscription_history 
            (business_id, old_plan_id, new_plan_id, action, reason)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->bind_param("iisss", $business_id, $old_plan_id, $new_plan_id, $action, $reason);
        $stmt->execute();
    }
}

?>
