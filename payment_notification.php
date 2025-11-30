<?php
session_start();
require 'db_connect.php'; // Database connection

// --- 1. Database Query and Error Handling ---

// Ensure the connection is valid before querying
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all payments. Assuming 'transaction_id' is the correct column name based on previous INSERTs.
$sql = "SELECT p.id, p.order_id, p.amount, p.transaction_id, p.payment_date, 
               u.firstname, u.lastname, u.email 
        FROM payments p
        JOIN users u ON p.user_id = u.id
        ORDER BY p.payment_date DESC";

$result = $conn->query($sql);

if ($result === false) {
    // Log the error and display a friendly message
    error_log("Payment History DB Error: " . $conn->error);
    $error_message = "Error fetching payment data. Please check the database schema.";
}

// --- 2. HTML Output and Display ---
?>
<!DOCTYPE html>
<html>

<head>
    <title>Payments History</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background: #f4f4f4;
        padding: 0;
        /* Remove body padding as main-content has it */
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

    .status-completed {
        color: #28a745;
        font-weight: bold;
    }

    .status-error {
        color: #dc3545;
        font-weight: bold;
    }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
            <li><a href="view_order.php"><i class="fas fa-shopping-cart"></i> View Orders</a></li>
            <li><a href="upload_clothes.php"><i class="fas fa-cloud-upload-alt"></i> Upload Clothes</a></li>
            <li><a href="manage_clothes.php"><i class="fas fa-tshirt"></i> Manage Clothes</a></li>
            <li><a href="payment_notification.php" style="background-color: #34495e;"><i class="fas fa-credit-card"></i>
                    Payment History</a></li>
            <li><a href="uploadclothes_recommendation.php"><i class="fas fa-star"></i> Upload Clothes for
                    Recommendation</a></li>
            <li><a href="manageclothes_recommendation.php"><i class="fas fa-list-alt"></i> Manage Clothes for
                    Recommendation</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            <li><a href="#"><i class="fas fa-cog"></i> Settings</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h2>Payments History</h2>

        <?php if (isset($error_message)): ?>
        <p class="status-error"><?= htmlspecialchars($error_message) ?></p>
        <?php elseif ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>S.N.</th>
                <th>User Name</th>
                <th>Email</th>
                <th>Order ID</th>
                <th>Amount Paid</th>
                <th>Transaction ID</th>
                <th>Date</th>
            </tr>
            <?php $i=1; while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['order_id'] ?? 'N/A') ?></td>
                <td>NPR <?= number_format($row['amount'] ?? 0, 2) ?></td>
                <td><?= htmlspecialchars($row['transaction_id'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($row['payment_date'] ?? 'N/A') ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
        <?php else: ?>
        <p>No payment records found.</p>
        <?php endif; ?>
    </div>

    <?php 
    // Close the database connection
    if (isset($conn)) {
        $conn->close();
    }
    ?>
</body>

</html>