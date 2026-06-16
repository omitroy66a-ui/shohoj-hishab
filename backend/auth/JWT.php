<?php

class JWTHandler {
    private $secret = 'sohoj_hishab_secret_key_2024';
    
    public static function generate($data) {
        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payload = base64_encode(json_encode(array_merge($data, ['iat' => time()])));
        $signature = hash_hmac('sha256', "$header.$payload", 'sohoj_hishab_secret_key_2024');
        
        return "$header.$payload.$signature";
    }
    
    public static function verify($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return false;
        
        list($header, $payload, $signature) = $parts;
        $newSignature = hash_hmac('sha256', "$header.$payload", 'sohoj_hishab_secret_key_2024');
        
        if (!hash_equals($signature, $newSignature)) return false;
        
        $decoded = json_decode(base64_decode($payload), true);
        if ($decoded['iat'] < (time() - 86400)) return false; // 24 hour expiry
        
        return $decoded;
    }
}
