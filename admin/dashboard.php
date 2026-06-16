<?php
require_once __DIR__ . '/../config/database.php';
session_start();

if(!isset($_SESSION['user_id'])){
    header('Location: ../login.php');
    exit();
}

$total_sales =
$conn->query("
SELECT IFNULL(SUM(grand_total), 0) AS t
FROM sales
")->fetch_assoc()['t'];

$today_sales =
$conn->query("
SELECT IFNULL(SUM(grand_total), 0) AS t
FROM sales
WHERE DATE(created_at) = CURDATE()
")->fetch_assoc()['t'];

$total_products =
$conn->query("
SELECT COUNT(*) total
FROM products
")->fetch_assoc()['total'];

$total_categories =
$conn->query("
SELECT COUNT(*) total
FROM categories
")->fetch_assoc()['total'];

$total_purchase = 0;
if ($conn->query("SHOW TABLES LIKE 'purchases'")->num_rows > 0) {
    $total_purchase = $conn->query("
SELECT IFNULL(SUM(grand_total), 0) AS t
FROM purchases
")->fetch_assoc()['t'];
}

$today_purchase = 0;
if ($conn->query("SHOW TABLES LIKE 'purchases'")->num_rows > 0) {
    $today_purchase = $conn->query("
SELECT IFNULL(SUM(grand_total), 0) AS t
FROM purchases
WHERE DATE(created_at) = CURDATE()
")->fetch_assoc()['t'];
}

$total_expenses = 0;
$today_expenses = 0;
if ($conn->query("SHOW TABLES LIKE 'expenses'")->num_rows > 0) {
    $total_expenses = $conn->query("
SELECT IFNULL(SUM(amount), 0) AS t
FROM expenses
")->fetch_assoc()['t'];
    $today_expenses = $conn->query("
SELECT IFNULL(SUM(amount), 0) AS t
FROM expenses
WHERE DATE(created_at) = CURDATE()
")->fetch_assoc()['t'];
}

$cash_in_hand = 0;
if ($conn->query("SHOW TABLES LIKE 'cashbook'")->num_rows > 0) {
    $cash_in_hand = $conn->query("
SELECT IFNULL(SUM(CASE WHEN type='income' THEN amount ELSE 0 END),0) - IFNULL(SUM(CASE WHEN type='expense' THEN amount ELSE 0 END),0) AS t
FROM cashbook
")->fetch_assoc()['t'];
}

$total_customers =
$conn->query("
SELECT COUNT(*) t
FROM customers
")->fetch_assoc()['t'];

$customer_due = 0;
if ($conn->query("SHOW TABLES LIKE 'customer_ledger'")->num_rows > 0) {
    $customer_due = $conn->query("
SELECT IFNULL(SUM(debit - credit), 0) AS t
FROM customer_ledger
")->fetch_assoc()['t'];
}

$supplier_due = 0;
if ($conn->query("SHOW TABLES LIKE 'supplier_ledger'")->num_rows > 0) {
    $supplier_due = $conn->query("
SELECT IFNULL(SUM(debit - credit), 0) AS t
FROM supplier_ledger
")->fetch_assoc()['t'];
}

$net_profit = 0;
if ($conn->query("SHOW TABLES LIKE 'profits'")->num_rows > 0) {
    $net_profit = $conn->query("
SELECT IFNULL(SUM(profit_amount), 0) AS t
FROM profits
")->fetch_assoc()['t'];
} else {
    $net_profit = $total_sales - $total_purchase - $total_expenses;
}

$today_profit = $today_sales - $today_expenses;
$low_stock =
$conn->query("
SELECT COUNT(*) AS total
FROM products
WHERE stock <= 10
")->fetch_assoc()['total'];

$top_products = [];
if ($conn->query("SHOW TABLES LIKE 'sale_items'")->num_rows > 0) {
    $top_result = $conn->query("
SELECT p.name, SUM(si.qty) AS total_qty
FROM sale_items si
LEFT JOIN products p ON p.id = si.product_id
GROUP BY si.product_id
ORDER BY total_qty DESC
LIMIT 5
");
    while ($row = $top_result->fetch_assoc()) {
        $top_products[] = $row;
    }
}

$branch_sales = [];
if ($conn->query("SHOW TABLES LIKE 'branches'")->num_rows > 0 && $conn->query("SHOW COLUMNS FROM sales LIKE 'branch_id'")->num_rows > 0) {
    $branch_result = $conn->query("
SELECT b.name, IFNULL(SUM(s.grand_total),0) AS total_sales
FROM branches b
LEFT JOIN sales s ON s.branch_id = b.id
GROUP BY b.id
ORDER BY total_sales DESC
LIMIT 5
");
    while ($row = $branch_result->fetch_assoc()) {
        $branch_sales[] = $row;
    }
}

$staff_performance = [];
$staff_count = 0;
if ($conn->query("SHOW TABLES LIKE 'users'")->num_rows > 0) {
    $staff_count = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='staff'")->fetch_assoc()['total'];
    if ($conn->query("SHOW COLUMNS FROM sales LIKE 'user_id'")->num_rows > 0) {
        $staff_result = $conn->query("
SELECT u.name, IFNULL(SUM(s.grand_total),0) AS total_sales
FROM users u
LEFT JOIN sales s ON s.user_id = u.id
WHERE u.role = 'staff'
GROUP BY u.id
ORDER BY total_sales DESC
LIMIT 5
");
        while ($row = $staff_result->fetch_assoc()) {
            $staff_performance[] = $row;
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f4f4f4; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        h1 { color: #333; margin: 0; }
        .logout-btn { background: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer; text-decoration: none; }
        .logout-btn:hover { background: #c82333; }
        .dashboard-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
        .stat-card { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .stat-card h3 { margin: 0 0 10px 0; color: #666; }
        .stat-card p { font-size: 28px; margin: 0; color: #007bff; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>Admin Dashboard</h1>
            <p style="margin: 5px 0 0 0; color: #666;">Welcome, <?php echo $_SESSION['user_name'] ?? 'Admin'; ?></p>
        </div>
        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="dashboard-stats">
        <div class="stat-card">
            <h3>Total Sales</h3>
            <p><?php echo number_format($total_sales, 2); ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Purchase</h3>
            <p><?php echo number_format($total_purchase, 2); ?></p>
        </div>
        <div class="stat-card">
            <h3>Net Profit</h3>
            <p><?php echo number_format($net_profit, 2); ?></p>
        </div>
        <div class="stat-card">
            <h3>Cash In Hand</h3>
            <p><?php echo number_format($cash_in_hand, 2); ?></p>
        </div>
        <div class="stat-card">
            <h3>Customer Due</h3>
            <p><?php echo number_format($customer_due, 2); ?></p>
        </div>
        <div class="stat-card">
            <h3>Supplier Due</h3>
            <p><?php echo number_format($supplier_due, 2); ?></p>
        </div>
        <div class="stat-card">
            <h3>Expense Summary</h3>
            <p><?php echo number_format($total_expenses, 2); ?></p>
        </div>
        <div class="stat-card">
            <h3>Today's Sales</h3>
            <p><?php echo number_format($today_sales, 2); ?></p>
        </div>
        <div class="stat-card">
            <h3>Today's Purchase</h3>
            <p><?php echo number_format($today_purchase, 2); ?></p>
        </div>
        <div class="stat-card">
            <h3>Today's Expense</h3>
            <p><?php echo number_format($today_expenses, 2); ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Products</h3>
            <p><?php echo $total_products ?? 0; ?></p>
        </div>
        <div class="stat-card">
            <h3>Low Stock</h3>
            <p><?php echo $low_stock ?? 0; ?></p>
        </div>
    </div>

    <div style="margin-top: 30px; display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:20px;">
        <div class="stat-card">
            <h3>Top Products</h3>
            <?php if (!empty($top_products)): ?>
                <ol style="padding-left: 20px; margin:0; color:#333;">
                    <?php foreach ($top_products as $product): ?>
                        <li><?php echo htmlspecialchars($product['name'] ?: 'Unknown'); ?> (<?php echo intval($product['total_qty']); ?> sold)</li>
                    <?php endforeach; ?>
                </ol>
            <?php else: ?>
                <p style="margin:0; color:#666;">No sales product data available yet.</p>
            <?php endif; ?>
        </div>

        <div class="stat-card">
            <h3>Branch Wise Sales</h3>
            <?php if (!empty($branch_sales)): ?>
                <ul style="padding-left: 20px; margin:0; color:#333;">
                    <?php foreach ($branch_sales as $branch): ?>
                        <li><?php echo htmlspecialchars($branch['name']); ?>: <?php echo number_format($branch['total_sales'], 2); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p style="margin:0; color:#666;">Branch sales not configured in this schema.</p>
            <?php endif; ?>
        </div>

        <div class="stat-card">
            <h3>Staff Performance</h3>
            <?php if (!empty($staff_performance)): ?>
                <ul style="padding-left: 20px; margin:0; color:#333;">
                    <?php foreach ($staff_performance as $staff): ?>
                        <li><?php echo htmlspecialchars($staff['name']); ?>: <?php echo number_format($staff['total_sales'], 2); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php elseif ($staff_count > 0): ?>
                <p style="margin:0; color:#666;"><?php echo intval($staff_count); ?> staff users found, sales allocation not tracked.</p>
            <?php else: ?>
                <p style="margin:0; color:#666;">Staff performance data not available.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
