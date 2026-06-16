<?php
header('Content-Type: application/json');

require_once "../../config/database.php";
require_once "../../modules/business/middleware.php";
require_once "../../modules/accounting/cashbook.php";

$business_id = businessId();
$method = $_SERVER['REQUEST_METHOD'];

if($method === 'GET') {
    // Get cashbook entries
    $limit = (int)($_GET['limit'] ?? 100);
    $type = $_GET['type'] ?? null;
    
    $res = getCashbookEntries($conn, $business_id, $limit, $type);
    $entries = [];
    
    while($row = $res->fetch_assoc()) {
        $entries[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $entries,
        'summary' => getCashSummary($conn, $business_id)
    ]);
    
} elseif($method === 'POST') {
    // Add new entry
    $type = $_POST['type'] ?? '';
    $amount = (float)($_POST['amount'] ?? 0);
    $note = $_POST['note'] ?? '';
    
    if($amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid amount']);
        exit;
    }
    
    $id = addCashEntry($conn, $business_id, $type, $amount, $note, 'api', 0);
    
    echo json_encode([
        'success' => $id ? true : false,
        'entry_id' => $id,
        'summary' => getCashSummary($conn, $business_id)
    ]);
}
