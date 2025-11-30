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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($conn->real_escape_string($_POST['title']));
    $content = trim($conn->real_escape_string($_POST['content']));

    if ($title && $content) {
        $sql = "INSERT INTO posts (title, content) VALUES ('$title', '$content')";
        if ($conn->query($sql)) {
            $message = "Post added!";
        } else {
            $message = "Error adding post: " . $conn->error;
        }
    } else {
        $message = "Please fill in all fields.";
    }
}

// Fetch posts safely
$posts = $conn->query("SELECT * FROM posts ORDER BY id ASC");
if (!$posts) {
    die("Error fetching posts: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Manage Posts</title>
    <style>
    body {
        background-color: #fff8e1;
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
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
        background: #34495e;
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


    .main-content {
        margin-left: 220px;
        padding: 20px;
        width: calc(100% - 220px);
    }

    h2 {
        text-align: center;
        margin: 20px 0;
    }

    .container {
        max-width: 700px;
        margin: 0 auto 60px;
        background: white;
        padding: 30px 40px;
        border: 2px solid #d2b48c;
        border-radius: 10px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        padding: 10px;
        text-align: left;
    }

    th {
        background-color: #f0e68c;
    }

    tr:nth-child(even) {
        background-color: #fffbe6;
    }

    a.delete-btn {
        color: white;
        background: crimson;
        padding: 6px 12px;
        text-decoration: none;
        border-radius: 6px;
    }

    .message {
        text-align: center;
        margin-bottom: 20px;
        color: green;
        font-weight: bold;
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
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="manage_user.php">Manage Users</a></li>
            <li><a href="upload_notification.php">Notification Upload</a></li>
            <li><a href="manage_posts.php">Manage Posts</a></li>
            <li><a href="view_order.php">View Orders</a></li>
            <li><a href="upload_clothes.php">Upload Clothes</a></li>
            <li><a href="manage_clothes.php">Manage Clothes</a></li>
            <li><a href="payment_notification.php"><i class="fa fa-upload"></i>Paymnet Notification</a></li>
            <li><a href="uploadclothes_recommendation.php"><i class="fa fa-upload"></i>Upload Clothes for
                    recommendation</a></li>
            <li><a href="manageclothes_recommendation.php"><i class="fa fa-users"></i> Manage clothes for
                    recommendation</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h2>All Posts</h2>

        <?php if ($message): ?>
        <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="container" style="max-width: 700px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr style="background-color: #f0e68c;">
                    <th style="padding: 10px;">ID</th>
                    <th style="padding: 10px;">Title</th>
                    <th style="padding: 10px;">Content</th>
                    <th style="padding: 10px;">Delete</th>
                </tr>
                <?php while ($row = $posts->fetch_assoc()): ?>
                <tr style="background-color: #fffbe6;">
                    <td style="padding: 10px;"><?php echo $row['id']; ?></td>
                    <td style="padding: 10px;"><?php echo htmlspecialchars($row['title']); ?></td>
                    <td style="padding: 10px;"><?php echo htmlspecialchars($row['content']); ?></td>
                    <td style="padding: 10px;">
                        <a class="delete-btn" href="delete_post.php?id=<?php echo $row['id']; ?>"
                            onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</body>

</html>