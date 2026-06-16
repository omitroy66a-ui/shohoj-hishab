<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/backend', '', $path);

// Route handler
if ($path === '/api/health') {
    http_response_code(200);
    echo json_encode(["status" => "API Running", "version" => "1.0.0"]);
}
else if (strpos($path, '/api/auth') === 0) {
    require_once __DIR__ . '/routes/auth.php';
}
else if (strpos($path, '/api/dashboard') === 0) {
    require_once __DIR__ . '/routes/dashboard.php';
}
else if (strpos($path, '/api/expenses') === 0) {
    require_once __DIR__ . '/routes/expenses.php';
}
else if (strpos($path, '/api/customers') === 0) {
    require_once __DIR__ . '/routes/customers.php';
}
else if (strpos($path, '/api/employees') === 0) {
    require_once __DIR__ . '/routes/employees.php';
}
else {
    http_response_code(404);
    echo json_encode(["success" => false, "error" => "Route not found"]);
}
