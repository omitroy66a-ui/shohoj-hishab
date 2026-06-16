<?php
function syncSales($conn, $business_id, $sales)
{
    $stmt = $conn->prepare("INSERT INTO sales(business_id, invoice_no, grand_total, paid, due, created_at) VALUES(?, ?, ?, 0, ?, NOW())");
    foreach ($sales as $sale) {
        $invoice = trim($sale['invoice'] ?? '');
        $total = isset($sale['total']) ? (float) $sale['total'] : 0;
        if ($stmt) {
            $stmt->bind_param('isdd', $business_id, $invoice, $total, $total);
            $stmt->execute();
        }
    }
    if ($stmt) $stmt->close();
}
