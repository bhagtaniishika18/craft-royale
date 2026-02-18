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

// Get unique customers from orders
// Group by email to get unique customers (using order data which is most accurate)
$customers_query = "SELECT 
    o.email,
    o.name,
    o.phone,
    o.user_id,
    COUNT(DISTINCT o.id) as total_orders,
    SUM(CASE WHEN o.payment_status = 'paid' THEN o.total_amount ELSE 0 END) as total_spent,
    MAX(o.created_at) as last_order_date,
    MIN(o.created_at) as first_order_date
FROM orders o
GROUP BY o.email, o.name, o.phone, o.user_id
ORDER BY last_order_date DESC";

$customers_result = mysqli_query($conn, $customers_query);
$customers = [];
while ($customer = mysqli_fetch_assoc($customers_result)) {
    $customers[] = $customer;
}

// Get statistics
$total_customers = count($customers);
$total_revenue = 0;
$total_orders_count = 0;
foreach ($customers as $customer) {
    $total_revenue += $customer['total_spent'];
    $total_orders_count += $customer['total_orders'];
}
$avg_order_value = $total_customers > 0 ? $total_revenue / $total_orders_count : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Customers - Craft Royale Admin</title>
    <?php include 'favicon.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
    <style>
        .customers-container {
            padding: 25px 40px;
        }
        
        .customers-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .customers-stats .stat-card {
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
        
        .customers-stats .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(135deg, #2fc7b4, #2fa76b);
        }
        
        .customers-stats .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        }
        
        .customers-stats .stat-card h3 {
            margin: 0;
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 800;
        }
        
        .customers-stats .stat-card .stat-value {
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
        
        .customers-table {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            border: 1px solid rgba(47, 199, 180, 0.1);
        }
        
        .table-header {
            padding: 25px 30px;
            background: linear-gradient(135deg, #2fc7b4, #2fa76b);
            color: #fff;
        }
        
        .table-header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        
        .customers-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .customers-table th {
            background: linear-gradient(135deg, rgba(47, 199, 180, 0.1), rgba(47, 167, 107, 0.1));
            padding: 18px 20px;
            text-align: left;
            font-weight: 700;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #2fa76b;
            border-bottom: 2px solid rgba(47, 199, 180, 0.2);
        }
        
        .customers-table td {
            padding: 18px 20px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .customers-table tbody tr {
            transition: all 0.2s ease;
        }
        
        .customers-table tbody tr:hover {
            background: rgba(47, 199, 180, 0.05);
        }
        
        .customers-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .customer-name {
            font-weight: 600;
            color: #2b2b2b;
            font-size: 16px;
        }
        
        .customer-email {
            color: #666;
            font-size: 14px;
            margin-top: 4px;
        }
        
        .customer-phone {
            color: #666;
            font-size: 14px;
        }
        
        .orders-count {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            background: linear-gradient(135deg, rgba(47, 199, 180, 0.1), rgba(47, 167, 107, 0.1));
            border-radius: 20px;
            color: #2fa76b;
            font-weight: 600;
            font-size: 14px;
        }
        
        .total-spent {
            font-weight: 700;
            color: #2fa76b;
            font-size: 16px;
        }
        
        .last-order {
            color: #666;
            font-size: 14px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            color: #ddd;
        }
        
        .empty-state p {
            margin: 0;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1>Customers</h1>
                    <p class="welcome-text">View all customers who have placed orders</p>
                </div>
                <div class="header-right">
                    <div class="admin-profile">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo $_SESSION['admin']; ?></span>
                    </div>
                </div>
            </header>
            
            <div class="customers-container">
                <!-- Statistics -->
                <div class="customers-stats">
                    <div class="stat-card">
                        <h3>Total Customers</h3>
                        <div class="stat-value"><?= $total_customers ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Total Orders</h3>
                        <div class="stat-value"><?= $total_orders_count ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Total Revenue</h3>
                        <div class="stat-value">₹<?= number_format($total_revenue, 0) ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Avg Order Value</h3>
                        <div class="stat-value">₹<?= number_format($avg_order_value, 0) ?></div>
                    </div>
                </div>
                
                <!-- Customers Table -->
                <div class="customers-table">
                    <div class="table-header">
                        <h2><i class="fas fa-users"></i> All Customers</h2>
                    </div>
                    
                    <?php if (count($customers) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Customer Name</th>
                                    <th>Contact</th>
                                    <th>Total Orders</th>
                                    <th>Total Spent</th>
                                    <th>Last Order</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customers as $customer): ?>
                                    <tr>
                                        <td>
                                            <div class="customer-name">
                                                <i class="fas fa-user-circle" style="color: #2fc7b4; margin-right: 8px;"></i>
                                                <?= htmlspecialchars($customer['name']) ?>
                                            </div>
                                            <div class="customer-email">
                                                <?= htmlspecialchars($customer['email']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="customer-phone">
                                                <i class="fas fa-phone" style="color: #666; margin-right: 6px;"></i>
                                                <?= htmlspecialchars($customer['phone']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="orders-count">
                                                <i class="fas fa-shopping-bag"></i>
                                                <?= $customer['total_orders'] ?> order<?= $customer['total_orders'] > 1 ? 's' : '' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="total-spent">
                                                ₹<?= number_format($customer['total_spent'], 2) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="last-order">
                                                <i class="fas fa-calendar" style="color: #999; margin-right: 6px;"></i>
                                                <?= date('M d, Y', strtotime($customer['last_order_date'])) ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-users"></i>
                            <p>No customers found</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
