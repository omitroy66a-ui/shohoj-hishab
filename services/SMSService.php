<?php

/**
 * ============================================================
 * BULK SMS SERVICE
 * ============================================================
 * Supports: Twilio, Nexmo, Local SMS Gateway
 */

class SMSService {
    private $conn;
    private $sms_provider;
    private $api_key;
    private $api_secret;
    private $sender_id;
    
    public function __construct($connection, $provider = 'local') {
        $this->conn = $connection;
        $this->sms_provider = $provider;
        $this->loadConfig();
    }

    /**
     * Load SMS Provider Configuration
     */
    private function loadConfig() {
        $stmt = $this->conn->prepare("
            SELECT * FROM sms_config WHERE provider = ? AND is_active = TRUE
        ");

        $stmt->bind_param("s", $this->sms_provider);
        $stmt->execute();
        $config = $stmt->get_result()->fetch_assoc();

        if ($config) {
            $this->api_key = $config['api_key'];
            $this->api_secret = $config['api_secret'];
            $this->sender_id = $config['sender_id'];
        }
    }

    /**
     * Send Single SMS
     */
    public function sendSMS($phone, $message, $type = 'general') {
        try {
            // Validate phone number
            $phone = $this->formatPhoneNumber($phone);
            if (!$phone) {
                return ['success' => false, 'message' => 'Invalid phone number'];
            }

            // Check SMS limit
            if (!$this->checkDailyLimit($phone)) {
                return ['success' => false, 'message' => 'SMS limit exceeded for this number'];
            }

            // Send based on provider
            $result = match($this->sms_provider) {
                'twilio' => $this->sendViaTwilio($phone, $message),
                'nexmo' => $this->sendViaNexmo($phone, $message),
                'local' => $this->sendViaLocal($phone, $message),
                default => ['success' => false, 'message' => 'Unknown SMS provider']
            };

            if ($result['success']) {
                // Log SMS
                $this->logSMS($phone, $message, $type, $result['message_id'] ?? '');
                return ['success' => true, 'message' => 'SMS sent successfully'];
            }

            return $result;
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send Bulk SMS
     */
    public function sendBulkSMS($phone_numbers, $message, $type = 'bulk') {
        try {
            $results = [
                'success' => [],
                'failed' => [],
                'total' => count($phone_numbers),
                'sent' => 0,
                'failed_count' => 0
            ];

            foreach ($phone_numbers as $phone) {
                $result = $this->sendSMS($phone, $message, $type);

                if ($result['success']) {
                    $results['success'][] = $phone;
                    $results['sent']++;
                } else {
                    $results['failed'][] = ['phone' => $phone, 'error' => $result['message']];
                    $results['failed_count']++;
                }

                // Rate limiting - wait 100ms between SMS
                usleep(100000);
            }

            // Log bulk SMS campaign
            $this->logBulkSMSCampaign($results, $message);

            return $results;
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send via Twilio
     */
    private function sendViaTwilio($phone, $message) {
        try {
            require_once 'vendor/autoload.php';
            $client = new \Twilio\Rest\Client($this->api_key, $this->api_secret);

            $response = $client->messages->create($phone, [
                'from' => $this->sender_id,
                'body' => $message
            ]);

            return [
                'success' => true,
                'message_id' => $response->sid,
                'status' => 'sent'
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send via Nexmo (Vonage)
     */
    private function sendViaNexmo($phone, $message) {
        try {
            $url = 'https://rest.nexmo.com/sms/json';

            $data = [
                'api_key' => $this->api_key,
                'api_secret' => $this->api_secret,
                'to' => $phone,
                'from' => $this->sender_id,
                'text' => $message
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($response, true);

            if ($result['messages'][0]['status'] == 0) {
                return [
                    'success' => true,
                    'message_id' => $result['messages'][0]['message-id'],
                    'status' => 'sent'
                ];
            }

            return ['success' => false, 'message' => 'Nexmo error'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send via Local Gateway
     */
    private function sendViaLocal($phone, $message) {
        try {
            // Local gateway configuration
            $gateway_url = 'http://localhost:9000/api/sms/send'; // Local SMS gateway

            $data = [
                'phone' => $phone,
                'message' => $message,
                'sender_id' => $this->sender_id
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $gateway_url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($http_code === 200) {
                $result = json_decode($response, true);
                return [
                    'success' => true,
                    'message_id' => $result['message_id'] ?? uniqid('sms_'),
                    'status' => 'sent'
                ];
            }

            return ['success' => false, 'message' => 'Local gateway error'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Format Phone Number (ensure it starts with +880 or 880)
     */
    private function formatPhoneNumber($phone) {
        // Remove spaces and special characters
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Handle different formats
        if (substr($phone, 0, 1) === '0') {
            // 01700000000 → +8801700000000
            $phone = '+88' . $phone;
        } elseif (substr($phone, 0, 3) === '880') {
            // 8801700000000 → +8801700000000
            $phone = '+' . $phone;
        } elseif (substr($phone, 0, 4) !== '+880') {
            // Invalid format
            return null;
        }

        return $phone;
    }

    /**
     * Check Daily SMS Limit per Number
     */
    private function checkDailyLimit($phone, $limit = 5) {
        $today = date('Y-m-d');

        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as count 
            FROM sms_logs 
            WHERE phone = ? AND DATE(created_at) = ? AND status = 'sent'
        ");

        $stmt->bind_param("ss", $phone, $today);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        return $result['count'] < $limit;
    }

    /**
     * Log SMS
     */
    private function logSMS($phone, $message, $type, $message_id) {
        $stmt = $this->conn->prepare("
            INSERT INTO sms_logs (phone, message, type, message_id, status, provider, created_at) 
            VALUES (?, ?, ?, ?, 'sent', ?, NOW())
        ");

        $status = 'sent';
        $stmt->bind_param("sssss", $phone, $message, $type, $message_id, $this->sms_provider);
        $stmt->execute();
    }

    /**
     * Log Bulk SMS Campaign
     */
    private function logBulkSMSCampaign($results, $message) {
        $campaign_data = json_encode([
            'total' => $results['total'],
            'sent' => $results['sent'],
            'failed' => $results['failed_count'],
            'failed_phones' => $results['failed']
        ]);

        $stmt = $this->conn->prepare("
            INSERT INTO sms_campaigns (message, total_count, sent_count, failed_count, campaign_data, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");

        $stmt->bind_param("siis", $message, $results['total'], $results['sent'], $campaign_data);
        $stmt->execute();
    }

    /**
     * Get SMS Statistics
     */
    public function getStatistics() {
        $stmt = $this->conn->prepare("
            SELECT 
                COUNT(*) as total_sms,
                SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent_count,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_count,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_count
            FROM sms_logs
        ");

        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Template: Payment Confirmation SMS
     */
    public static function getPaymentConfirmationSMS($payment_number, $amount, $plan) {
        return "Sohoj Hishab: Payment ৳{$amount} received for {$plan} plan (Ref: {$payment_number}). Your subscription will be activated soon. Thank you!";
    }

    /**
     * Template: Subscription Activated SMS
     */
    public static function getSubscriptionActivatedSMS($plan, $expiry_date) {
        return "Sohoj Hishab: Your {$plan} subscription is now active! Valid until {$expiry_date}. Start using all features now. Thank you!";
    }

    /**
     * Template: Trial Expiring SMS
     */
    public static function getTrialExpiringSMS($days_left) {
        return "Sohoj Hishab: Your trial expires in {$days_left} days. Upgrade now to continue enjoying all features!";
    }

    /**
     * Template: Payment Approval SMS
     */
    public static function getPaymentApprovedSMS($plan) {
        return "Sohoj Hishab: Your payment has been approved! Your {$plan} subscription is now active. Enjoy!";
    }
}

?>
