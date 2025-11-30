<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.html");
    exit;
}

include 'db_connect.php'; // Database connection

// Delete functionality
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    // Get image to delete file
    $imgQuery = $conn->prepare("SELECT image FROM recommendation_clothes WHERE id=?");
    $imgQuery->bind_param("i", $id);
    $imgQuery->execute();
    $imgResult = $imgQuery->get_result();
    if ($imgResult->num_rows > 0) {
        $row = $imgResult->fetch_assoc();
        if (!empty($row['image']) && file_exists("uploads/".$row['image'])) {
            unlink("uploads/".$row['image']);
        }
    }
    // Delete record
    $stmt = $conn->prepare("DELETE FROM recommendation_clothes WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: manage_recommendation_clothes.php");
    exit;
}

// Fetch all recommendation clothes
$result = $conn->query("SELECT * FROM recommendation_clothes ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Recommendation Clothes</title>
    <style>
    body {
        font-family: Arial;
        background: #f4f4f4;
        margin: 0;
    }

    .sidebar {
        width: 220px;
        background: #2c3e50;
        color: white;
        height: 100vh;
        position: fixed;
    }

    .sidebar .menu {
        list-style: none;
        padding: 0;
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
    }

    table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
    }

    th,
    td {
        padding: 10px;
        border: 1px solid #ccc;
        text-align: center;
    }

    th {
        background: #007bff;
        color: white;
    }

    tr:nth-child(even) {
        background: #f9f9f9;
    }

    a.btn {
        padding: 5px 10px;
        border-radius: 5px;
        text-decoration: none;
        color: white;
    }

    a.edit {
        background: #28a745;
    }

    a.delete {
        background: #dc3545;
    }

    img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 5px;
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
        <h2>Manage Recommendation Clothes</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Product Name</th>
                <th>Skin Tone</th>
                <th>Color</th>
                <th>Body Type</th>
                <th>Gender</th>
                <th>Category</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
            <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                <td><?php echo ucfirst($row['skin_tone']); ?></td>
                <td><?php echo htmlspecialchars($row['color']); ?></td>
                <td><?php echo ucfirst($row['body_type']); ?></td>
                <td><?php echo ucfirst($row['gender']); ?></td>
                <td><?php echo ucfirst($row['category']); ?></td>
                <td>
                    <?php if (!empty($row['image'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Product">
                    <?php endif; ?>
                </td>
                <td>
                    <a href="edit_recommendation.php?id=<?php echo $row['id']; ?>" class="btn edit">Edit</a>
                    <a href="manage_recommendation_clothes.php?delete_id=<?php echo $row['id']; ?>" class="btn delete"
                        onclick="return confirm('Are you sure?');">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
            <?php else: ?>
            <tr>
                <td colspan="9">No recommendation clothes uploaded yet.</td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

</body>

</html>