<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// If session expired or manually destroyed → redirect
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$userId = $_SESSION['user_id'];

// Database
$conn = new mysqli("localhost", "root", "", "ak-store");
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

/* -------- MARK ALL NOTIFICATIONS AS READ -------- */
$update = $conn->prepare("UPDATE notification SET is_read = 1 WHERE user_id = ?");
$update->bind_param("i", $userId);
$update->execute();

/* -------- FETCH USER NOTIFICATIONS -------- */
$sql = "SELECT notification.*, users.firstname 
        FROM notification 
        JOIN users ON notification.user_id = users.id
        WHERE notification.user_id = ?
        ORDER BY notification.created_at ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Your Notifications</title>
    <style>
    body {
        background: #c2abbeff;
        font-family: Arial, sans-serif;
        padding: 20px;
    }

    nav#nav {
        background: #333;
        padding: 10px 0;
    }

    nav#nav .navbar ul {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        gap: 20px;
    }

    nav#nav .navbar ul li a {
        color: white;
        text-decoration: none;
        padding: 8px 12px;
    }

    nav#nav .navbar ul li a.active,
    nav#nav .navbar ul li a:hover {
        background-color: #555;
        border-radius: 4px;
    }

    h1 {
        text-align: center;
    }

    .notification {
        background: white;
        margin: 15px auto;
        padding: 15px;
        width: 80%;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .time {
        font-size: 0.9em;
        color: gray;
    }
    </style>
</head>

<body>

    <nav id="nav">
        <div class="navbar">
            <ul>
                <li><a href="main.php">Home</a></li>
                <li><a class="nav-link" href="recommendation.php">Recommend</a></li>
                <li><a class="nav-link" href="men.php">Men</a></li>
                <li><a class="nav-link" href="women.php">Women</a></li>
                <li><a class="nav-link" href="kids.php">Kids</a></li>
                <li><a class="nav-link" href="sale.php">Sale</a></li>
                <li><a class="nav-link" href="cart.php">Cart 🛒</a></li>
                <li><a class="nav-link active" href="notification.php">🔔</a></li>
               
            </ul>
        </div>
    </nav>

    <h1>🔔 Your Notifications</h1>

    <?php if ($result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
    <div class="notification">
        <p><strong><?= htmlspecialchars($row['firstname']) ?>:</strong>
            <?= htmlspecialchars($row['message']) ?></p>
        <p class="time"><?= htmlspecialchars($row['created_at']) ?></p>
    </div>
    <?php endwhile; ?>
    <?php else: ?>
    <p style="text-align:center;">No notifications found.</p>
    <?php endif; ?>

    <?php 
$stmt->close();
$conn->close();
?>
</body>

</html>