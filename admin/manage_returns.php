<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Strong cache prevention headers
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0, private');
header('Pragma: no-cache');
header('Expires: 0');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

include '../includes/db.php';

// Check if request_type column exists
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM return_refund_requests LIKE 'request_type'");
$has_request_type = ($check_column && mysqli_num_rows($check_column) > 0);

// Automatically add the column if it's missing
if (!$has_request_type) {
    mysqli_query($conn, "ALTER TABLE return_refund_requests ADD COLUMN request_type ENUM('return', 'refund') DEFAULT 'return' AFTER order_number");
    // Refresh the check
    $check_column = mysqli_query($conn, "SHOW COLUMNS FROM return_refund_requests LIKE 'request_type'");
    $has_request_type = ($check_column && mysqli_num_rows($check_column) > 0);
}

// Get all return/refund requests
$returns_query = "SELECT r.*, o.order_number, o.total_amount, o.name as customer_name, o.email as customer_email, o.phone as customer_phone,
                 o.created_at as order_date, o.updated_at as delivery_date
                 FROM return_refund_requests r 
                 JOIN orders o ON r.order_id = o.id 
                 ORDER BY r.created_at DESC";
$returns_result = mysqli_query($conn, $returns_query);
$returns = [];
while ($return = mysqli_fetch_assoc($returns_result)) {
    $returns[] = $return;
}

// Get statistics
$total_returns = count($returns);
$pending_returns = 0;
$approved_returns = 0;
$refunded_returns = 0;
foreach ($returns as $return) {
    if ($return['status'] === 'pending') $pending_returns++;
    if ($return['status'] === 'approved') $approved_returns++;
    if ($return['status'] === 'refunded') $refunded_returns++;
}

// Reason labels
$reason_labels = [
    'colour_wrong' => 'Colour is wrong',
    'item_wrong' => 'Item is wrong',
    'defective' => 'Defective',
    'no_good_quality' => 'No good quality',
    'other' => 'Other reason'
];

$status_labels = [
    'pending' => 'Pending Review',
    'approved' => 'Approved',
    'product_received' => 'Product Received',
    'product_exchanged' => 'Product Exchanged',
    'product_shipped' => 'Product Shipped',
    'completed' => 'Completed',
    'refund_processing' => 'Refund Processing',
    'refunded' => 'Refunded',
    'rejected' => 'Rejected'
];

