<?php
require_once "../../config/database.php";
require_once "../../modules/business/middleware.php";

$business_id = businessId();
$id = (int)($_GET['id'] ?? 0);

// Get the payment to delete from cashbook
$payment = $conn->query("SELECT amount FROM salary_payments WHERE id='$id' AND business_id='$business_id'")->fetch_assoc();

if($payment) {
    // Delete from salary_payments
    $conn->query("DELETE FROM salary_payments WHERE id='$id' AND business_id='$business_id'");
    
    // Delete corresponding cashbook entry
    $conn->query("DELETE FROM cashbook WHERE ref_type='salary' AND ref_id='$id' AND business_id='$business_id'");
}

header("Location: list.php");
exit;
