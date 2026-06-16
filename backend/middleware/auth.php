<?php

require_once __DIR__ . '/../../config/database.php';

/**
 * Authentication Middleware
 * Checks if user is logged in with session
 */

function requireAuth() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(["success" => false, "error" => "Authentication required"]);
        exit;
    }
    
    return $_SESSION;
}

/**
 * Get current user
 */
function getCurrentUser() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'email' => $_SESSION['user_email'],
        'name' => $_SESSION['user_name'],
        'role' => $_SESSION['user_role']
    ];
}

/**
 * Check if user has specific role
 */
function hasRole($role) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['user_role'])) {
        return false;
    }
    
    if (is_array($role)) {
        return in_array($_SESSION['user_role'], $role);
    }
    
    return $_SESSION['user_role'] === $role;
}

/**
 * Require specific role
 */
function requireRole($roles) {
    if (!hasRole($roles)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(["success" => false, "error" => "Insufficient permissions"]);
        exit;
    }
}

/**
 * Check CSRF token
 */
function validateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        
        if (!$token || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(["success" => false, "error" => "CSRF token invalid"]);
            exit;
        }
    }
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Setup secure session
 */
function setupSecureSession() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        session_start();
    }
}

/**
 * JWT Token verification (for API)
 */
function verifyJWTToken($token) {
    require_once __DIR__ . '/../auth/JWT.php';
    
    // Remove "Bearer " prefix if present
    $token = str_replace('Bearer ', '', $token);
    
    $decoded = JWTHandler::verify($token);
    
    if (!$decoded) {
        http_response_code(401);
        echo json_encode(["success" => false, "error" => "Invalid token"]);
        exit;
    }
    
    return $decoded;
}

/**
 * CORS Middleware
 */
function corsMiddleware() {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