$status_colors = [
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Manage Returns/Refunds - Craft Royale Admin</title>
    <?php include 'favicon.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .returns-container {
            padding: 20px;
            max-width: 100%;
            overflow-x: hidden;
        }
        
        .returns-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .returns-stats .stat-card {
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            border: 1px solid rgba(47, 199, 180, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .returns-stats .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(135deg, #2fc7b4, #2fa76b);
        }
        
        .returns-stats .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        }
        
        .returns-stats .stat-card h3 {
            margin: 0 0 15px 0;
            font-size: 14px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        
        .returns-stats .stat-card .stat-value {
            font-size: 36px;
            font-weight: 800;
            color: #2b2b2b;
            background: linear-gradient(135deg, #2fc7b4, #2fa76b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .returns-table {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            border: 1px solid rgba(47, 199, 180, 0.1);
            max-width: 100%;
        }
        
        .returns-table table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        
        .table-header {
            padding: 15px 20px;
            background: linear-gradient(135deg, #2fc7b4, #2fa76b);
            color: #fff;
        }
        
        .table-header h2 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
        }
        
        
        .returns-table th {
            background: linear-gradient(135deg, rgba(47, 199, 180, 0.1), rgba(47, 167, 107, 0.1));
            padding: 10px 8px;
            text-align: left;
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #2fa76b;
            border-bottom: 2px solid rgba(47, 199, 180, 0.2);
            white-space: nowrap;
        }
        
        .returns-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 12px;
            vertical-align: middle;
        }
        
        .returns-table tbody tr {
            transition: all 0.2s ease;
        }
        
        .returns-table tbody tr:hover {
            background: rgba(47, 199, 180, 0.05);
        }
        
        .returns-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .status-badge {
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 600;
            color: #fff;
            display: inline-block;
            white-space: nowrap;
        }
        
        .action-buttons {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 10px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            white-space: nowrap;
        }
        
        .btn i {
            font-size: 10px;
        }
        
        .btn-view {
            background: #17a2b8;
            color: #fff;
        }
        
        .btn-view:hover {
            background: #138496;
        }
        
        .btn-approve {
            background: #28a745;
            color: #fff;
        }
        
        .btn-approve:hover {
            background: #218838;
        }
        
        .btn-reject {
            background: #dc3545;
            color: #fff;
        }
        
        .btn-reject:hover {
            background: #c82333;
        }
        
        .btn-received {
            background: #007bff;
            color: #fff;
        }
        
        .btn-received:hover {
            background: #0056b3;
        }
        
        .btn-refund {
            background: #6f42c1;
            color: #fff;
        }
        
        .btn-refund:hover {
            background: #5a32a3;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background: #fff;
            margin: 5% auto;
            padding: 30px;
            border-radius: 12px;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e5e5e5;
        }
        
        .modal-header h2 {
            margin: 0;
            color: #2b2b2b;
        }
        
        .close {
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #999;
        }
        
        .close:hover {
            color: #000;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2b2b2b;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 2px solid #e5e5e5;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .image-preview {
            margin-top: 15px;
            text-align: center;
        }
        
        .image-preview img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>
        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1>Manage Returns/Refunds</h1>
                    <p class="welcome-text">View and manage all return/refund requests</p>
                </div>
            </header>
            
            <div class="returns-container">
                <div class="returns-stats">
                    <div class="stat-card">
                        <h3>Total Returns</h3>
                        <div class="stat-value"><?= $total_returns ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Pending Review</h3>
                        <div class="stat-value"><?= $pending_returns ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Approved</h3>
                        <div class="stat-value"><?= $approved_returns ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Refunded</h3>
                        <div class="stat-value"><?= $refunded_returns ?></div>
                    </div>
                </div>
                
                <div class="returns-table">
                    <div class="table-header">
                        <h2><i class="fas fa-undo"></i> Return/Refund Requests</h2>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 5%;">ID</th>
                                <th style="width: 10%;">Order #</th>
                                <th style="width: 7%;">Type</th>
                                <th style="width: 15%;">Customer</th>
                                <th style="width: 10%;">Reason</th>
                                <th style="width: 8%;">Amount</th>
                                <th style="width: 12%;">Status</th>
                                <th style="width: 10%;">Date</th>
                                <th style="width: 23%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($returns)): ?>
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 40px; color: #999;">
                                        No return/refund requests found
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($returns as $return): 
                                    $request_type = ($has_request_type && isset($return['request_type'])) ? $return['request_type'] : 'return';
                                    
                                    // IMPROVEMENT: If status is refund-related, infer type as refund if it's currently return
                                    if ($request_type === 'return' && in_array($return['status'], ['refund_processing', 'refunded'])) {
                                        $request_type = 'refund';
                                    }
                                    
                                    $is_return = ($request_type === 'return');
                                    $is_refund = ($request_type === 'refund');
                                ?>
                                    <tr>
                                        <td style="white-space: nowrap;">#<?= $return['id'] ?></td>
                                        <td style="white-space: nowrap; font-size: 11px;"><?= htmlspecialchars($return['order_number']) ?></td>
                                        <td>
                                            <?php if ($is_return): ?>
                                                <span class="status-badge" style="background: #2fc7b4; color: #fff; font-size: 10px; padding: 3px 8px;">
                                                    <i class="fas fa-undo-alt"></i> Return
                                                </span>
                                            <?php else: ?>
                                                <span class="status-badge" style="background: #2fa76b; color: #fff; font-size: 10px; padding: 3px 8px;">
                                                    <i class="fas fa-money-bill-wave"></i> Refund
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="max-width: 140px;">
                                            <div style="font-weight: 600; font-size: 12px;"><?= htmlspecialchars(substr($return['customer_name'], 0, 20)) ?><?= strlen($return['customer_name']) > 20 ? '...' : '' ?></div>
                                            <small style="color: #666; font-size: 10px;"><?= htmlspecialchars(substr($return['customer_email'], 0, 20)) ?><?= strlen($return['customer_email']) > 20 ? '...' : '' ?></small>
                                        </td>
                                        <td style="max-width: 100px; font-size: 11px;">
                                            <?= htmlspecialchars($reason_labels[$return['return_reason']]) ?>
                                            <?php if ($return['return_reason'] === 'other' && $return['other_reason']): ?>
                                                <br><small style="color: #666; font-size: 10px;"><?= htmlspecialchars(substr($return['other_reason'], 0, 25)) ?>...</small>
                                            <?php endif; ?>
                                        </td>
                                        <td style="white-space: nowrap; font-size: 12px; font-weight: 600;">₹<?= number_format($return['total_amount'], 2) ?></td>
                                        <td>
                                            <span class="status-badge" style="background: <?= $status_colors[$return['status']] ?>; font-size: 10px; padding: 3px 8px;">
                                                <?= $status_labels[$return['status']] ?>
                                            </span>
                                        </td>
                                        <td style="white-space: nowrap; font-size: 11px;"><?= date('d M Y', strtotime($return['created_at'])) ?><br><small style="color: #666; font-size: 10px;"><?= date('h:i A', strtotime($return['created_at'])) ?></small></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-view" onclick="viewReturn(<?= $return['id'] ?>)">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                                <?php if ($return['status'] === 'pending'): ?>
                                                    <button class="btn btn-approve" onclick="updateStatus(<?= $return['id'] ?>, 'approved', '<?= $request_type ?>')">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                    <button class="btn btn-reject" onclick="updateStatus(<?= $return['id'] ?>, 'rejected', '<?= $request_type ?>')">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                <?php elseif ($return['status'] === 'approved'): ?>
                                                    <button class="btn btn-received" onclick="updateStatus(<?= $return['id'] ?>, 'product_received', '<?= $request_type ?>')">
                                                        <i class="fas fa-box"></i> Product Received
                                                    </button>
                                                <?php elseif ($return['status'] === 'product_received'): ?>
                                                    <?php if ($is_return): ?>
                                                        <button class="btn btn-exchange" onclick="processExchange(<?= $return['id'] ?>)" style="background: #20c997; color: #fff;">
                                                            <i class="fas fa-exchange-alt"></i> Exchange Product
                                                        </button>
                                                    <?php else: ?>
                                                        <button class="btn btn-refund" onclick="processRefund(<?= $return['id'] ?>)">
                                                            <i class="fas fa-money-bill-wave"></i> Process Refund
                                                        </button>
                                                    <?php endif; ?>
                                                <?php elseif ($return['status'] === 'product_exchanged' && $is_return): ?>
                                                    <button class="btn btn-ship" onclick="updateStatus(<?= $return['id'] ?>, 'product_shipped', '<?= $request_type ?>')" style="background: #6610f2; color: #fff;">
                                                        <i class="fas fa-shipping-fast"></i> Mark Shipped
                                                    </button>
                                                <?php elseif ($return['status'] === 'product_shipped' && $is_return): ?>
                                                    <button class="btn btn-complete" onclick="updateStatus(<?= $return['id'] ?>, 'completed', '<?= $request_type ?>')" style="background: #28a745; color: #fff;">
                                                        <i class="fas fa-check-circle"></i> Mark Completed
                                                    </button>
                                                <?php endif; ?>
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
    
    <!-- View Return Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div class="modal-header">
                <h2>Return/Refund Details</h2>
            </div>
            <div id="modalBody"></div>
        </div>
    </div>
    
    <!-- Refund Modal -->
    <div id="refundModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeRefundModal()">&times;</span>
            <div class="modal-header">
                <h2>Process Refund</h2>
            </div>
            <form id="refundForm" onsubmit="submitRefund(event)">
                <input type="hidden" id="refund_return_id" name="return_id">
                <div class="form-group">
                    <label>Refund Amount (₹)</label>
                    <input type="number" step="0.01" id="refund_amount" name="refund_amount" required>
                </div>
                <div class="form-group">
                    <label>Admin Notes</label>
                    <textarea id="refund_notes" name="admin_notes" placeholder="Add any notes about this refund..."></textarea>
                </div>
                <button type="submit" class="btn btn-refund" style="width: 100%;">
                    <i class="fas fa-check"></i> Confirm Refund
                </button>
            </form>
        </div>
    </div>
    
    <!-- Exchange Product Modal -->
    <div id="exchangeModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeExchangeModal()">&times;</span>
            <div class="modal-header">
                <h2>Exchange Product</h2>
            </div>
            <form id="exchangeForm" onsubmit="submitExchange(event)">
                <input type="hidden" id="exchange_return_id" name="return_id">
                <div class="form-group">
                    <label>New Product Details</label>
                    <textarea id="exchange_product_details" name="exchange_product_details" placeholder="Enter details about the replacement product (e.g., Product name, SKU, etc.)..." required></textarea>
                </div>
                <div class="form-group">
                    <label>Tracking Number (Optional)</label>
                    <input type="text" id="exchange_tracking" name="tracking_number" placeholder="Enter tracking number if available...">
                </div>
                <div class="form-group">
                    <label>Admin Notes</label>
                    <textarea id="exchange_notes" name="admin_notes" placeholder="Add any notes about this exchange..."></textarea>
                </div>
                <button type="submit" class="btn" style="width: 100%; background: #20c997; color: #fff;">
                    <i class="fas fa-exchange-alt"></i> Confirm Exchange
                </button>
            </form>
        </div>
    </div>
    
    <script>
        function viewReturn(returnId) {
            fetch(`get_return_details.php?id=${returnId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const return_data = data.return;
                        
                        // IMPROVEMENT: Infer refund type if not set but status is refund-related
                        if ((!return_data.request_type || return_data.request_type === 'return') && 
                            ['refund_processing', 'refunded'].includes(return_data.status)) {
                            return_data.request_type = 'refund';
                        }
                        
                        const reasonLabels = {
                            'colour_wrong': 'Colour is wrong',
                            'item_wrong': 'Item is wrong',
                            'defective': 'Defective',
                            'no_good_quality': 'No good quality',
                            'other': 'Other reason'
                        };
                        
                        let html = `
                            <div class="form-group">
                                <label>Order Number:</label>
                                <div>${return_data.order_number}</div>
                            </div>
                            <div class="form-group">
                                <label>Request Type:</label>
                                <div style="font-weight: 700; color: ${return_data.request_type === 'refund' ? '#2fa76b' : '#2fc7b4'}">
                                    ${return_data.request_type ? return_data.request_type.toUpperCase() : 'RETURN'}
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Customer:</label>
                                <div>${return_data.customer_name} (${return_data.customer_email})</div>
                            </div>
                            <div class="form-group">
                                <label>Return Reason:</label>
                                <div>${reasonLabels[return_data.return_reason]}</div>
                            </div>
                            ${return_data.return_reason === 'other' && return_data.other_reason ? `
                            <div class="form-group">
                                <label>Problem Description:</label>
                                <div>${return_data.other_reason}</div>
                            </div>
                            ` : ''}
                            <div class="form-group">
                                <label>Request Date:</label>
                                <div>${new Date(return_data.created_at).toLocaleString()}</div>
                            </div>
                            ${return_data.image ? `
                            <div class="image-preview">
                                <img src="../uploads/returns/${return_data.image}" alt="Return Image">
                            </div>
                            ` : ''}
                            ${return_data.admin_notes ? `
                            <div class="form-group">
                                <label>Admin Notes:</label>
                                <div style="background: #f8f9fa; padding: 15px; border-radius: 6px;">${return_data.admin_notes}</div>
                            </div>
                            ` : ''}
                        `;
                        
                        document.getElementById('modalBody').innerHTML = html;
                        document.getElementById('viewModal').style.display = 'block';
                    }
                })
                .catch(error => {
                    Swal.fire('Error', 'Failed to load return details', 'error');
                });
        }
        
        function closeModal() {
            document.getElementById('viewModal').style.display = 'none';
        }
        
        function updateStatus(returnId, status, requestType = 'return') {
            const statusLabels = {
                'approved': 'approve',
                'rejected': 'reject',
                'product_received': 'mark as product received',
                'product_exchanged': 'mark as product exchanged',
                'product_shipped': 'mark as product shipped',
                'completed': 'mark as completed'
            };
            
            const requestTypeLabel = requestType === 'return' ? 'Return' : 'Refund';
            
            Swal.fire({
                title: `${statusLabels[status] || status.charAt(0).toUpperCase() + status.slice(1)} ${requestTypeLabel} Request`,
                html: `
                    <div style="text-align: left;">
                        <p>Are you sure you want to ${statusLabels[status] || status} this return request?</p>
                        <label style="display: block; margin-top: 15px; font-weight: 600;">Admin Notes (Optional):</label>
                        <textarea id="admin_notes" style="width: 100%; padding: 10px; border: 2px solid #e5e5e5; border-radius: 6px; margin-top: 5px; min-height: 80px;" placeholder="Add any notes about this action..."></textarea>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2fc7b4',
                cancelButtonColor: '#dc3545',
                confirmButtonText: 'Yes, proceed',
                preConfirm: () => {
                    return document.getElementById('admin_notes').value;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('return_id', returnId);
                    formData.append('status', status);
                    if (result.value) {
                        formData.append('admin_notes', result.value);
                    }
                    
                    fetch('update_return_status.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire('Success', data.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Failed to update status', 'error');
                    });
                }
            });
        }
        
        function processRefund(returnId) {
            document.getElementById('refund_return_id').value = returnId;
            document.getElementById('refundModal').style.display = 'block';
        }
        
        function closeRefundModal() {
            document.getElementById('refundModal').style.display = 'none';
            document.getElementById('refundForm').reset();
        }
        
        function submitRefund(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            formData.append('status', 'refunded');
            
            fetch('update_return_status.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire('Success', 'Refund processed successfully', 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Failed to process refund', 'error');
            });
        }
        
        function processExchange(returnId) {
            document.getElementById('exchange_return_id').value = returnId;
            document.getElementById('exchangeModal').style.display = 'block';
        }
        
        function closeExchangeModal() {
            document.getElementById('exchangeModal').style.display = 'none';
            document.getElementById('exchangeForm').reset();
        }
        
        function submitExchange(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            formData.append('status', 'product_exchanged');
            
            fetch('update_return_status.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire('Success', 'Product exchange confirmed successfully', 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Failed to process exchange', 'error');
            });
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const viewModal = document.getElementById('viewModal');
            const refundModal = document.getElementById('refundModal');
            const exchangeModal = document.getElementById('exchangeModal');
            if (event.target == viewModal) {
                viewModal.style.display = 'none';
            }
            if (event.target == refundModal) {
                refundModal.style.display = 'none';
            }
            if (event.target == exchangeModal) {
                exchangeModal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
