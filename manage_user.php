<?php
session_start();

// ✅ Allow only admins
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.html");
  exit;
}

// ✅ Connect to the correct database
$conn = new mysqli("localhost", "root", "", "ak-store");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// ✅ Fetch users from person table
$result = $conn->query("SELECT * FROM users");
if (!$result) {
  die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Manage Users</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        text-align: center;
        margin: 40px;
        background-color: #f9f9f9;
    }

    .sidebar {
        width: 220px;
        background: #2c3e50;
        color: white;
        height: 100vh;
        position: fixed;
    }

    .main-content {
        margin-left: 220px;
        /* keep sidebar width */
        padding: 20px;
        width: calc(100% - 220px);
        height: 100vh;
        /* make full viewport height */
        overflow-y: auto;
        /* enable vertical scrolling */
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
    }

    .sidebar .menu li a:hover {
        background-color: #34495e;
    }

    .main-content {
        margin-left: 220px;
        padding: 20px;
        width: 100%;
    }

    h2 {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 30px;
    }

    table {
        margin: 0 auto;
        border-collapse: collapse;
        width: 70%;
        font-size: 1.2rem;
        font-weight: 600;
        background: white;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    th,
    td {
        border: 1px solid #ddd;
        padding: 12px 20px;
    }

    th {
        background-color: #f0e68c;
        /* light khaki */
        font-weight: 700;
    }

    tr:nth-child(even) {
        background-color: #f5f5f5;
    }

    a.delete-link {
        color: #555;
        font-weight: 600;
        text-decoration: none;
        padding: 6px 14px;
        border: 2px solid #555;
        border-radius: 4px;
        transition: all 0.3s ease;
        display: inline-block;
    }

    a.delete-link:hover {
        background-color: purple;
        color: white;
        border-color: purple;
    }
    </style>
</head>

<body>
    <div class="sidebar">
        <div class="profile">
            <img src="assets/images/admin.jpg" alt="Admin" class="profile-pic">
            <h3>AK store</h3>
            <p class="online">● Online</p>
        </div>
        <ul class="menu">
            <li><a href="index.php"><i class="fa fa-home"></i> Dashboard</a></li>
            <li><a href="manage_user.php"><i class="fa fa-users"></i> Manage Users</a></li>
            <li><a href="upload_notification.php"><i class="fa fa-file-alt"></i> notification upload</a></li>
            <li><a href="manage_posts.php"><i class="fa fa-file-alt"></i> Manage Posts</a></li>
            <li><a href="view_order.php"><i class="fa fa-shopping-cart"></i> View Orders</a></li>
            <li><a href="upload_clothes.php"><i class="fa fa-upload"></i> Upload Clothes</a></li>
            <li><a href="manage_clothes.php"><i class="fa fa-upload"></i>Manage Clothes</a></li>
            <li><a href="payment_notification.php"><i class="fa fa-upload"></i>Paymnet Notification</a></li>
            <li><a href="uploadclothes_recommendation.php"><i class="fa fa-upload"></i>Upload Clothes for
                    recommendation</a></li>
            <li><a href="manageclothes_recommendation.php"><i class="fa fa-users"></i> Manage clothes for
                    recommendation</a></li>
            <li><a href="logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a></li>
            <li><a href="#"><i class="fa fa-cog"></i> Settings</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h2>All Users</h2>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['role']); ?></td>
                    <td>
                        <a class="delete-link" href="delete_user.php?id=<?php echo urlencode($row['id']); ?>"
                            onclick="return confirm('Are you sure you want to delete this user?');">
                            Delete
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>




</body>

</html>