<?php

/**
 * ============================================================
 * USER SUBSCRIPTION & UPGRADE DASHBOARD
 * ============================================================
 */

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/SubscriptionService.php';
require_once __DIR__ . '/../services/SubscriptionMiddleware.php';

$subscriptionService = new SubscriptionService($conn);
$subscriptionMiddleware = new SubscriptionMiddleware($conn);

$business_id = $_SESSION['business_id'] ?? null;
if (!$business_id) {
    header('Location: ../login.php');
    exit;
}

// Get subscription status
$status = $subscriptionMiddleware->getSubscriptionStatus($business_id);
$all_plans = $subscriptionService->getAllPlans();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Subscription - Sohoj Hishab</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1100px;
            margin: 0 auto;
        }
        
        header {
            text-align: center;
            color: white;
            margin-bottom: 40px;
        }
        
        header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .current-plan {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .plan-status {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .status-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        
        .status-card.trial {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .status-card.standard {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .status-card.advanced {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
        
        .status-card.expired {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }
        
        .status-card h3 {
            font-size: 20px;
            margin-bottom: 5px;
            opacity: 0.95;
        }
        
        .status-card .value {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .status-card .label {
            font-size: 12px;
            opacity: 0.85;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .current-plan h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
        }
        
        .features {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin-top: 20px;
        }
        
        .features h4 {
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .feature-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #34495e;
        }
        
        .feature-item::before {
            content: "✓";
            color: #27ae60;
            font-weight: bold;
            font-size: 18px;
        }
        
        .upgrade-section {
            margin-top: 30px;
            text-align: center;
        }
        
        .upgrade-section a {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 12px 30px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-right: 10px;
        }
        
        .upgrade-section a:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }
        
        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .plan-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            position: relative;
        }
        
        .plan-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .plan-card.featured {
            border: 3px solid #3498db;
            transform: scale(1.05);
        }
        
        .plan-card h3 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 24px;
        }
        
        .plan-card .price {
            font-size: 28px;
            color: #3498db;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .plan-card .duration {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .duration-btn {
            padding: 8px 16px;
            border: 2px solid #ecf0f1;
            background: white;
            color: #34495e;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 12px;
        }
        
        .duration-btn.active {
            background: #3498db;
            color: white;
            border-color: #3498db;
        }
        
        .duration-btn:hover {
            border-color: #3498db;
        }
        
        .plan-features {
            margin: 20px 0;
        }
        
        .plan-features ul {
            list-style: none;
            margin-bottom: 15px;
        }
        
        .plan-features li {
            padding: 8px 0;
            color: #555;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .plan-features li::before {
            content: "✓ ";
            color: #27ae60;
            font-weight: bold;
        }
        
        .plan-card.unavailable .plan-features li:not(:first-child)::before {
            content: "✕ ";
            color: #e74c3c;
        }
        
        .plan-card.unavailable .plan-features li:not(:first-child) {
            color: #95a5a6;
            text-decoration: line-through;
        }
        
        .plan-actions {
            margin-top: 20px;
        }
        
        .upgrade-btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .upgrade-btn.primary {
            background: #3498db;
            color: white;
        }
        
        .upgrade-btn.primary:hover {
            background: #2980b9;
        }
        
        .upgrade-btn.current {
            background: #27ae60;
            color: white;
        }
        
        .upgrade-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .badge {
            display: inline-block;
            background: #e74c3c;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>📱 My Subscription</h1>
            <p>Manage your subscription plan and features</p>
        </header>
        
        <div class="current-plan">
            <h2>Current Plan Status</h2>
            
            <div class="plan-status">
                <?php if ($status['type'] === 'trial'): ?>
                    <div class="status-card trial">
                        <h3>Trial Plan</h3>
                        <div class="value"><?php echo $status['days_remaining']; ?></div>
                        <div class="label">Days Remaining</div>
                    </div>
                    <div class="status-card">
                        <h3>Plan</h3>
                        <div class="value"><?php echo htmlspecialchars($status['plan']); ?></div>
                        <div class="label">Active Now</div>
                    </div>
                <?php else: ?>
                    <div class="status-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                        <h3>Active</h3>
                        <div class="value"><?php echo htmlspecialchars($status['plan']); ?></div>
                        <div class="label">Current Plan</div>
                    </div>
                    <div class="status-card">
                        <h3>Expiry</h3>
                        <div class="value"><?php echo date('M d', strtotime($status['expiry_date'])); ?></div>
                        <div class="label"><?php echo $status['days_remaining']; ?> Days Left</div>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($status['features'])): ?>
                <div class="features">
                    <h4>✓ Available Features:</h4>
                    <div class="feature-list">
                        <?php foreach ($status['features'] as $feature): ?>
                            <div class="feature-item"><?php echo htmlspecialchars($feature['feature_name']); ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($status['status'] === 'active'): ?>
                <div class="upgrade-section">
                    <?php if ($status['type'] === 'trial'): ?>
                        <a href="#upgrade">Upgrade to Premium</a>
                    <?php else: ?>
                        <a href="#renew">Renew Subscription</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="upgrade-section">
                    <a href="#plans" style="background: #e74c3c;">Activate a Plan</a>
                </div>
            <?php endif; ?>
        </div>
        
        <h2 style="color: white; margin-bottom: 20px; font-size: 24px;">Available Plans</h2>
        <div class="plans-grid">
            <?php foreach ($all_plans as $plan): ?>
                <div class="plan-card <?php echo ($status['type'] === $plan['plan_type']) ? 'current' : ''; ?>">
                    <?php if ($plan['plan_type'] === 'advanced'): ?>
                        <span class="badge">⭐ RECOMMENDED</span>
                    <?php endif; ?>
                    
                    <h3><?php echo htmlspecialchars($plan['name']); ?></h3>
                    
                    <?php if ($plan['plan_type'] !== 'trial'): ?>
                        <div class="price">৳<?php echo number_format($plan['price'], 0); ?>/month</div>
                    <?php else: ?>
                        <div class="price">Free</div>
                    <?php endif; ?>
                    
                    <div class="plan-features">
                        <ul>
                            <?php
                            // Get features for this plan
                            $stmt = $conn->prepare("
                                SELECT feature_name 
                                FROM feature_permissions 
                                WHERE plan_type = ? 
                                ORDER BY feature_name 
                                LIMIT 6
                            ");
                            $stmt->bind_param("s", $plan['plan_type']);
                            $stmt->execute();
                            $features = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                            
                            foreach ($features as $feature):
                            ?>
                                <li><?php echo htmlspecialchars($feature['feature_name']); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="plan-actions">
                        <?php if ($status['type'] === $plan['plan_type']): ?>
                            <button class="upgrade-btn current" disabled>✓ Current Plan</button>
                        <?php elseif ($plan['plan_type'] === 'trial'): ?>
                            <button class="upgrade-btn primary" onclick="selectPlan(<?php echo $plan['id']; ?>)">Start Free Trial</button>
                        <?php else: ?>
                            <button class="upgrade-btn primary" onclick="selectPlan(<?php echo $plan['id']; ?>)">Upgrade Now</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <script>
        function selectPlan(planId) {
            // Redirect to payment gateway or upgrade page
            window.location.href = `upgrade.php?plan=${planId}`;
        }
    </script>
</body>
</html>
