<?php

/**
 * ============================================================
 * ENHANCED ADMIN PANEL - PAYMENTS, DISCOUNTS & AUTOMATION
 * ============================================================
 */

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/SubscriptionService.php';
require_once __DIR__ . '/../services/PaymentGatewayService.php';
require_once __DIR__ . '/../services/SubscriptionDiscountService.php';
require_once __DIR__ . '/../services/SubscriptionQueueService.php';

// Check if user is admin
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['super_admin', 'admin'])) {
    header('Location: ../login.php');
    exit;
}

$subscriptionService = new SubscriptionService($conn);
$paymentGatewayService = new PaymentGatewayService($conn);
$discountService = new SubscriptionDiscountService($conn);
$queueService = new SubscriptionQueueService($conn);

// Handle actions via POST
$success_message = '';
$error_message = '';
$is_error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $reviewed_by = $_SESSION['user_id'] ?? null;

    // Approve Payment
    if ($action === 'approve' && isset($_POST['payment_id'])) {
        $payment_id = $_POST['payment_id'];
        $result = $subscriptionService->approvePaymentAndActivate($payment_id, $reviewed_by);
        $success_message = $result['success'] ? 'Payment approved & subscription activated!' : $result['message'];
        $is_error = !$result['success'];
    }

    // Reject Payment
    elseif ($action === 'reject' && isset($_POST['payment_id'])) {
        $payment_id = $_POST['payment_id'];
        $reason = $_POST['reason'] ?? 'Admin rejection';
        $result = $subscriptionService->rejectPayment($payment_id, $reason, $reviewed_by);
        $success_message = $result['success'] ? 'Payment rejected!' : $result['message'];
        $is_error = !$result['success'];
    }

    // Apply Discount (Amount)
    elseif ($action === 'apply_discount_amount' && isset($_POST['subscription_id'])) {
        $subscription_id = $_POST['subscription_id'];
        $discount_amount = floatval($_POST['discount_amount'] ?? 0);
        $discount_reason = $_POST['discount_reason'] ?? 'Admin discount';
        
        $result = $discountService->applyDiscount($subscription_id, $discount_amount, $discount_reason, $reviewed_by);
        $success_message = $result['success'] ? "Discount ৳{$result['discount_amount']} applied!" : $result['message'];
        $is_error = !$result['success'];
    }

    // Apply Discount (Percentage)
    elseif ($action === 'apply_discount_percent' && isset($_POST['subscription_id'])) {
        $subscription_id = $_POST['subscription_id'];
        $discount_percent = floatval($_POST['discount_percent'] ?? 0);
        $discount_reason = $_POST['discount_reason'] ?? 'Admin discount';
        
        $result = $discountService->applyPercentageDiscount($subscription_id, $discount_percent, $discount_reason, $reviewed_by);
        $success_message = $result['success'] ? "Discount {$discount_percent}% applied!" : $result['message'];
        $is_error = !$result['success'];
    }

    // Remove Discount
    elseif ($action === 'remove_discount' && isset($_POST['subscription_id'])) {
        $subscription_id = $_POST['subscription_id'];
        $result = $discountService->removeDiscount($subscription_id);
        $success_message = $result['success'] ? 'Discount removed!' : $result['message'];
        $is_error = !$result['success'];
    }
}

