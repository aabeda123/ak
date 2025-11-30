<?php
session_start();

// Only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.html");
    exit;
}

$conn = new mysqli("localhost", "root", "", "ak-store");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $product_name = $conn->real_escape_string($_POST['product_name']);
    $price = $conn->real_escape_string($_POST['price']);
    $gender = $_POST['gender'];
    $category = $_POST['category'];
    $sale_type = $_POST['sale_type'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {

        // sanitize filename
        $image = preg_replace('/[^A-Za-z0-9\.\-_]/', '', $_FILES['image']['name']);
        $targetDir = __DIR__ . "/uploads/";
        $targetFile = $targetDir . $image;

        // Ensure uploads folder exists
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {

            $stmt = $conn->prepare("
                INSERT INTO clothes (product_name, image, price, gender, category, sale_type)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("ssdsss", $product_name, $image, $price, $gender, $category, $sale_type);

            if ($stmt->execute()) {
                $message = "Clothes uploaded successfully!";
            } else {
                $message = "Database error: " . $conn->error;
                if (file_exists($targetFile)) unlink($targetFile);
            }

        } else {
            $message = "Failed to move uploaded file. Check folder permissions.";
        }

    } else {
        $message = "Please upload a valid image.";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Upload Clothes</title>
    <style>
    body {
        margin: 0;
        font-family: sans-serif;
        background: #f2f2f2;
    }

    .container {
        display: flex;
        height: 100vh;
    }

    .sidebar {
        width: 220px;
        background: #2c3e50;
        color: white;
        height: 100%;
        position: fixed;
        left: 0;
        top: 0;
        bottom: 0;
        overflow-y: auto;
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
        width: calc(100% - 220px);
        height: 100vh;
        overflow-y: auto;
    }

    form {
        background: white;
        padding: 20px 30px;
        border-radius: 8px;
        width: 100%;
        max-width: 500px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        margin: auto;
    }

    h2 {
        text-align: center;
    }

    input,
    select {
        width: 100%;
        margin-bottom: 12px;
        padding: 10px;
        font-size: 16px;
    }

    input[type="submit"] {
        background: #007bff;
        color: white;
        border: none;
        cursor: pointer;
        font-size: 16px;
    }

    input[type="submit"]:hover {
        background: #0056b3;
    }

    p {
        text-align: center;
        font-weight: bold;
    }
    </style>
</head>

<body>
    <div class="container">
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
                <li><a href="payment_notification.php">Payment Notification</a></li>
                <li><a href="uploadclothes_recommendation.php">Upload Clothes for recommendation</a></li>
                <li><a href="manageclothes_recommendation.php">Manage clothes for recommendation</a></li>
                <li><a href="logout.php">Logout</a></li>
                <li><a href="#">Settings</a></li>
            </ul>
        </div>

        <div class="main-content">
            <form method="POST" enctype="multipart/form-data">
                <h2>Upload Clothes</h2>
                <?php if ($message) echo "<p>$message</p>"; ?>

                <input type="text" name="product_name" placeholder="Product Name" required>
                <input type="text" name="price" placeholder="Price" required>

                <select name="gender" required>
                    <option value="">-- Select Gender --</option>
                    <option value="men">Men</option>
                    <option value="women">Women</option>
                    <option value="kids">Kids</option>
                </select>

                <select name="category" required>
                    <option value="">-- Select Category --</option>
                    <option value="formal">Formal</option>
                    <option value="casual">Casual</option>
                    <option value="party">Party</option>
                    <option value="accessories">Accessories</option>
                    <option value="bags">Bags</option>
                    <option value="shoes">Shoes</option>
                </select>

                <select name="sale_type" required>
                    <option value="regular">Regular</option>
                    <option value="sale">Sale</option>
                </select>

                <input type="file" name="image" accept="image/*" required>
                <input type="submit" value="Upload">
            </form>
        </div>
    </div>
</body>

</html>