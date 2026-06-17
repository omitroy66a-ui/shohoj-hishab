<?php

/**
 * ============================================================
 * ADMIN SUBSCRIPTION & PAYMENT MANAGEMENT PANEL
 * ============================================================
 */

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/SubscriptionService.php';

// Check if user is admin
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['super_admin', 'admin'])) {
    header('Location: ../login.php');
    exit;
}

$subscriptionService = new SubscriptionService($conn);

// Handle payment approval/rejection via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $payment_id = $_POST['payment_id'] ?? '';
    $reviewed_by = $_SESSION['user_id'] ?? null;

    if ($action === 'approve' && $payment_id) {
        $result = $subscriptionService->approvePaymentAndActivate($payment_id, $reviewed_by);
        $success_message = $result['success'] ? 'Payment approved and subscription activated!' : $result['message'];
        $is_error = !$result['success'];
    } elseif ($action === 'reject' && $payment_id) {
        $reason = $_POST['reason'] ?? 'Admin rejection';
        $result = $subscriptionService->rejectPayment($payment_id, $reason, $reviewed_by);
        $success_message = $result['success'] ? 'Payment rejected!' : $result['message'];
        $is_error = !$result['success'];
    }
}

// Get pending payments
$pending_payments = $subscriptionService->getPendingPayments(100);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription & Payment Management - Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 10px;
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
        
        .content {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
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
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-failed {
            background: #f8d7da;
            color: #721c24;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        button {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
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
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        
        .modal-header {
            margin-bottom: 20px;
        }
        
        .modal-header h3 {
            color: #2c3e50;
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
            font-family: inherit;
        }
        
        textarea {
            resize: vertical;
            min-height: 80px;
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
        
        .btn-cancel:hover {
            background: #7f8c8d;
        }
        
        .btn-submit {
            background: #3498db;
            color: white;
        }
        
        .btn-submit:hover {
            background: #2980b9;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        
        .stat-card h3 {
            font-size: 28px;
            margin-bottom: 8px;
        }
        
        .stat-card p {
            font-size: 14px;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>💳 Subscription & Payment Management</h1>
            <p>Review and approve/reject pending subscription payments</p>
        </header>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert <?php echo $is_error ? 'alert-error' : 'alert-success'; ?> show">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <div class="content">
            <div class="stats">
                <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h3><?php echo count($pending_payments); ?></h3>
                    <p>Pending Payments</p>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <h3><?php 
                        $total_amount = array_sum(array_map(function($p) { return $p['amount']; }, $pending_payments));
                        echo '৳' . number_format($total_amount, 2);
                    ?></h3>
                    <p>Total Pending Amount</p>
                </div>
            </div>
            
            <h2>📋 Pending Payments</h2>
            
            <?php if (empty($pending_payments)): ?>
                <p style="text-align: center; color: #7f8c8d; padding: 40px;">
                    ✓ No pending payments. All payments are reviewed!
                </p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Payment #</th>
                            <th>Transaction ID</th>
                            <th>Business ID</th>
                            <th>Plan</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_payments as $payment): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($payment['payment_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars(substr($payment['transaction_id'], 0, 20) . '...'); ?></td>
                                <td><?php echo $payment['business_id']; ?></td>
                                <td><?php echo htmlspecialchars($payment['plan_name']); ?></td>
                                <td><strong>৳<?php echo number_format($payment['amount'], 2); ?></strong></td>
                                <td><?php echo htmlspecialchars($payment['payment_method'] ?? 'Online'); ?></td>
                                <td><?php echo date('M d, Y', strtotime($payment['created_at'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($payment['payment_status']); ?>">
                                        <?php echo ucfirst($payment['payment_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-approve" onclick="approvePayment(<?php echo $payment['id']; ?>)">
                                            ✓ Approve
                                        </button>
                                        <button class="btn-reject" onclick="openRejectModal(<?php echo $payment['id']; ?>)">
                                            ✕ Reject
                                        </button>
                                    </div>
                                </td>
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
            <div class="modal-header">
                <h3>Reject Payment</h3>
            </div>
            <form method="post">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" id="rejectPaymentId" name="payment_id" value="">
                
                <div class="form-group">
                    <label>Rejection Reason</label>
                    <textarea name="reason" required placeholder="Enter reason for rejection..."></textarea>
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
            if (confirm('Are you sure you want to approve this payment and activate the subscription?')) {
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
        
        // Close modal on outside click
        document.getElementById('rejectModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRejectModal();
            }
        });
    </script>
</body>
</html>