// Get data
$pending_payments = $subscriptionService->getPendingPayments(100);
$gateways = $paymentGatewayService->getAllGateways();
$discounted_subscriptions = $discountService->getSubscriptionsWithDiscounts(20);
$total_discounts = $discountService->getTotalDiscountsGiven();
$queue_status = $queueService->getQueueStatus();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Subscriptions & Payments</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }

        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            margin-bottom: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
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

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .stat-card.pending {
            border-left: 4px solid #f39c12;
        }

        .stat-card.approved {
            border-left: 4px solid #27ae60;
        }

        .stat-card.discounts {
            border-left: 4px solid #3498db;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #2c3e50;
        }

        .stat-label {
            font-size: 14px;
            color: #7f8c8d;
            margin-top: 5px;
        }

        .section {
            background: white;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #34495e;
            color: white;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid #ecf0f1;
        }

        tbody tr:hover {
            background: #f8f9fa;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-pending {
            background: #fff3cd;
            color: #856404;
        }

        .badge-completed {
            background: #d4edda;
            color: #155724;
        }

        .badge-failed {
            background: #f8d7da;
            color: #721c24;
        }

        .btn-group {
            display: flex;
            gap: 8px;
        }

        button {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 13px;
        }

        .btn-approve {
            background: #27ae60;
            color: white;
        }

        .btn-approve:hover {
            background: #229954;
        }

        .btn-reject {
            background: #e74c3c;
            color: white;
        }

        .btn-reject:hover {
            background: #c0392b;
        }

        .btn-discount {
            background: #3498db;
            color: white;
        }

        .btn-discount:hover {
            background: #2980b9;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 8px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #34495e;
        }

        input, textarea, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #bdc3c7;
            border-radius: 4px;
            font-size: 14px;
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #667eea;
            background: #f8f9ff;
        }

        .modal-footer {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn-cancel {
            background: #95a5a6;
            color: white;
        }

        .btn-submit {
            background: #667eea;
            color: white;
        }

        .gateway-info {
            background: #f0f4ff;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }

        .gateway-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #ecf0f1;
        }

        .gateway-item:last-child {
            border-bottom: none;
        }

        .gateway-name {
            font-weight: 600;
            color: #2c3e50;
        }

        .gateway-number {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            color: #27ae60;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="container">
            <h1>💳 Admin Dashboard - Subscriptions & Payments</h1>
        </div>
    </div>

    <div class="container">
        <?php if (!empty($success_message)): ?>
            <div class="alert <?php echo $is_error ? 'alert-error' : 'alert-success'; ?> show">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="dashboard-grid">
            <div class="stat-card pending">
                <div class="stat-value"><?php echo count($pending_payments); ?></div>
                <div class="stat-label">Pending Payments</div>
            </div>
            <div class="stat-card discounts">
                <div class="stat-value">৳<?php echo number_format($total_discounts['total_discount_amount'] ?? 0, 0); ?></div>
                <div class="stat-label">Total Discounts Given</div>
            </div>
            <div class="stat-card approved">
                <div class="stat-value"><?php echo $total_discounts['total_discounted_subscriptions'] ?? 0; ?></div>
                <div class="stat-label">Subscriptions with Discounts</div>
            </div>
        </div>

        <!-- Payment Gateways Info -->
        <div class="section">
            <h2>📱 Payment Gateway Configuration</h2>
            <div class="gateway-info">
                <h3>Active Gateways</h3>
                <div style="margin-top: 15px;">
                    <?php foreach ($gateways as $gateway): ?>
                        <div class="gateway-item">
                            <div>
                                <span class="gateway-name"><?php echo htmlspecialchars($gateway['gateway_name']); ?></span>
                                <p style="font-size: 12px; color: #7f8c8d; margin-top: 3px;"><?php echo htmlspecialchars($gateway['account_name']); ?></p>
                            </div>
                            <span class="gateway-number"><?php echo htmlspecialchars($gateway['phone_number']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Pending Payments -->
        <div class="section">
            <h2>💰 Pending Payments for Review</h2>
            <?php if (empty($pending_payments)): ?>
                <p style="text-align: center; color: #7f8c8d; padding: 30px;">✓ No pending payments</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Payment #</th>
                            <th>Transaction ID</th>
                            <th>Gateway</th>
                            <th>Amount</th>
                            <th>Plan</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_payments as $payment): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($payment['payment_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars(substr($payment['transaction_id'], 0, 15) . '...'); ?></td>
                                <td><?php echo htmlspecialchars($payment['payment_method'] ?? 'Online'); ?></td>
                                <td>৳<?php echo number_format($payment['amount'], 0); ?></td>
                                <td><?php echo htmlspecialchars($payment['plan_name']); ?></td>
                                <td><?php echo date('M d', strtotime($payment['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn-approve" onclick="approvePayment(<?php echo $payment['id']; ?>)">✓ Approve</button>
                                        <button class="btn-reject" onclick="openRejectModal(<?php echo $payment['id']; ?>)">✕ Reject</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Subscriptions with Discounts -->
        <div class="section">
            <h2>💳 Subscriptions with Active Discounts</h2>
            <?php if (empty($discounted_subscriptions)): ?>
                <p style="text-align: center; color: #7f8c8d; padding: 30px;">No discounted subscriptions yet</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Business ID</th>
                            <th>Plan</th>
                            <th>Original Price</th>
                            <th>Discount</th>
                            <th>Final Price</th>
                            <th>Reason</th>
                            <th>Applied By</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($discounted_subscriptions as $sub): ?>
                            <tr>
                                <td><?php echo $sub['business_id']; ?></td>
                                <td><?php echo htmlspecialchars($sub['plan_name']); ?></td>
                                <td>৳<?php echo number_format($sub['original_price'], 0); ?></td>
                                <td style="color: #e74c3c; font-weight: 600;">
                                    -৳<?php echo number_format($sub['discount_amount'], 0); ?>
                                </td>
                                <td style="color: #27ae60; font-weight: 600;">
                                    ৳<?php echo number_format($sub['discounted_price'], 0); ?>
                                </td>
                                <td><?php echo htmlspecialchars($sub['discount_reason']); ?></td>
                                <td><?php echo htmlspecialchars($sub['applied_by_name']); ?></td>
                                <td><?php echo date('M d', strtotime($sub['discount_applied_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="modal">
        <div class="modal-content">
            <h3>Reject Payment</h3>
            <form method="post">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" id="rejectPaymentId" name="payment_id" value="">
                
                <div class="form-group">
                    <label>Rejection Reason</label>
                    <textarea name="reason" required placeholder="Why are you rejecting this payment?"></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeRejectModal()">Cancel</button>
                    <button type="submit" class="btn-submit">Reject Payment</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function approvePayment(paymentId) {
            if (confirm('Approve this payment and activate subscription?')) {
                const form = document.createElement('form');
                form.method = 'post';
                form.innerHTML = `
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="payment_id" value="${paymentId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function openRejectModal(paymentId) {
            document.getElementById('rejectPaymentId').value = paymentId;
            document.getElementById('rejectModal').classList.add('show');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.remove('show');
        }

        document.getElementById('rejectModal').addEventListener('click', function(e) {
            if (e.target === this) closeRejectModal();
        });
    </script>
</body>
</html>
