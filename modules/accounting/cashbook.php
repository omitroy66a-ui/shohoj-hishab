<?php
/**
 * Cashbook System
 * Tracks all cash income and expenses
 */

function addCashEntry($conn, $business_id, $type, $amount, $note, $ref_type, $ref_id) {
    if($amount <= 0) return false;
    
    $business_id = (int)$business_id;
    $type = $conn->real_escape_string($type);
    $amount = (float)$amount;
    $note = $conn->real_escape_string($note);
    $ref_type = $conn->real_escape_string($ref_type);
    $ref_id = (int)$ref_id;
    
    $result = $conn->query("
        INSERT INTO cashbook(type, amount, note, ref_type, ref_id, business_id)
        VALUES('$type', '$amount', '$note', '$ref_type', '$ref_id', '$business_id')
    ");
    
    return $result ? $conn->insert_id : false;
}

function getCashBalance($conn, $business_id) {
    $business_id = (int)$business_id;
    
    $result = $conn->query("
        SELECT
            SUM(CASE WHEN type='income' THEN amount ELSE 0 END) -
            SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) as balance
        FROM cashbook
        WHERE business_id='$business_id'
    ")->fetch_assoc();
    
    return (float)($result['balance'] ?? 0);
}

function getCashbookEntries($conn, $business_id, $limit = 100, $type = null) {
    $business_id = (int)$business_id;
    $limit = (int)$limit;
    
    $query = "SELECT * FROM cashbook WHERE business_id='$business_id'";
    
    if($type) {
        $type = $conn->real_escape_string($type);
        $query .= " AND type='$type'";
    }
    
    $query .= " ORDER BY created_at DESC LIMIT $limit";
    
    return $conn->query($query);
}

function getCashSummary($conn, $business_id) {
    $business_id = (int)$business_id;
    
    $income = $conn->query("SELECT SUM(amount) as total FROM cashbook WHERE type='income' AND business_id='$business_id'")->fetch_assoc()['total'] ?? 0;
    $expense = $conn->query("SELECT SUM(amount) as total FROM cashbook WHERE type='expense' AND business_id='$business_id'")->fetch_assoc()['total'] ?? 0;
    $balance = $income - $expense;
    
    return [
        'income' => (float)$income,
        'expense' => (float)$expense,
        'balance' => (float)$balance
    ];
}
