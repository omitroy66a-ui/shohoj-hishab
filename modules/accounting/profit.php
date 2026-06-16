<?php
/**
 * Profit & Loss Calculation Engine
 * Auto-calculates business profit
 */

function calculateProfit($conn, $business_id) {
    $business_id = (int)$business_id;
    
    // Total Sales
    $sales = $conn->query("
        SELECT COALESCE(SUM(grand_total), 0) as total
        FROM sales
        WHERE business_id='$business_id'
    ")->fetch_assoc()['total'] ?? 0;
    
    // Total Purchases
    $purchases = $conn->query("
        SELECT COALESCE(SUM(grand_total), 0) as total
        FROM purchases
        WHERE business_id='$business_id'
    ")->fetch_assoc()['total'] ?? 0;
    
    // Total Expenses
    $expenses = $conn->query("
        SELECT COALESCE(SUM(amount), 0) as total
        FROM expenses
        WHERE business_id='$business_id'
    ")->fetch_assoc()['total'] ?? 0;
    
    // Total Salaries
    $salaries = $conn->query("
        SELECT COALESCE(SUM(amount), 0) as total
        FROM salary_payments
        WHERE business_id='$business_id'
    ")->fetch_assoc()['total'] ?? 0;
    
    $profit = $sales - ($purchases + $expenses + $salaries);
    
    return [
        'sales' => (float)$sales,
        'purchases' => (float)$purchases,
        'expenses' => (float)$expenses,
        'salaries' => (float)$salaries,
        'total_costs' => (float)($purchases + $expenses + $salaries),
        'profit' => (float)$profit,
        'profit_percentage' => $sales > 0 ? (float)(($profit / $sales) * 100) : 0
    ];
}

function getProfitTrend($conn, $business_id, $days = 30) {
    $business_id = (int)$business_id;
    $days = (int)$days;
    
    $start_date = date('Y-m-d', strtotime("-$days days"));
    
    return $conn->query("
        SELECT
            DATE(COALESCE(s.created_at, e.created_at)) as date,
            COALESCE(SUM(s.grand_total), 0) as sales,
            COALESCE(SUM(e.amount), 0) as expenses
        FROM (SELECT created_at, grand_total FROM sales WHERE business_id='$business_id' AND created_at >= '$start_date') s
        FULL OUTER JOIN (SELECT created_at, amount FROM expenses WHERE business_id='$business_id' AND created_at >= '$start_date') e
        ON DATE(s.created_at) = DATE(e.created_at)
        GROUP BY DATE(COALESCE(s.created_at, e.created_at))
        ORDER BY date DESC
    ");
}
