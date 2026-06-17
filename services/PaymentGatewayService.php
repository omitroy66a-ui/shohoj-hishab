<?php

/**
 * ============================================================
 * PAYMENT GATEWAY SERVICE
 * ============================================================
 * Handles multiple payment gateway integration
 * Currently: Nagad, bKash, Rocket
 */

class PaymentGatewayService {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }

    /**
     * Get Gateway Info (Nagad, bKash, etc)
     */
    public function getGatewayInfo($gateway_name) {
        $stmt = $this->conn->prepare("
            SELECT * FROM payment_gateways 
            WHERE gateway_name = ? AND is_active = TRUE
        ");

        $stmt->bind_param("s", $gateway_name);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }

    /**
     * Get All Active Gateways
     */
    public function getAllGateways() {
        $stmt = $this->conn->prepare("
            SELECT id, gateway_name, phone_number, account_name 
            FROM payment_gateways 
            WHERE is_active = TRUE
            ORDER BY gateway_name
        ");

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get Nagad Details Specifically
     */
    public function getNagadDetails() {
        return $this->getGatewayInfo('Nagad');
    }

    /**
     * Get Gateway Phone Number
     */
    public function getGatewayPhone($gateway_name) {
        $gateway = $this->getGatewayInfo($gateway_name);
        return $gateway ? $gateway['phone_number'] : null;
    }

    /**
     * Verify Payment Gateway Configuration
     */
    public function verifyGatewayConfig($gateway_name) {
        $gateway = $this->getGatewayInfo($gateway_name);
        
        if (!$gateway) {
            return ['valid' => false, 'message' => 'Gateway not found or inactive'];
        }

        if (empty($gateway['phone_number'])) {
            return ['valid' => false, 'message' => 'Gateway phone number not configured'];
        }

        return ['valid' => true, 'gateway' => $gateway];
    }

    /**
     * Generate Payment Instruction Message
     */
    public function generatePaymentInstruction($amount, $gateway_name = 'Nagad', $payment_reference = '') {
        $gateway = $this->getGatewayInfo($gateway_name);
        
        if (!$gateway) {
            return null;
        }

        $message = "Send ৳{$amount} to {$gateway['account_name']}\n";
        $message .= "📱 {$gateway['gateway_name']}: {$gateway['phone_number']}\n";
        
        if (!empty($payment_reference)) {
            $message .= "📝 Reference: {$payment_reference}\n";
        }

        return $message;
    }

    /**
     * Log Payment Transaction
     */
    public function logPaymentTransaction($business_subscription_id, $business_id, $amount, $gateway_name, $payment_number, $transaction_id) {
        $payment_gateway = $gateway_name;
        $gateway_reference = $payment_number;

        $stmt = $this->conn->prepare("
            INSERT INTO subscription_payments 
            (business_subscription_id, business_id, payment_number, transaction_id, amount, payment_method, payment_gateway, gateway_reference, payment_status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");

        $stmt->bind_param(
            "iissdss",
            $business_subscription_id,
            $business_id,
            $payment_number,
            $transaction_id,
            $amount,
            $gateway_name,
            $payment_gateway,
            $gateway_reference
        );

        return $stmt->execute();
    }

    /**
     * Create Payment QR Code (for Nagad/bKash)
     * Note: Actual QR generation would use qr-code library
     */
    public function generatePaymentQR($gateway_name, $amount, $reference) {
        $gateway = $this->getGatewayInfo($gateway_name);
        
        if (!$gateway) {
            return null;
        }

        // For Nagad: nagad://send?phone=01763206165&amount=100
        $qr_data = match($gateway_name) {
            'Nagad' => "nagad://send?phone={$gateway['phone_number']}&amount={$amount}&reference={$reference}",
            'bKash' => "bkash://send?phone={$gateway['phone_number']}&amount={$amount}",
            'Rocket' => "rocket://send?phone={$gateway['phone_number']}&amount={$amount}",
            default => null
        };

        return $qr_data;
    }
}

?>
