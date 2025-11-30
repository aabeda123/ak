<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.html");
  exit;
}

$conn = new mysqli("localhost", "root", "", "ak-store");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$message = "";

// Delete clothing item
if (isset($_GET['delete_id'])) {
  $delete_id = intval($_GET['delete_id']);

  // Get image to delete
  $img = $conn->query("SELECT image FROM clothes WHERE id = $delete_id")->fetch_assoc();
  if ($img && file_exists("uploads/" . $img['image'])) {
    unlink("uploads/" . $img['image']);
  }

  $conn->query("DELETE FROM clothes WHERE id = $delete_id");
  $message = "Clothing item deleted successfully!";
}

// Fetch all clothes
$result = $conn->query("SELECT * FROM clothes ORDER BY id ASC");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Manage Clothes</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        padding: 30px;
        background: #f8f9fa;
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
        text-align: center;
        margin-bottom: 20px;
    }

    .message {
        text-align: center;
        color: green;
        font-weight: bold;
        margin-bottom: 15px;
    }

    table {
        width: 90%;
        margin: 20px auto;
        border-collapse: collapse;
        background: white;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    th,
    td {
        padding: 12px;
        border: 1px solid #ddd;
        text-align: center;
    }

    th {
        background-color: #343a40;
        color: white;
    }

    img {
        width: 80px;
        height: auto;
    }

    .delete-btn {
        background-color: #dc3545;
        color: white;
        padding: 6px 10px;
        text-decoration: none;
        border-radius: 5px;
        font-weight: bold;
    }
    </style>
    <script>
    window.onload = function() {
        const msg = document.getElementById('success-message');
        if (msg) {
            setTimeout(() => {
                msg.style.display = 'none';
            }, 1000); // 1 second
        }
    };
    </script>
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
        <h2>🧺 Manage Clothes</h2>

        <?php if ($message): ?>
        <p class="message" id="success-message"><?= $message ?></p>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Gender</th>
                    <th>Category</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><img src="uploads/<?= htmlspecialchars($row['image']) ?>" alt=""></td>
                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                    <td>Rs. <?= htmlspecialchars($row['price']) ?></td>
                    <td><?= ucfirst($row['gender']) ?></td>
                    <td><?= ucfirst($row['category']) ?></td>
                    <td>
                        <a class="delete-btn" href="?delete_id=<?= $row['id'] ?>"
                            onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php else: ?>
                <tr>
                    <td colspan="8">No clothes found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>



</body>

</html>