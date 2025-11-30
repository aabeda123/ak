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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = $conn->real_escape_string($_POST['product_name']);
    $price = $conn->real_escape_string($_POST['price']);
    $skin_tone = $_POST['skin_tone'];
    $color = $_POST['color'];
    $body_type = $_POST['body_type'];
    $gender = $_POST['gender'];
    $category = $_POST['category'];

    $image = $_FILES['image']['name'];
    $target = "uploads/" . basename($image);

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        $sql = "INSERT INTO recommendation_clothes 
                (product_name, price, skin_tone, color, body_type, gender, category, image)
                VALUES ('$product_name','$price','$skin_tone','$color','$body_type','$gender','$category','$image')";
        if ($conn->query($sql)) {
            $message = "Recommendation added successfully!";
        } else {
            $message = "Error: " . $conn->error;
        }
    } else {
        $message = "Image upload failed.";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Upload Clothes Recommendation</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background: #f4f4f4;
        padding: 20px;
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
        width: calc(100% - 220px);
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


    h2 {
        text-align: center;
        margin-bottom: 20px;
    }

    form {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    input[type=text],
    input[type=number],
    select,
    input[type=file] {
        padding: 10px;
        font-size: 16px;
        width: 100%;
        border-radius: 6px;
        border: 1px solid #ccc;
    }

    input[type=submit] {
        padding: 12px;
        font-size: 16px;
        background: #007bff;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
    }

    input[type=submit]:hover {
        background: #0056b3;
    }

    p.message {
        text-align: center;
        color: green;
        font-weight: bold;
    }
    </style>
    <script>
    function updateColors() {
        const tone = document.getElementById('skin_tone').value;
        const colorSelect = document.getElementById('color');
        let colors = [];
        if (tone === 'fair') colors = ['White', 'Pastel Pink', 'Light Blue', 'Beige'];
        else if (tone === 'medium') colors = ['Coral', 'Olive', 'Navy', 'Turquoise'];
        else if (tone === 'olive') colors = ['Orange', 'Warm Brown', 'Khaki', 'Mustard'];
        else if (tone === 'dark') colors = ['Bright Yellow', 'Royal Blue', 'Hot Pink', 'White'];
        else colors = [];

        colorSelect.innerHTML = '';
        colors.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c;
            opt.text = c;
            colorSelect.add(opt);
        });
    }
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
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="manage_user.php">Manage Users</a></li>
            <li><a href="upload_notification.php">Notification Upload</a></li>
            <li><a href="manage_posts.php">Manage Posts</a></li>
            <li><a href="view_order.php">View Orders</a></li>
            <li><a href="upload_clothes.php">Upload Clothes</a></li>
            <li><a href="manage_clothes.php">Manage Clothes</a></li>
            <li><a href="payment_notification.php">Payment Notifications</a></li>
            <li><a href="uploadclothes_recommendation.php">Upload Clothes for Recommendation</a></li>
            <li><a href="manageclothes_recommendation.php"><i class="fa fa-users"></i> Manage clothes for
                    recommendation</a></li>
            <li><a href="logout.php">Logout</a></li>
            <li><a href="#">Settings</a></li>
        </ul>
    </div>
    <div class="main-content">
        <h2>Upload Clothes Recommendation</h2>
        <?php if ($message) echo "<p class='message'>$message</p>"; ?>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="product_name" placeholder="Product Name" required>
            <input type="number" name="price" placeholder="Price" step="0.01" required>

            <select name="skin_tone" id="skin_tone" onchange="updateColors()" required>
                <option value="">-- Select Skin Tone --</option>
                <option value="fair">Fair</option>
                <option value="medium">Medium</option>
                <option value="olive">Olive</option>
                <option value="dark">Dark</option>
            </select>

            <select name="color" id="color" required>
                <option value="">-- Select Color --</option>
            </select>

            <select name="body_type" required>
                <option value="">-- Select Body Type --</option>
                <option value="rectangle">Rectangle</option>
                <option value="inverted_triangle">Inverted Triangle</option>
                <option value="hourglass">Hourglass</option>
                <option value="pear">Pear</option>
                <option value="apple">Apple</option>
            </select>

            <select name="gender" required>
                <option value="">-- Select Gender --</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="kids">Kids</option>
            </select>

            <select name="category" required>
                <option value="">-- Select Category --</option>
                <option value="top">Top</option>
                <option value="bottom">Bottom</option>
                <option value="footwear">Footwear</option>
                <option value="all">All</option>
            </select>

            <input type="file" name="image" accept="image/*" required>

            <input type="submit" value="Upload Recommendation">
        </form>
    </div>
</body>

</html>