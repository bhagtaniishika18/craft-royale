<?php
session_start();

// Strong cache prevention headers
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0, private');
header('Pragma: no-cache');
header('Expires: 0');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include '../includes/db.php';

// Fix old orders: Update UPI, credit_card, bank_deposit orders to 'paid' if they're 'pending'
// This fixes orders created before the payment status logic was corrected
$fix_query = "UPDATE orders 
              SET payment_status = 'paid' 
              WHERE payment_status = 'pending' 
              AND payment_method IN ('upi', 'credit_card', 'bank_deposit', 'razorpay')";
mysqli_query($conn, $fix_query);

// Get all orders
$orders_query = "SELECT o.*, u.first_name, u.last_name, u.email as user_email 
                 FROM orders o 
                 LEFT JOIN users u ON o.user_id = u.id 
                 ORDER BY o.created_at DESC";
$orders_result = mysqli_query($conn, $orders_query);
$orders = [];
while ($order = mysqli_fetch_assoc($orders_result)) {
    $orders[] = $order;
}

// Get return/refund requests for orders
$return_requests = [];
foreach ($orders as $order) {
    $return_query = "SELECT * FROM return_refund_requests WHERE order_id = {$order['id']} ORDER BY created_at DESC LIMIT 1";
    $return_result = mysqli_query($conn, $return_query);
    if ($return_result && mysqli_num_rows($return_result) > 0) {
        $return_requests[$order['id']] = mysqli_fetch_assoc($return_result);
    }
}

