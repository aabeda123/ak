<?php
session_start();
require 'db_connect.php'; // Ensure this file establishes the $conn object

// --- 1. Database Query and Error Handling ---

// Fetch all orders
$sql = "SELECT o.*, u.firstname, u.lastname, u.email 
        FROM orders o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.order_date DESC";

$result = $conn->query($sql);

$error_message = null;
if ($result === false) {
    // Log the error and set an admin message for debugging
    error_log("Order History DB Error: " . $conn->error);
    $error_message = "Error fetching order data. Please check the 'orders' table structure for missing columns like 'total_amount' or 'transaction_id'.";
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Orders History</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
    body {
        font-family: Arial, sans-serif;
        background: #f4f4f4;
        padding: 0;
        margin: 0;
    }

    .sidebar {
        width: 220px;
        background: #2c3e50;
        color: white;
        height: 100vh;
        position: fixed;
    }

    .sidebar .profile {
        text-align: center;
        padding: 20px;
    }

    .profile-pic {
        width: 80px;
        border-radius: 50%;
    }

    .online {
        color: limegreen;
    }

    .sidebar .menu {
        list-style: none;
        padding: 0;
    }

    .sidebar .menu li a {
        display: block;
        padding: 12px 20px;
        color: white;
        text-decoration: none;
        transition: background-color 0.3s;
    }

    .sidebar .menu li a:hover {
        background-color: #34495e;
    }

    .main-content {
        margin-left: 220px;
        padding: 20px;
        width: calc(100% - 220px);
        height: 100vh;
        overflow-y: auto;
    }

    h2 {
        text-align: center;
        margin-bottom: 20px;
    }

    table {
        border-collapse: collapse;
        width: 100%;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        background: white;
    }

    th,
    td {
        border: 1px solid #ddd;
        padding: 12px 10px;
        text-align: left;
    }

    th {
        background: #e9ecef;
        font-weight: bold;
    }

    /* --- Status Styling --- */
    .status-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-weight: bold;
        text-transform: capitalize;
    }

    .status-paid {
        background: #d4edda;
        color: #155724;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .status-shipped {
        background: #cce5ff;
        color: #004085;
    }

    .status-cancelled {
        background: #f8d7da;
        color: #721c24;
    }

    /* Add more statuses as needed (e.g., delivered) */
    </style>
</head>

<body>
    <div class="sidebar">
        <div class="profile">
            <img src="assets/images/admin.jpg" alt="Admin" class="profile-pic">
            <h3>AK Store Admin</h3>
            <p class="online"><i class="fas fa-circle"></i> Online</p>
        </div>
        <ul class="menu">
            <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="manage_user.php"><i class="fas fa-users"></i> Manage Users</a></li>
            <li><a href="upload_notification.php"><i class="fas fa-bell"></i> Notification Upload</a></li>
            <li><a href="manage_posts.php"><i class="fas fa-file-alt"></i> Manage Posts</a></li>
            <li><a href="view_order.php" style="background-color: #34495e;"><i class="fas fa-shopping-cart"></i> View
                    Orders</a></li>
            <li><a href="upload_clothes.php"><i class="fas fa-cloud-upload-alt"></i> Upload Clothes</a></li>
            <li><a href="manage_clothes.php"><i class="fas fa-tshirt"></i> Manage Clothes</a></li>
            <li><a href="payment_notification.php"><i class="fas fa-credit-card"></i> Payment History</a></li>
            <li><a href="uploadclothes_recommendation.php"><i class="fas fa-star"></i> Upload Clothes for
                    Recommendation</a></li>
            <li><a href="manageclothes_recommendation.php"><i class="fas fa-list-alt"></i> Manage Clothes for
                    Recommendation</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            <li><a href="#"><i class="fas fa-cog"></i> Settings</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h2>Orders History</h2>

        <?php if ($error_message): ?>
        <p style="color: red; font-weight: bold;"><?= htmlspecialchars($error_message) ?></p>
        <?php elseif ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>S.N.</th>
                <th>Order ID</th>
                <th>User Name</th>
                <th>Email</th>
                <th>Total Amount</th>
                <th>Transaction ID</th>
                <th>Status</th>
                <th>Payment Date</th>
                <th>Order Date</th>
                <th>Action</th>
            </tr>
            <?php $i=1; while($row = $result->fetch_assoc()): 
                // Determine class for styling based on status
                $status_normalized = strtolower(str_replace(' ', '-', $row['status']));
                $status_class = 'status-' . $status_normalized;
            ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td>NPR <?= number_format($row['total_price'] ?? 0, 2) ?></td>
                <td><?= htmlspecialchars($row['transaction_id'] ?? 'N/A') ?></td>
                <td>
                    <span class="status-badge <?= htmlspecialchars($status_class) ?>">
                        <?= htmlspecialchars($row['status']) ?>
                    </span>
                </td>
                <td><?= htmlspecialchars($row['payment_date'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($row['order_date'] ?? 'N/A') ?></td>
                <td><a href="view_order_details.php?order_id=<?= $row['id'] ?>">Details</a></td>
            </tr>
            <?php endwhile; ?>
        </table>
        <?php else: ?>
        <p>No orders found in the database.</p>
        <?php endif; ?>
    </div>

</body>

</html>
<?php
// Close the database connection
if (isset($conn)) {
    $conn->close();
}
?>