<?php

/**
 * ============================================================
 * PLAN UPGRADE & PAYMENT PAGE
 * ============================================================
 */

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/SubscriptionService.php';
require_once __DIR__ . '/../services/PaymentGatewayService.php';

$subscriptionService = new SubscriptionService($conn);
$paymentGatewayService = new PaymentGatewayService($conn);

$business_id = $_SESSION['business_id'] ?? null;
if (!$business_id) {
    header('Location: ../login.php');
    exit;
}

$plan_id = $_GET['plan'] ?? null;
$duration = $_GET['duration'] ?? 'monthly';

if (!$plan_id) {
    header('Location: subscription.php');
    exit;
}

// Get plan details
$plan = $subscriptionService->getPlanDetails($plan_id);
if (!$plan) {
    die('Plan not found');
}

// Get pricing
$pricing = $subscriptionService->getPlanPricing($plan_id, $duration);
if (!$pricing) {
    die('Pricing not found');
}

// Handle form submission
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $duration = $_POST['duration'] ?? 'monthly';
    $payment_number = trim($_POST['payment_number'] ?? '');
    $transaction_id = trim($_POST['transaction_id'] ?? '');
    $payment_method = $_POST['payment_method'] ?? 'online';

    // Validate
    if (empty($payment_number) || empty($transaction_id)) {
        $error = 'Payment Number and Transaction ID are required';
    } else {
        // Step 1: Create pending subscription
        $result = $subscriptionService->upgradeSubscription($business_id, $plan_id, $duration);

        if ($result['success']) {
            $subscription_id = $result['subscription_id'];

            // Step 2: Process payment (record it as pending)
            $pricing = $subscriptionService->getPlanPricing($plan_id, $duration);
            $amount = $pricing['price'];

            $payment_result = $subscriptionService->processPayment(
                $subscription_id,
                $payment_number,
                $transaction_id,
                $amount,
                $payment_method
            );

            if ($payment_result['success']) {
                $success = 'Payment recorded successfully! Admin will review and activate your plan shortly.';
                $payment_id = $payment_result['payment_id'];
            } else {
                $error = 'Failed to process payment: ' . $payment_result['message'];
            }
        } else {
            $error = 'Failed to create subscription: ' . $result['message'];
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upgrade to <?php echo htmlspecialchars($plan['name']); ?> - Sohoj Hishab</title>
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
            max-width: 900px;
            margin: 0 auto;
        }

        .payment-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        @media (max-width: 768px) {
            .payment-container {
                grid-template-columns: 1fr;
            }
        }

        .summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .summary h2 {
            font-size: 28px;
            margin-bottom: 20px;
        }

        .plan-name {
            font-size: 24px;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .price-display {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .price-period {
            font-size: 14px;
            opacity: 0.8;
            margin-bottom: 30px;
        }

        .summary-items {
            border-top: 1px solid rgba(255,255,255,0.3);
            padding-top: 20px;
            margin-top: 20px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .summary-item.total {
            border-top: 1px solid rgba(255,255,255,0.3);
            padding-top: 15px;
            margin-top: 15px;
            font-size: 18px;
            font-weight: 700;
        }

        .payment-form {
            padding: 40px;
        }

        .payment-form h3 {
            color: #2c3e50;
            margin-bottom: 25px;
            font-size: 20px;
        }

        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: none;
        }

        .alert.show {
            display: block;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #34495e;
            font-weight: 600;
        }

        input[type="text"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ecf0f1;
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
            background: #f8f9ff;
        }

        .duration-options {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        .duration-option {
            display: none;
        }

        .duration-option input[type="radio"] {
            display: none;
        }

        .duration-option label {
            display: block;
            padding: 15px;
            border: 2px solid #ecf0f1;
            border-radius: 6px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 0;
        }

        .duration-option input[type="radio"]:checked + label {
            border-color: #667eea;
            background: #f0f4ff;
            color: #667eea;
            font-weight: 600;
        }

        .duration-option {
            display: block;
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .submit-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .back-link {
            display: block;
            text-align: center;
            color: white;
            text-decoration: none;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .info-box {
            background: #f0f4ff;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #34495e;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="subscription.php" class="back-link">← Back to Subscription</a>

        <div class="payment-container">
            <!-- Summary Section -->
            <div class="summary">
                <h2>Order Summary</h2>

                <div class="plan-name">
                    <?php echo htmlspecialchars($plan['name']); ?> Plan
                </div>

                <div class="price-display">
                    ৳<?php echo number_format($pricing['price'], 0); ?>
                </div>

                <div class="price-period">
                    <?php 
                    $periods = ['monthly' => 'per month', 'six_months' => 'for 6 months', 'yearly' => 'per year'];
                    echo $periods[$duration] ?? 'per month';
                    ?>
                </div>

                <div class="summary-items">
                    <div class="summary-item">
                        <span>Plan:</span>
                        <span><?php echo htmlspecialchars($plan['name']); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Duration:</span>
                        <span><?php 
                        $duration_labels = ['monthly' => '1 Month', 'six_months' => '6 Months', 'yearly' => '1 Year'];
                        echo $duration_labels[$duration] ?? '1 Month';
                        ?></span>
                    </div>
                    <?php if ($pricing['discount_percentage'] > 0): ?>
                        <div class="summary-item">
                            <span>Discount:</span>
                            <span><?php echo $pricing['discount_percentage']; ?>%</span>
                        </div>
                    <?php endif; ?>
                    <div class="summary-item total">
                        <span>Total:</span>
                        <span>৳<?php echo number_format($pricing['price'], 0); ?></span>
                    </div>
                </div>
            </div>

            <!-- Payment Form Section -->
            <div class="payment-form">
                <h3>Complete Your Payment</h3>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success show">
                        ✓ <?php echo htmlspecialchars($success); ?>
                        <p style="margin-top: 10px;">
                            <a href="subscription.php" style="color: inherit; text-decoration: underline;">View your subscription</a>
                        </p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-error show">
                        ✕ <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="info-box">
                        ℹ️ Enter your payment details below. Admin will review and activate your plan within 2-24 hours.
                    </div>

                    <div class="form-group">
                        <label>Duration</label>
                        <div class="duration-options">
                            <div class="duration-option">
                                <input type="radio" id="duration_monthly" name="duration" value="monthly" <?php echo $duration === 'monthly' ? 'checked' : ''; ?>>
                                <label for="duration_monthly">Monthly<br>৳<?php 
                                $monthly = $subscriptionService->getPlanPricing($plan_id, 'monthly');
                                echo number_format($monthly['price'], 0); 
                                ?></label>
                            </div>
                            <div class="duration-option">
                                <input type="radio" id="duration_six" name="duration" value="six_months" <?php echo $duration === 'six_months' ? 'checked' : ''; ?>>
                                <label for="duration_six">6 Months<br>৳<?php 
                                $six = $subscriptionService->getPlanPricing($plan_id, 'six_months');
                                echo number_format($six['price'], 0); 
                                ?></label>
                            </div>
                            <div class="duration-option">
                                <input type="radio" id="duration_yearly" name="duration" value="yearly" <?php echo $duration === 'yearly' ? 'checked' : ''; ?>>
                                <label for="duration_yearly">Yearly<br>৳<?php 
                                $yearly = $subscriptionService->getPlanPricing($plan_id, 'yearly');
                                echo number_format($yearly['price'], 0); 
                                ?></label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="payment_method">Payment Method *</label>
                        <select id="payment_method" name="payment_method" required>
                            <option value="">-- Select Payment Method --</option>
                            <option value="online">Online Payment</option>
                            <option value="bkash">bKash</option>
                            <option value="nagad">Nagad</option>
                            <option value="rocket">Rocket</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="payment_number">Payment Number / Reference *</label>
                        <input type="text" id="payment_number" name="payment_number" placeholder="e.g., 1234567890 or Invoice #12345" required>
                        <small style="color: #7f8c8d; display: block; margin-top: 5px;">Your transaction or payment ID</small>
                    </div>

                    <div class="form-group">
                        <label for="transaction_id">Transaction ID / Receipt Number *</label>
                        <input type="text" id="transaction_id" name="transaction_id" placeholder="e.g., TXN-20231215-12345" required>
                        <small style="color: #7f8c8d; display: block; margin-top: 5px;">Unique transaction identifier from payment gateway</small>
                    </div>

                    <button type="submit" class="submit-btn">Complete Payment (৳<?php echo number_format($pricing['price'], 0); ?>)</button>
                </form>

                <p style="text-align: center; color: #7f8c8d; margin-top: 20px; font-size: 12px;">
                    Your payment information is secure. Admin review typically takes 2-24 hours.
                </p>
            </div>
        </div>
    </div>

    <script>
        // Update prices when duration changes
        document.querySelectorAll('input[name="duration"]').forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.checked) {
                    // Optionally reload to show updated pricing
                    // Or update via AJAX
                }
            });
        });
    </script>
</body>
</html>