// Get order statistics
$total_orders = count($orders);
$pending_orders = 0;
$paid_orders = 0;
$total_revenue = 0;
foreach ($orders as $order) {
    if ($order['payment_status'] === 'paid') {
        $paid_orders++;
        $total_revenue += floatval($order['total_amount']);
    }
    if ($order['order_status'] === 'pending') {
        $pending_orders++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Manage Orders - Craft Royale Admin</title>
    <?php include 'favicon.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .orders-container {
            padding: 25px 40px;
            overflow-x: hidden;
            max-width: 100%;
        }
        
        body {
            overflow-x: hidden;
        }
        
        .orders-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .orders-stats .stat-card {
            background: #fff;
            padding: 20px 25px;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            border: 1px solid rgba(47, 199, 180, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .orders-stats .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(135deg, #2fc7b4, #2fa76b);
        }
        
        .orders-stats .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        }
        
        .orders-stats .stat-card h3 {
            margin: 0;
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 800;
        }
        
        .orders-stats .stat-card .stat-value {
            font-size: 2.2rem;
            font-weight: 800;
            color: #1e293b;
            background: linear-gradient(135deg, #2fc7b4, #2fa76b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            letter-spacing: -1px;
        }
        
        .orders-table {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            overflow: hidden;
            border: 1px solid rgba(47, 199, 180, 0.1);
        }
        
        .orders-table {
            overflow: hidden;
            max-width: 100%;
        }
        
        .orders-table table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
        }
        
        .orders-table thead {
            background: linear-gradient(135deg, #2fc7b4, #2fa76b);
            color: #fff;
        }
        
        .orders-table th {
            padding: 10px 8px;
            text-align: left;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }
        
        .orders-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 12px;
            vertical-align: middle;
        }
        
        .orders-table tbody tr {
            transition: all 0.2s ease;
        }
        
        .orders-table tbody tr:hover {
            background: rgba(47, 199, 180, 0.05);
        }
        
        .orders-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-badge.pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-badge.paid {
            background: #d4edda;
            color: #155724;
        }
        
        .status-badge.failed {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-badge.processing {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-badge.shipped {
            background: #cce5ff;
            color: #004085;
        }
        
        .status-badge.delivered {
            background: #d4edda;
            color: #155724;
        }
        
        .status-badge.cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn-view {
            padding: 5px 10px;
            background: #2fc7b4;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 11px;
            text-decoration: none;
            display: inline-block;
            white-space: nowrap;
        }
        
        .btn-view:hover {
            background: #2fa76b;
        }
        
        .btn-update {
            padding: 6px 12px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .btn-update:hover {
            background: #0056b3;
        }
        
        /* Order Details Modal */
        .order-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 10000;
            overflow-y: auto;
            padding: 20px;
        }
        
        .order-modal-overlay.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .order-modal {
            background: #fff;
            border-radius: 16px;
            max-width: 900px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            position: relative;
            margin: auto;
        }
        
        .order-modal-header {
            background: linear-gradient(135deg, #2fc7b4, #2fa76b);
            color: #fff;
            padding: 20px 30px;
            border-radius: 16px 16px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .order-modal-header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        
        .order-modal-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: #fff;
            font-size: 24px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .order-modal-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }
        
        .order-modal-body {
            padding: 30px;
        }
        
        .order-details-section {
            margin-bottom: 30px;
        }
        
        .order-details-section h3 {
            color: #2b2b2b;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .order-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .order-info-item {
            display: flex;
            flex-direction: column;
        }
        
        .order-info-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .order-info-value {
            font-size: 14px;
            color: #2b2b2b;
            font-weight: 600;
        }
        
        .order-items-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
            margin-top: 15px;
        }
        
        .order-items-table thead {
            background: transparent;
        }
        
        .order-items-table th {
            padding: 12px 15px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: none;
        }
        
        .order-items-table td {
            padding: 15px;
            background: #f8f9fa;
            font-size: 13px;
            border: none;
            transition: all 0.3s ease;
        }

        .order-items-table tbody tr td:first-child {
            border-radius: 12px 0 0 12px;
        }

        .order-items-table tbody tr td:last-child {
            border-radius: 0 12px 12px 0;
        }

        .order-items-table tbody tr:hover td {
            background: #edf2f7;
            transform: scale(1.01);
        }
        
        .order-item-product {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .order-item-image {
            width: 50px;
            height: 50px;
            object-fit: contain;
            background: #fff;
            padding: 2px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border: 1px solid #f0f0f0;
        }
        
        .order-item-name {
            font-weight: 700;
            color: #2d3748;
            font-size: 14px;
        }
        
        .order-totals {
            margin-top: 30px;
            padding: 25px;
            background: #f8f9fa;
            border-radius: 16px;
            border: 1px dashed #e2e8f0;
        }
        
        .order-total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
            color: #718096;
        }
        
        .order-total-label {
            font-weight: 600;
        }
        
        .order-total-value {
            font-weight: 700;
            color: #2d3748;
        }
        
        .order-total-row.grand-total {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
            font-size: 20px;
        }
        
        .order-total-row.grand-total .order-total-label {
            color: #1a202c;
            font-size: 18px;
        }
        
        .order-total-row.grand-total .order-total-value {
            color: #2fc7b4;
            font-size: 24px;
            font-weight: 800;
        }
        
        .order-modal-loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .order-modal-loading i {
            font-size: 48px;
            color: #2fc7b4;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        /* Custom SweetAlert2 Styling - Professional */
        .swal2-popup-success,
        .swal2-popup-error {
            border-radius: 16px !important;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15) !important;
            padding: 0 !important;
            max-width: 400px !important;
            overflow: hidden !important;
            border: 1px solid rgba(0, 0, 0, 0.1) !important;
            position: relative !important;
        }
        
        .swal2-popup-success::before {
            content: '' !important;
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            height: 4px !important;
            background: linear-gradient(90deg, #2fc7b4, #2fa76b) !important;
        }
        
        .swal2-popup-error::before {
            content: '' !important;
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            height: 4px !important;
            background: linear-gradient(90deg, #e74c3c, #c0392b) !important;
        }
        
        /* Success Title */
        .swal-title-success {
            font-size: 24px !important;
            font-weight: 700 !important;
            margin: 0 0 20px 0 !important;
            padding: 25px 25px 0 25px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        
        .swal-title-success span {
            color: #2fa76b !important;
        }
        
        /* Error Title */
        .swal-title-error {
            font-size: 24px !important;
            font-weight: 700 !important;
            margin: 0 0 20px 0 !important;
            padding: 25px 25px 0 25px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        
        .swal-title-error span {
            color: #e74c3c !important;
        }
        
        /* Success Content */
        .swal-content-success {
            padding: 0 25px 25px 25px !important;
            text-align: center !important;
        }
        
        .swal-icon-wrapper {
            width: 70px !important;
            height: 70px !important;
            margin: 0 auto 20px auto !important;
            background: linear-gradient(135deg, #2fc7b4, #2fa76b) !important;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            box-shadow: 0 8px 20px rgba(47, 167, 107, 0.25) !important;
            animation: scaleIn 0.4s ease-out !important;
        }
        
        .swal-icon-wrapper i {
            font-size: 32px !important;
            color: #ffffff !important;
        }
        
        .swal-main-text {
            font-size: 16px !important;
            color: #2b2b2b !important;
            font-weight: 600 !important;
            margin: 0 0 8px 0 !important;
            line-height: 1.5 !important;
        }
        
        .swal-subtitle {
            font-size: 13px !important;
            color: #888 !important;
            font-weight: 400 !important;
            margin-top: 5px !important;
        }
        
        /* Error Content */
        .swal-content-error {
            padding: 0 25px 25px 25px !important;
            text-align: center !important;
        }
        
        .swal-icon-wrapper-error {
            width: 70px !important;
            height: 70px !important;
            margin: 0 auto 20px auto !important;
            background: linear-gradient(135deg, #e74c3c, #c0392b) !important;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            box-shadow: 0 8px 20px rgba(231, 76, 60, 0.25) !important;
            animation: scaleIn 0.4s ease-out !important;
        }
        
        .swal-icon-wrapper-error i {
            font-size: 32px !important;
            color: #ffffff !important;
        }
        
        .swal-content-error .swal-main-text {
            font-size: 16px !important;
            color: #2b2b2b !important;
            font-weight: 600 !important;
            margin: 0 0 8px 0 !important;
            line-height: 1.5 !important;
        }
        
        /* Buttons */
        .swal2-confirm-success {
            border-radius: 8px !important;
            padding: 10px 24px !important;
            font-weight: 600 !important;
            font-size: 14px !important;
            box-shadow: 0 4px 12px rgba(47, 167, 107, 0.3) !important;
            transition: all 0.2s ease !important;
            background: linear-gradient(135deg, #2fc7b4, #2fa76b) !important;
            border: none !important;
            color: #ffffff !important;
        }
        
        .swal2-confirm-success:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 16px rgba(47, 167, 107, 0.4) !important;
            background: linear-gradient(135deg, #2fa76b, #2fc7b4) !important;
        }
        
        .swal2-confirm-error {
            border-radius: 8px !important;
            padding: 10px 24px !important;
            font-weight: 600 !important;
            font-size: 14px !important;
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3) !important;
            transition: all 0.2s ease !important;
            background: linear-gradient(135deg, #e74c3c, #c0392b) !important;
            border: none !important;
            color: #ffffff !important;
        }
        
        .swal2-confirm-error:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 16px rgba(231, 76, 60, 0.4) !important;
            background: linear-gradient(135deg, #c0392b, #e74c3c) !important;
        }
        
        /* Timer Progress Bar */
        .swal2-timer-progress-bar {
            background: linear-gradient(90deg, #2fc7b4, #2fa76b) !important;
            height: 3px !important;
        }
        
        .swal2-html-container-custom {
            margin: 0 !important;
            padding: 0 !important;
        }
        
        .swal2-title {
            margin: 0 !important;
            padding: 0 !important;
        }
        
        /* Professional Animations */
        @keyframes scaleIn {
            0% {
                transform: scale(0.8);
                opacity: 0;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        .swal2-show-animation {
            animation: swal2-show 0.4s !important;
        }
        
        .swal2-hide-animation {
            animation: swal2-hide 0.3s !important;
        }
        
        @keyframes swal2-show {
            0% {
                transform: scale(0.7) translateY(-20px);
                opacity: 0;
            }
            100% {
                transform: scale(1) translateY(0);
                opacity: 1;
            }
        }
        
        @keyframes swal2-hide {
            0% {
                transform: scale(1) translateY(0);
                opacity: 1;
            }
            100% {
                transform: scale(0.7) translateY(-20px);
                opacity: 0;
            }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1>Manage Orders</h1>
                    <p class="welcome-text">View and manage all customer orders</p>
                </div>
            </header>
            
            <div class="orders-container">
                <!-- Statistics -->
                <div class="orders-stats">
                    <div class="stat-card">
                        <h3>Total Orders</h3>
                        <div class="stat-value"><?= $total_orders ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Paid Orders</h3>
                        <div class="stat-value"><?= $paid_orders ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Pending Orders</h3>
                        <div class="stat-value"><?= $pending_orders ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Total Revenue</h3>
                        <div class="stat-value">₹<?= number_format($total_revenue, 0) ?></div>
                    </div>
                </div>
                
                <!-- Orders Table -->
                <div class="orders-table">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Payment Method</th>
                                <th>Payment Status</th>
                                <th>Order Status</th>
                                <th>Return/Refund</th>
                                <th style="text-align: right;">Amount</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 40px; color: #666;">
                                        No orders found
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($orders as $order): 
                                    $order_date = date('d M Y, h:i A', strtotime($order['created_at']));
                                    $customer_name = ($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? '');
                                    if (trim($customer_name) === '') {
                                        $customer_name = $order['name'] ?? 'N/A';
                                    }
                                    
                                    $payment_methods = [
                                        'razorpay' => 'Razorpay',
                                        'upi' => 'UPI',
                                        'credit_card' => 'Credit Card',
                                        'bank_deposit' => 'Bank Deposit',
                                        'cod' => 'COD'
                                    ];
                                    $payment_method_display = $payment_methods[$order['payment_method']] ?? ucfirst($order['payment_method']);
                                ?>
                                    <tr>
                                        <td style="white-space: nowrap; font-size: 11px; word-break: break-all;"><strong><?= htmlspecialchars($order['order_number']) ?></strong></td>
                                        <td style="font-size: 11px;">
                                            <div style="font-weight: 600; font-size: 11px; margin-bottom: 2px;"><?= htmlspecialchars($customer_name) ?></div>
                                            <small style="color: #666; font-size: 10px; word-break: break-word;"><?= htmlspecialchars($order['email']) ?></small>
                                        </td>
                                        <td style="white-space: nowrap; font-size: 11px;"><?= date('d M Y', strtotime($order['created_at'])) ?><br><small style="color: #666; font-size: 10px;"><?= date('h:i A', strtotime($order['created_at'])) ?></small></td>
                                        <td style="font-size: 11px; white-space: nowrap;"><?= htmlspecialchars($payment_method_display) ?></td>
                                        <td style="white-space: nowrap;">
                                            <span class="status-badge <?= $order['payment_status'] ?>" style="font-size: 10px; padding: 4px 8px; display: inline-block;">
                                                <?= ucfirst($order['payment_status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <select class="order-status-select" data-order-id="<?= $order['id'] ?>" data-current-status="<?= $order['order_status'] ?>" <?= $order['order_status'] === 'delivered' ? 'disabled' : '' ?> style="padding: 5px 6px; border-radius: 5px; border: 1px solid #ddd; font-size: 10px; cursor: <?= $order['order_status'] === 'delivered' ? 'not-allowed' : 'pointer' ?>; background-color: <?= $order['order_status'] === 'delivered' ? '#f5f5f5' : '#fff' ?>; opacity: <?= $order['order_status'] === 'delivered' ? '0.7' : '1' ?>; width: 100%; max-width: 100%;">
                                                <option value="pending" <?= $order['order_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="processing" <?= $order['order_status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                                                <option value="shipped" <?= $order['order_status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                                <option value="delivered" <?= $order['order_status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                                <option value="cancelled" <?= $order['order_status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                            </select>
                                        </td>
                                        <td>
                                            <?php 
                                            $return_request = isset($return_requests[$order['id']]) ? $return_requests[$order['id']] : null;
                                            if ($return_request && isset($return_request['status']) && !empty($return_request['status'])): 
                                                $return_status_colors = [
                                                    'pending' => '#ffc107',
                                                    'approved' => '#17a2b8',
                                                    'product_received' => '#007bff',
                                                    'product_exchanged' => '#20c997',
                                                    'product_shipped' => '#6610f2',
                                                    'completed' => '#28a745',
                                                    'refund_processing' => '#6f42c1',
                                                    'refunded' => '#28a745',
                                                    'rejected' => '#dc3545'
                                                ];
                                                $return_status_labels = [
                                                    'pending' => 'Pending',
                                                    'approved' => 'Approved',
                                                    'product_received' => 'Received',
                                                    'product_exchanged' => 'Exchanged',
                                                    'product_shipped' => 'Shipped',
                                                    'completed' => 'Completed',
                                                    'refund_processing' => 'Processing',
                                                    'refunded' => 'Refunded',
                                                    'rejected' => 'Rejected'
                                                ];
                                                $status = $return_request['status'];
                                                $status_color = isset($return_status_colors[$status]) ? $return_status_colors[$status] : '#999';
                                                $status_label = isset($return_status_labels[$status]) ? $return_status_labels[$status] : ucfirst($status);
                                            ?>
                                                <a href="manage_returns.php" style="text-decoration: none;">
                                                    <span class="status-badge" style="background: <?= $status_color ?>; color: #fff; font-size: 11px; padding: 4px 8px;">
                                                        <?= $status_label ?>
                                                    </span>
                                                </a>
                                            <?php else: ?>
                                                <span style="color: #999;">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="white-space: nowrap; font-size: 12px; font-weight: 600; text-align: right;"><strong>₹<?= number_format($order['total_amount'], 0) ?></strong></td>
                                        <td style="white-space: nowrap;">
                                            <div class="action-buttons">
                                                <button type="button" class="btn-view view-order-btn" data-order-id="<?= $order['id'] ?>" style="padding: 5px 10px; font-size: 11px; border: none; cursor: pointer;">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Order Details Modal -->
    <div class="order-modal-overlay" id="orderModal">
        <div class="order-modal">
            <div class="order-modal-header">
                <h2>Order Details</h2>
                <button class="order-modal-close" onclick="closeOrderModal()">&times;</button>
            </div>
            <div class="order-modal-body" id="orderModalBody">
                <div class="order-modal-loading">
                    <i class="fas fa-spinner"></i>
                    <p>Loading order details...</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Handle order status updates
    document.querySelectorAll('.order-status-select').forEach(select => {
        select.addEventListener('change', function() {
            // Prevent changes if order is already delivered
            const currentStatus = this.getAttribute('data-current-status');
            if (currentStatus === 'delivered') {
                this.value = 'delivered';
                Swal.fire({
                    title: '<div class="swal-title-error"><span>Cannot Change</span></div>',
                    html: '<div class="swal-content-error"><div class="swal-icon-wrapper-error"><i class="fas fa-lock"></i></div><p class="swal-main-text">Order status cannot be changed once delivered</p><div class="swal-subtitle">Delivered orders are locked</div></div>',
                    icon: false,
                    background: '#ffffff',
                    color: '#2b2b2b',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#2fa76b',
                    timer: 3000,
                    timerProgressBar: true
                });
                return;
            }
            
            const orderId = this.getAttribute('data-order-id');
            const newStatus = this.value;
            const originalValue = this.getAttribute('data-original-value') || this.options[this.selectedIndex - 1]?.value;
            
            // Prevent changing to delivered if trying to go back
            if (newStatus === 'delivered' && currentStatus !== 'delivered') {
                // Allow this - it's the final status
            }
            
            // Show loading
            this.disabled = true;
            const originalText = this.innerHTML;
            this.style.opacity = '0.6';
            
            // Send update request
            fetch('update_order_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `order_id=${orderId}&order_status=${newStatus}`
            })
            .then(response => response.json())
            .then(data => {
                this.disabled = false;
                this.style.opacity = '1';
                
                if (data.status === 'success') {
                    // Update the select to reflect new status
                    this.setAttribute('data-original-value', newStatus);
                    this.setAttribute('data-current-status', newStatus);
                    
                    // If status is now delivered, disable the dropdown
                    if (newStatus === 'delivered') {
                        this.disabled = true;
                        this.style.cursor = 'not-allowed';
                        this.style.backgroundColor = '#f5f5f5';
                        this.style.opacity = '0.7';
                    } else {
                        this.disabled = false;
                        this.style.cursor = 'pointer';
                        this.style.backgroundColor = '#fff';
                        this.style.opacity = '1';
                    }
                    
                    // Show professional success notification
                    Swal.fire({
                        title: '<div class="swal-title-success"><span>✓ Success</span></div>',
                        html: '<div class="swal-content-success"><div class="swal-icon-wrapper"><i class="fas fa-check-circle"></i></div><p class="swal-main-text">Order status updated successfully</p><div class="swal-subtitle">Changes have been saved</div></div>',
                        icon: false,
                        background: '#ffffff',
                        color: '#2b2b2b',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#2fa76b',
                        buttonsStyling: true,
                        customClass: {
                            popup: 'swal2-popup-success',
                            confirmButton: 'swal2-confirm-success',
                            htmlContainer: 'swal2-html-container-custom'
                        },
                        timer: 2500,
                        timerProgressBar: true,
                        showConfirmButton: true,
                        allowOutsideClick: true,
                        allowEscapeKey: true,
                        showClass: {
                            popup: 'swal2-show-animation'
                        },
                        hideClass: {
                            popup: 'swal2-hide-animation'
                        }
                    });
                } else {
                    // Revert to original value
                    this.value = originalValue;
                    Swal.fire({
                        title: '<div class="swal-title-error"><span>Error</span></div>',
                        html: '<div class="swal-content-error"><div class="swal-icon-wrapper-error"><i class="fas fa-exclamation-circle"></i></div><p class="swal-main-text">' + (data.message || 'Failed to update order status') + '</p><div class="swal-subtitle">Please try again</div></div>',
                        icon: false,
                        background: '#ffffff',
                        color: '#2b2b2b',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#e74c3c',
                        buttonsStyling: true,
                        customClass: {
                            popup: 'swal2-popup-error',
                            confirmButton: 'swal2-confirm-error',
                            htmlContainer: 'swal2-html-container-custom'
                        },
                        showClass: {
                            popup: 'swal2-show-animation'
                        },
                        hideClass: {
                            popup: 'swal2-hide-animation'
                        }
                    });
                }
            })
            .catch(error => {
                this.disabled = false;
                this.style.opacity = '1';
                this.value = originalValue;
                console.error('Error:', error);
                Swal.fire({
                    title: '<div class="swal-title-error"><span>Connection Error</span></div>',
                    html: '<div class="swal-content-error"><div class="swal-icon-wrapper-error"><i class="fas fa-wifi"></i></div><p class="swal-main-text">An error occurred while updating the order status.</p><div class="swal-subtitle">Please check your connection and try again</div></div>',
                    icon: false,
                    background: '#ffffff',
                    color: '#2b2b2b',
                    confirmButtonText: 'Retry',
                    confirmButtonColor: '#e74c3c',
                    buttonsStyling: true,
                    customClass: {
                        popup: 'swal2-popup-error',
                        confirmButton: 'swal2-confirm-error',
                        htmlContainer: 'swal2-html-container-custom'
                    },
                    showClass: {
                        popup: 'swal2-show-animation'
                    },
                    hideClass: {
                        popup: 'swal2-hide-animation'
                    }
                });
            });
        });
    });
    
    // Handle order view modal
    function openOrderModal(orderId) {
        const modal = document.getElementById('orderModal');
        const modalBody = document.getElementById('orderModalBody');
        
        modal.classList.add('active');
        modalBody.innerHTML = '<div class="order-modal-loading"><i class="fas fa-spinner"></i><p>Loading order details...</p></div>';
        
        fetch(`get_order_details.php?id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const order = data.order;
                    const items = data.items;
                    
                    let html = `
                        <div class="order-details-section">
                            <h3>Order Information</h3>
                            <div class="order-info-grid">
                                <div class="order-info-item">
                                    <div class="order-info-label">Order ID</div>
                                    <div class="order-info-value">#${order.order_number || order.id}</div>
                                </div>
                                <div class="order-info-item">
                                    <div class="order-info-label">Date</div>
                                    <div class="order-info-value">${order.formatted_date}</div>
                                </div>
                                <div class="order-info-item">
                                    <div class="order-info-label">Customer Name</div>
                                    <div class="order-info-value">${order.name || (order.first_name + ' ' + order.last_name) || 'N/A'}</div>
                                </div>
                                <div class="order-info-item">
                                    <div class="order-info-label">Email</div>
                                    <div class="order-info-value">${order.email || order.user_email || 'N/A'}</div>
                                </div>
                                <div class="order-info-item">
                                    <div class="order-info-label">Phone</div>
                                    <div class="order-info-value">${order.phone || 'N/A'}</div>
                                </div>
                                <div class="order-info-item">
                                    <div class="order-info-label">Payment Method</div>
                                    <div class="order-info-value">${order.payment_method ? order.payment_method.charAt(0).toUpperCase() + order.payment_method.slice(1).replace('_', ' ') : 'N/A'}</div>
                                </div>
                                <div class="order-info-item">
                                    <div class="order-info-label">Payment Status</div>
                                    <div class="order-info-value">
                                        <span class="status-badge ${order.payment_status}" style="font-size: 11px; padding: 4px 8px;">
                                            ${order.payment_status ? order.payment_status.charAt(0).toUpperCase() + order.payment_status.slice(1) : 'N/A'}
                                        </span>
                                    </div>
                                </div>
                                <div class="order-info-item">
                                    <div class="order-info-label">Order Status</div>
                                    <div class="order-info-value">
                                        <span class="status-badge ${order.order_status}" style="font-size: 11px; padding: 4px 8px;">
                                            ${order.order_status ? order.order_status.charAt(0).toUpperCase() + order.order_status.slice(1) : 'N/A'}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="order-details-section">
                            <h3>Shipping Address</h3>
                            <div class="order-info-item">
                                <div class="order-info-value">
                                    ${order.address || 'N/A'}<br>
                                    ${order.city ? order.city + ', ' : ''}${order.state || ''} ${order.zip || ''}
                                </div>
                            </div>
                        </div>
                        
                        <div class="order-details-section">
                            <h3>Order Items</h3>
                            <table class="order-items-table">
                                <thead>
                                    <tr>
                                        <th>Product Details</th>
                                        <th style="text-align: center;">Qty</th>
                                        <th style="text-align: right;">Price</th>
                                        <th style="text-align: right;">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    
                    items.forEach(item => {
                        html += `
                            <tr>
                                <td>
                                    <div class="order-item-product">
                                        <img src="${item.product_image}" alt="${item.product_name}" class="order-item-image" onerror="this.src='../assets/images/placeholder.jpg'">
                                        <div class="order-item-name">${item.product_name}</div>
                                    </div>
                                </td>
                                <td style="text-align: center;">${item.quantity}</td>
                                <td style="text-align: right;">₹${parseInt(item.unit_price)}</td>
                                <td style="text-align: right;">₹${parseInt(item.subtotal)}</td>
                            </tr>
                        `;
                    });
                    
                    html += `
                                </tbody>
                            </table>
                            
                            <div class="order-totals">
                                <div class="order-total-row">
                                    <span class="order-total-label">Subtotal:</span>
                                    <span class="order-total-value">₹${parseInt(order.subtotal || 0)}</span>
                                </div>
                    `;
                    
                    if (order.shipping_cost > 0) {
                        html += `
                                <div class="order-total-row">
                                    <span class="order-total-label">Shipping:</span>
                                    <span class="order-total-value">₹${parseInt(order.shipping_cost)}</span>
                                </div>
                        `;
                    }
                    
                    if (order.discount > 0) {
                        html += `
                                <div class="order-total-row">
                                    <span class="order-total-label">Discount:</span>
                                    <span class="order-total-value">-₹${parseInt(order.discount)}</span>
                                </div>
                        `;
                    }
                    
                    html += `
                                <div class="order-total-row grand-total">
                                    <span class="order-total-label">Total Amount:</span>
                                    <span class="order-total-value">₹${parseInt(order.total_amount)}</span>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    modalBody.innerHTML = html;
                } else {
                    modalBody.innerHTML = `<div class="order-modal-loading"><p style="color: #e74c3c;">${data.message || 'Failed to load order details'}</p></div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                modalBody.innerHTML = '<div class="order-modal-loading"><p style="color: #e74c3c;">An error occurred while loading order details.</p></div>';
            });
    }
    
    function closeOrderModal() {
        document.getElementById('orderModal').classList.remove('active');
    }
    
    // Close modal on overlay click
    document.getElementById('orderModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeOrderModal();
        }
    });
    
    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeOrderModal();
        }
    });
    
    // Add click handlers to view buttons
    document.querySelectorAll('.view-order-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.getAttribute('data-order-id');
            openOrderModal(orderId);
        });
    });
    </script>
</body>
</html>
