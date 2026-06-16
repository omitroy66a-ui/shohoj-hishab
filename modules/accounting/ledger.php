<?php
/**
 * Customer Ledger System
 * Handles all customer debit/credit entries
 */

function addLedgerEntry($conn, $business_id, $customer_id, $type, $amount, $note, $ref_type, $ref_id) {
    if($amount <= 0) return false;
    
    $customer_id = (int)$customer_id;
    $ref_id = (int)$ref_id;
    $amount = (float)$amount;
    $type = $conn->real_escape_string($type);
    $note = $conn->real_escape_string($note);
    $ref_type = $conn->real_escape_string($ref_type);
    $business_id = (int)$business_id;
    
    $result = $conn->query("
        INSERT INTO customer_ledger(customer_id, type, amount, note, ref_type, ref_id, business_id)
        VALUES('$customer_id', '$type', '$amount', '$note', '$ref_type', '$ref_id', '$business_id')
    ");
    
    return $result ? $conn->insert_id : false;
}

function getCustomerDue($conn, $business_id, $customer_id) {
    $business_id = (int)$business_id;
    $customer_id = (int)$customer_id;
    
    $result = $conn->query("
        SELECT
            SUM(CASE WHEN type='debit' THEN amount ELSE 0 END) -
            SUM(CASE WHEN type='credit' THEN amount ELSE 0 END) as due
        FROM customer_ledger
        WHERE customer_id='$customer_id' AND business_id='$business_id'
    ")->fetch_assoc();
    
    return (float)($result['due'] ?? 0);
}

function getCustomerLedger($conn, $business_id, $customer_id, $limit = 50) {
    $business_id = (int)$business_id;
    $customer_id = (int)$customer_id;
    $limit = (int)$limit;
    
    return $conn->query("
        SELECT * FROM customer_ledger
        WHERE customer_id='$customer_id' AND business_id='$business_id'
        ORDER BY created_at DESC
        LIMIT $limit
    ");
}

function updateCustomerBalance($conn, $business_id, $customer_id) {
    $due = getCustomerDue($conn, $business_id, $customer_id);
    
    $business_id = (int)$business_id;
    $customer_id = (int)$customer_id;
    
    $conn->query("
        UPDATE customers
        SET opening_due='$due'
        WHERE id='$customer_id' AND business_id='$business_id'
    ");
    
    return $due;
}
