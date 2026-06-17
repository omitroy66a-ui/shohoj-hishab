<?php

/**
 * ============================================================
 * SUBSCRIPTION DISCOUNT SERVICE
 * ============================================================
 * Admin can apply discounts to active subscriptions
 */

class SubscriptionDiscountService {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }

    /**
     * Apply Discount to Subscription
     */
    public function applyDiscount($business_subscription_id, $discount_amount, $discount_reason, $admin_id) {
        try {
            // Get subscription details
            $stmt = $this->conn->prepare("
                SELECT bs.*, sp.price as original_price
                FROM business_subscriptions bs
                JOIN subscription_plans sp ON bs.plan_id = sp.id
                WHERE bs.id = ?
            ");

            $stmt->bind_param("i", $business_subscription_id);
            $stmt->execute();
            $subscription = $stmt->get_result()->fetch_assoc();

            if (!$subscription) {
                return ['success' => false, 'message' => 'Subscription not found'];
            }

            // Calculate discount percentage
            $original_price = $subscription['original_price'] ?? $subscription['original_price'];
            $discount_percentage = ($discount_amount / $original_price) * 100;
            $final_price = $original_price - $discount_amount;

            // Update subscription with discount
            $update_stmt = $this->conn->prepare("
                UPDATE business_subscriptions 
                SET 
                    original_price = ?,
                    discounted_price = ?,
                    discount_reason = ?,
                    discount_applied_by = ?,
                    discount_applied_at = NOW()
                WHERE id = ?
            ");

            $update_stmt->bind_param(
                "ddssi",
                $original_price,
                $final_price,
                $discount_reason,
                $admin_id,
                $business_subscription_id
            );

            if ($update_stmt->execute()) {
                // Log discount in discount history
                $log_stmt = $this->conn->prepare("
                    INSERT INTO subscription_discounts 
                    (business_subscription_id, business_id, original_amount, discount_amount, discount_percentage, discount_reason, applied_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");

                $log_stmt->bind_param(
                    "iidddssi",
                    $business_subscription_id,
                    $subscription['business_id'],
                    $original_price,
                    $discount_amount,
                    $discount_percentage,
                    $discount_reason,
                    $admin_id
                );

                $log_stmt->execute();

                return [
                    'success' => true,
                    'message' => 'Discount applied successfully',
                    'original_price' => $original_price,
                    'discount_amount' => $discount_amount,
                    'final_price' => $final_price,
                    'discount_percentage' => round($discount_percentage, 2)
                ];
            }

            return ['success' => false, 'message' => 'Failed to apply discount'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Apply Percentage Discount
     */
    public function applyPercentageDiscount($business_subscription_id, $discount_percentage, $discount_reason, $admin_id) {
        try {
            // Get subscription details
            $stmt = $this->conn->prepare("
                SELECT bs.*, sp.price as original_price
                FROM business_subscriptions bs
                JOIN subscription_plans sp ON bs.plan_id = sp.id
                WHERE bs.id = ?
            ");

            $stmt->bind_param("i", $business_subscription_id);
            $stmt->execute();
            $subscription = $stmt->get_result()->fetch_assoc();

            if (!$subscription) {
                return ['success' => false, 'message' => 'Subscription not found'];
            }

            $original_price = $subscription['original_price'] ?? $subscription['original_price'];
            $discount_amount = ($original_price * $discount_percentage) / 100;
            $final_price = $original_price - $discount_amount;

            return $this->applyDiscount(
                $business_subscription_id, 
                $discount_amount, 
                $discount_reason, 
                $admin_id
            );
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Remove/Revert Discount
     */
    public function removeDiscount($business_subscription_id) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE business_subscriptions 
                SET 
                    original_price = 0,
                    discounted_price = 0,
                    discount_reason = NULL,
                    discount_applied_by = NULL,
                    discount_applied_at = NULL
                WHERE id = ?
            ");

            $stmt->bind_param("i", $business_subscription_id);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Discount removed successfully'];
            }

            return ['success' => false, 'message' => 'Failed to remove discount'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get Discount History for Subscription
     */
    public function getDiscountHistory($business_subscription_id) {
        $stmt = $this->conn->prepare("
            SELECT sd.*, CONCAT(u.name) as applied_by_name
            FROM subscription_discounts sd
            LEFT JOIN users u ON sd.applied_by = u.id
            WHERE sd.business_subscription_id = ?
            ORDER BY sd.applied_at DESC
        ");

        $stmt->bind_param("i", $business_subscription_id);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get Current Discount for Subscription
     */
    public function getCurrentDiscount($business_subscription_id) {
        $stmt = $this->conn->prepare("
            SELECT 
                original_price,
                discounted_price,
                (original_price - discounted_price) as discount_amount,
                ((original_price - discounted_price) / original_price * 100) as discount_percentage,
                discount_reason,
                discount_applied_at
            FROM business_subscriptions
            WHERE id = ? AND discounted_price > 0
        ");

        $stmt->bind_param("i", $business_subscription_id);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Get All Subscriptions with Active Discounts
     */
    public function getSubscriptionsWithDiscounts($limit = 50) {
        $stmt = $this->conn->prepare("
            SELECT 
                bs.id,
                bs.business_id,
                sp.name as plan_name,
                bs.original_price,
                bs.discounted_price,
                (bs.original_price - bs.discounted_price) as discount_amount,
                bs.discount_reason,
                u.name as applied_by_name,
                bs.discount_applied_at
            FROM business_subscriptions bs
            JOIN subscription_plans sp ON bs.plan_id = sp.id
            LEFT JOIN users u ON bs.discount_applied_by = u.id
            WHERE bs.discounted_price > 0
            ORDER BY bs.discount_applied_at DESC
            LIMIT ?
        ");

        $stmt->bind_param("i", $limit);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Calculate Total Discounts Given
     */
    public function getTotalDiscountsGiven() {
        $stmt = $this->conn->prepare("
            SELECT 
                COUNT(*) as total_discounted_subscriptions,
                SUM(discount_amount) as total_discount_amount
            FROM subscription_discounts
        ");

        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}

?>
