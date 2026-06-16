<?php

/**
 * Security Helper Functions
 * - Password hashing
 * - Verification code generation
 * - Email/SMS sending
 */

class AuthSecurity {
    
    /**
     * Hash password with BCRYPT
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Generate verification code (6 digits)
     */
    public static function generateVerificationCode() {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Generate secure random token
     */
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Send verification code via email
     */
    public static function sendEmailVerification($email, $code, $type = 'register') {
        $subject = $type === 'register' ? 'Verify Your Email' : 'Reset Your Password';
        
        $message = "
        <html>
        <head>
            <title>$subject</title>
            <style>
                body { font-family: Arial, sans-serif; background: #f4f4f4; }
                .container { max-width: 500px; margin: 20px auto; background: #fff; padding: 30px; border-radius: 8px; }
                .header { background: #007bff; color: #fff; padding: 15px; border-radius: 4px; text-align: center; }
                .code { font-size: 32px; font-weight: bold; text-align: center; letter-spacing: 5px; margin: 30px 0; color: #007bff; }
                .footer { text-align: center; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Sohoj Hishab</h1>
                </div>
                <p>Hi there,</p>
                <p>Your verification code is:</p>
                <div class='code'>$code</div>
                <p>This code will expire in 15 minutes.</p>
                <p>If you didn't request this, please ignore this email.</p>
                <div class='footer'>
                    <p>&copy; 2024 Sohoj Hishab. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        
        return mail($email, $subject, $message, $headers);
    }
    
    /**
     * Send verification code via SMS
     */
    public static function sendSMSVerification($phone, $code) {
        // Using a placeholder for SMS API integration
        // Replace with your SMS provider (Twilio, Nexmo, etc.)
        
        // Example: Twilio, Nexmo, or local SMS gateway
        $message = "Your Sohoj Hishab verification code is: $code (Valid for 15 minutes)";
        
        // Log for development
        error_log("SMS to $phone: $message");
        
        // TODO: Integrate with actual SMS provider
        // return sendSMSViaProvider($phone, $message);
        
        return true; // Placeholder return
    }
    
    /**
     * Sanitize input
     */
    public static function sanitize($input) {
        return htmlspecialchars(stripslashes(trim($input)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate phone (11 digits for Bangladesh)
     */
    public static function validatePhone($phone) {
        return preg_match('/^(88)?0?1[3-9]\d{8}$/', str_replace([' ', '-', '+'], '', $phone));
    }
    
    /**
     * Validate password strength
     */
    public static function validatePasswordStrength($password) {
        $strength = [
            'length' => strlen($password) >= 8,
            'uppercase' => preg_match('/[A-Z]/', $password),
            'lowercase' => preg_match('/[a-z]/', $password),
            'numbers' => preg_match('/[0-9]/', $password),
            'special' => preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)
        ];
        
        return [
            'valid' => array_sum($strength) >= 3, // At least 3 criteria
            'details' => $strength
        ];
    }
}
