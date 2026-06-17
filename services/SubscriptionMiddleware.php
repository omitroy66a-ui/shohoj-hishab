<?php

/**
 * ============================================================
 * SUBSCRIPTION MIDDLEWARE
 * ============================================================
 * Feature access control and subscription validation
 */

class SubscriptionMiddleware {
    private $conn;
    private $subscriptionService;
    
    public function __construct($connection) {
        $this->conn = $connection;
        require_once __DIR__ . '/../services/SubscriptionService.php';
        $this->subscriptionService = new SubscriptionService($connection);
    }

    /**
     * Check if Business has Active Subscription
     */
    public function checkSubscription($business_id) {
        $subscription = $this->subscriptionService->getActiveSubscription($business_id);
        
        if (!$subscription) {
            return [
                'has_subscription' => false,
                'message' => 'No active subscription found',
                'status_code' => 403
            ];
        }

        // Check if expired
        if ($this->subscriptionService->isSubscriptionExpired($business_id)) {
            return [
                'has_subscription' => false,
                'message' => 'Subscription has expired',
                'status_code' => 403
            ];
        }

        $days_remaining = $this->subscriptionService->getDaysRemaining($business_id);

        return [
            'has_subscription' => true,
            'subscription' => $subscription,
            'days_remaining' => $days_remaining,
            'status_code' => 200
        ];
    }

    /**
     * Check if User can Access Specific Feature
     */
    public function canAccessFeature($business_id, $feature_key) {
        // Get active subscription
        $subscription = $this->subscriptionService->getActiveSubscription($business_id);
        
        if (!$subscription) {
            return [
                'can_access' => false,
                'reason' => 'No active subscription'
            ];
        }

        // Check if expired
        if ($this->subscriptionService->isSubscriptionExpired($business_id)) {
            return [
                'can_access' => false,
                'reason' => 'Subscription expired'
            ];
        }

        $plan_type = $subscription['plan_type'];

        // Check feature permission
        $stmt = $this->conn->prepare("
            SELECT id 
            FROM feature_permissions
            WHERE plan_type = ? AND (feature_key = ? OR feature_key = 'all_features')
            LIMIT 1
        ");

        $stmt->bind_param("ss", $plan_type, $feature_key);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return [
                'can_access' => true,
                'plan' => $plan_type,
                'feature' => $feature_key
            ];
        }

        return [
            'can_access' => false,
            'reason' => 'Feature not available in current plan'
        ];
    }

    /**
     * Get All Features for Current Plan
     */
    public function getAvailableFeatures($business_id) {
        $subscription = $this->subscriptionService->getActiveSubscription($business_id);
        
        if (!$subscription) {
            return [];
        }

        $plan_type = $subscription['plan_type'];

        $stmt = $this->conn->prepare("
            SELECT feature_key, feature_name 
            FROM feature_permissions
            WHERE plan_type = ?
            ORDER BY feature_name
        ");

        $stmt->bind_param("s", $plan_type);
        $stmt->execute();
        
        $features = [];
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $features[] = $row;
        }

        return $features;
    }

    /**
     * Check Trial Expiry
     */
    public function checkTrialExpiry($business_id) {
        $subscription = $this->subscriptionService->getActiveSubscription($business_id);
        
        if (!$subscription || $subscription['plan_type'] !== 'trial') {
            return null;
        }

        $days_remaining = $this->subscriptionService->getDaysRemaining($business_id);

        if ($days_remaining <= 0) {
            // Auto-lock trial
            $this->lockTrialSubscription($business_id);
            
            return [
                'expired' => true,
                'message' => 'Trial period has ended. Please upgrade to continue.'
            ];
        }

        return [
            'expired' => false,
            'days_remaining' => $days_remaining,
            'expiry_date' => $subscription['expiry_date']
        ];
    }

    /**
     * Lock Trial Subscription (Auto-lock on Day 4)
     */
    private function lockTrialSubscription($business_id) {
        $stmt = $this->conn->prepare("
            UPDATE business_subscriptions 
            SET status = 'expired' 
            WHERE business_id = ? AND plan_type = 'trial'
        ");

        $stmt->bind_param("i", $business_id);
        $stmt->execute();
    }

    /**
     * Get Subscription Status UI Data
     */
    public function getSubscriptionStatus($business_id) {
        $subscription = $this->subscriptionService->getActiveSubscription($business_id);

        if (!$subscription) {
            return [
                'status' => 'no_subscription',
                'message' => 'No active subscription',
                'action' => 'upgrade'
            ];
        }

        // Check if expired
        if ($this->subscriptionService->isSubscriptionExpired($business_id)) {
            return [
                'status' => 'expired',
                'plan' => $subscription['plan_name'],
                'message' => 'Subscription Expired',
                'action' => 'renew',
                'expiry_date' => $subscription['expiry_date']
            ];
        }

        $days_remaining = $this->subscriptionService->getDaysRemaining($business_id);
        $plan_type = $subscription['plan_type'];

        if ($plan_type === 'trial') {
            return [
                'status' => 'active',
                'type' => 'trial',
                'plan' => $subscription['plan_name'],
                'days_remaining' => $days_remaining,
                'features' => 'All Features Enabled',
                'action' => 'upgrade',
                'message' => "Days Remaining: $days_remaining"
            ];
        }

        // Get pricing info
        $pricing = $this->getActiveSubscriptionPricing($business_id);

        return [
            'status' => 'active',
            'type' => $plan_type,
            'plan' => $subscription['plan_name'],
            'expiry_date' => $subscription['expiry_date'],
            'days_remaining' => $days_remaining,
            'price' => $pricing['price'] ?? 'N/A',
            'features' => $this->getAvailableFeatures($business_id),
            'action' => 'renew'
        ];
    }

    /**
     * Get Active Subscription Pricing (for display)
     */
    private function getActiveSubscriptionPricing($business_id) {
        // This is a simplified version - adjust based on your needs
        $subscription = $this->subscriptionService->getActiveSubscription($business_id);
        
        if (!$subscription) {
            return null;
        }

        // Get from plan_pricing table
        $stmt = $this->conn->prepare("
            SELECT price FROM plan_pricing 
            WHERE plan_id = ? 
            LIMIT 1
        ");

        $stmt->bind_param("i", $subscription['plan_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }

    /**
     * Get Restricted Features Message
     */
    public function getRestrictedFeatureMessage($business_id, $feature_key) {
        $subscription = $this->subscriptionService->getActiveSubscription($business_id);

        if (!$subscription) {
            return "Activate a plan to access this feature";
        }

        $feature_sql = "SELECT feature_name FROM feature_permissions WHERE feature_key = ? LIMIT 1";
        $stmt = $this->conn->prepare($feature_sql);
        $stmt->bind_param("s", $feature_key);
        $stmt->execute();
        $feature_result = $stmt->get_result();
        $feature = $feature_result->fetch_assoc();

        $feature_name = $feature ? $feature['feature_name'] : $feature_key;
        
        return "Feature '$feature_name' is not available in " . 
               $subscription['plan_name'] . " plan. Please upgrade to access this feature.";
    }
}

?>
