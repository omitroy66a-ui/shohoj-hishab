<?php
header('Content-Type: application/json');

require_once "../../config/database.php";
require_once "../../modules/business/middleware.php";
require_once "../../modules/accounting/profit.php";

$business_id = businessId();

$pnl = calculateProfit($conn, $business_id);

echo json_encode([
    'success' => true,
    'data' => $pnl
]);
