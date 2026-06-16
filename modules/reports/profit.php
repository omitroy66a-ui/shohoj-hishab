<?php
function calculateProfit($conn, $business_id)
{
    $stmt = $conn->prepare("SELECT IFNULL(SUM(grand_total), 0) AS total FROM sales WHERE business_id = ?");
    $stmt->bind_param("i", $business_id);
    $stmt->execute();
    $sales = $stmt->get_result()->fetch_assoc()['total'];

    $stmt = $conn->prepare("SELECT IFNULL(SUM(grand_total), 0) AS total FROM purchases WHERE business_id = ?");
    $stmt->bind_param("i", $business_id);
    $stmt->execute();
    $purchases = $stmt->get_result()->fetch_assoc()['total'];

    $stmt = $conn->prepare("SELECT IFNULL(SUM(amount), 0) AS total FROM expenses WHERE business_id = ?");
    $stmt->bind_param("i", $business_id);
    $stmt->execute();
    $expenses = $stmt->get_result()->fetch_assoc()['total'];

    return $sales - ($purchases + $expenses);
}

function currency($amount, $symbol = '৳')
{
    return $symbol . ' ' . number_format($amount, 2);
}

function invoiceNumber($prefix, $id)
{
    return $prefix . str_pad($id, 5, '0', STR_PAD_LEFT);
}
