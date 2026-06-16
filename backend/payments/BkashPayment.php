<?php

/**
 * bKash Payment Gateway Integration (Live)
 * Documentation: https://developer.bkash.com/
 */

class BkashPayment {
    
    private $app_key;
    private $app_secret;
    private $username;
    private $password;
    private $base_url;
    
    public function __construct() {
        // Get credentials from environment or config
        $this->app_key = getenv('BKASH_APP_KEY') ?: 'YOUR_BKASH_APP_KEY';
        $this->app_secret = getenv('BKASH_APP_SECRET') ?: 'YOUR_BKASH_APP_SECRET';
        $this->username = getenv('BKASH_USERNAME') ?: 'YOUR_BKASH_USERNAME';
        $this->password = getenv('BKASH_PASSWORD') ?: 'YOUR_BKASH_PASSWORD';
        
        // Use production URL for live integration
        $this->base_url = 'https://api.bkash.com';
    }
    
    /**
     * Get bKash access token
     */
    private function getAccessToken() {
        $endpoint = $this->base_url . '/v1/tokenized/checkout/token/request';
        
        $data = json_encode([
            'app_key' => $this->app_key,
            'app_secret' => $this->app_secret
        ]);
        
        $response = $this->makeRequest('POST', $endpoint, $data);
        
        if (isset($response['statusCode']) && $response['statusCode'] === '0000') {
            return $response['id_token'];
        }
        
        throw new Exception('Failed to get bKash token: ' . json_encode($response));
    }
    
    /**
     * Create payment (bKash Tokenized Checkout)
     */
    public function createPayment($transaction_id, $amount, $customer_name, $customer_phone) {
        $token = $this->getAccessToken();
        $endpoint = $this->base_url . '/v1/tokenized/checkout/create';
        
        $data = json_encode([
            'mode' => '0011',  // Tokenized mode
            'paymentType' => 'DisbursementType',
            'trxID' => $transaction_id,
            'amount' => $amount,
            'currency' => 'BDT',
            'intent' => 'authorization',
            'merchantInvoiceNumber' => $transaction_id,
            'callbackURL' => getenv('APP_URL') . '/backend/payments/bkash_callback.php'
        ]);
        
        $headers = [
            'Authorization: Bearer ' . $token,
            'X-APP-Key: ' . $this->app_key,
            'Content-Type: application/json'
        ];
        
        $response = $this->makeRequest('POST', $endpoint, $data, $headers);
        
        if (isset($response['statusCode']) && $response['statusCode'] === '0000') {
            return [
                'success' => true,
                'payment_url' => $response['bkashURL'],
                'transaction_id' => $response['trxID'],
                'payment_request_id' => $response['paymentID']
            ];
        }
        
        return [
            'success' => false,
            'error' => $response['statusMessage'] ?? 'Payment creation failed'
        ];
    }
    
    /**
     * Execute payment (after user approval)
     */
    public function executePayment($payment_id, $token) {
        $endpoint = $this->base_url . '/v1/tokenized/checkout/execute';
        
        $data = json_encode([
            'paymentID' => $payment_id,
            'token' => $token
        ]);
        
        $headers = [
            'X-APP-Key: ' . $this->app_key,
            'Content-Type: application/json'
        ];
        
        $response = $this->makeRequest('POST', $endpoint, $data, $headers);
        
        if (isset($response['statusCode']) && $response['statusCode'] === '0000') {
            return [
                'success' => true,
                'transaction_id' => $response['trxID'],
                'payment_id' => $response['paymentID'],
                'amount' => $response['amount'],
                'status' => $response['transactionStatus']
            ];
        }
        
        return [
            'success' => false,
            'error' => $response['statusMessage'] ?? 'Payment execution failed'
        ];
    }
    
    /**
     * Query payment status
     */
    public function queryPayment($payment_id) {
        $token = $this->getAccessToken();
        $endpoint = $this->base_url . '/v1/tokenized/checkout/query';
        
        $data = json_encode([
            'paymentID' => $payment_id
        ]);
        
        $headers = [
            'Authorization: Bearer ' . $token,
            'X-APP-Key: ' . $this->app_key,
            'Content-Type: application/json'
        ];
        
        $response = $this->makeRequest('POST', $endpoint, $data, $headers);
        
        return $response;
    }
    
    /**
     * Make HTTP request
     */
    private function makeRequest($method, $url, $data = null, $headers = []) {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        
        curl_close($ch);
        
        if ($curl_error) {
            throw new Exception('cURL Error: ' . $curl_error);
        }
        
        return json_decode($response, true);
    }
}
