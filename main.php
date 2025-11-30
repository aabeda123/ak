<?php
session_start(); // start session

$conn = new mysqli("localhost", "root", "", "ak-store");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Posts
$posts_result = $conn->query("SELECT * FROM posts ORDER BY created_at DESC");

// Recommended Products
$sql = "SELECT * FROM clothes ORDER BY sales DESC LIMIT 20";
$result = $conn->query($sql);

$products_to_show = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sales = (int)$row['sales'];
        $rating = ($sales >= 50) ? 5 :
                  (($sales >= 40) ? 4 :
                  (($sales >= 20) ? 3 : 2));

        if ($rating >= 3) {
            $row['rating'] = $rating;
            $products_to_show[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>AK Store - Home</title>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: Arial, sans-serif;
    }

    body {
        background: #FFFFFF;
        padding: 20px;
    }

    /* NAVBAR */
    nav {
        width: 100%;
        background: #000;
        margin-bottom: 20px;
    }

    nav ul {
        display: flex;
        justify-content: center;
        padding: 10px;
        gap: 18px;
        list-style: none;
    }

    nav a {
        color: #FFD700;
        text-decoration: none;
        padding: 10px 14px;
        font-weight: bold;
        transition: 0.3s;
    }

    nav a:hover {
        background: #333;
        color: #FFF;
    }

    /* USER DROPDOWN */
    .user-menu {
        position: relative;
    }

    #dropdown {
        display: none;
        position: absolute;
        top: 35px;
        right: 0;
        background: white;
        color: black;
        border: 1px solid #FFD700;
        border-radius: 5px;
        min-width: 150px;
        z-index: 1000;
    }

    #dropdown a {
        display: block;
        padding: 8px 15px;
        text-decoration: none;
        color: black;
    }

    #dropdown a:hover {
        background: #FFD700;
        color: black;
    }

    /* FULL PAGE BACKGROUND SLIDER */
    .bg-slider {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1;
        overflow: hidden;
    }

    .bg-slider img {
        position: absolute;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0;
        animation: slideShow 18s infinite;
    }

    .bg-slider img:nth-child(1) {
        animation-delay: 0s;
    }

    .bg-slider img:nth-child(2) {
        animation-delay: 6s;
    }

    .bg-slider img:nth-child(3) {
        animation-delay: 12s;
    }

    @keyframes slideShow {
        0% {
            opacity: 0;
        }

        10% {
            opacity: 1;
        }

        30% {
            opacity: 1;
        }

        40% {
            opacity: 0;
        }

        100% {
            opacity: 0;
        }
    }

    /* MAIN CONTAINER */
    .main,
    .column,
    .hero-left {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(3px);
        border: 1px solid #FFD700;
    }

    .main {
        display: flex;
        gap: 20px;
    }

    .hero-left {
        flex: 1;
        padding: 20px;
        border-radius: 15px;
        display: flex;
        gap: 20px;
        align-items: center;
        justify-content: center;
    }

    .hero-left img {
        width: 250px;
        height: 250px;
        object-fit: cover;
        border-radius: 12px;
    }

    .hero-content {
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 15px;
    }

    .quote {
        font-size: 18px;
        color: #FFD700;
        max-width: 300px;
    }

    .auth-row {
        display: flex;
        gap: 15px;
    }

    .auth-row a {
        flex: 1;
        text-align: center;
        padding: 12px 0;
        border-radius: 10px;
        font-weight: bold;
        text-decoration: none;
        font-size: 16px;
        transition: 0.3s;
    }

    .login {
        background: #FFD700;
        color: #000;
    }

    .login:hover {
        background: #FFC300;
    }

    .signup {
        background: #000;
        color: #FFD700;
    }

    .signup:hover {
        background: #333;
    }

    /* RIGHT SIDE */
    .right {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    #modelImage {
        width: 220px;
        height: 220px;
        object-fit: cover;
        border-radius: 12px;
        opacity: 0;
        transition: opacity 1.2s ease;
    }

    .column {
        background: rgba(255, 255, 255, 0.95);
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 0 12px rgba(0, 0, 0, 0.15);
        border: 1px solid #FFD700;
    }

    .post {
        background: #FFF;
        padding: 15px;
        margin-bottom: 15px;
        border-radius: 8px;
        border: 1px solid #FFD700;
        color: #000;
    }

    .products {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .product-box {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #FFF;
        padding: 10px;
        border-radius: 8px;
        border: 1px solid #FFD700;
    }

    .product-box img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 6px;
    }

    .product-box div {
        flex: 1;
        color: #000;
    }

    .product-box button {
        padding: 6px 10px;
        border: none;
        background: #000;
        color: #FFD700;
        border-radius: 5px;
        cursor: pointer;
    }

    .product-box button:hover {
        background: #333;
    }

    h2 {
        margin-bottom: 15px;
        color: #000;
    }
    </style>
</head>

<body>

    <!-- NAVBAR -->
    <nav>
        <ul>
            <li><a href="main.php">Home</a></li>
            <li><a href="recommendation.php">Recommend</a></li>
            <li><a href="men.php">Men</a></li>
            <li><a href="women.php">Women</a></li>
            <li><a href="kids.php">Kids</a></li>
            <li><a href="sale.php">Sale</a></li>
            <li><a href="cart.php">Cart 🛒</a></li>

            <?php if(isset($_SESSION['user_name'])): ?>
            <li class="user-menu">
                <a href="#" id="userIcon">👤 <?php echo $_SESSION['user_name']; ?></a>
                <div id="dropdown">
                    <a href="notification.php">🔔 Notifications</a>
                    <a href="logout.php">Logout</a>
                </div>
            </li>
            <?php else: ?>
            <li><a class="login" href="login.html">Login</a></li>
            <li><a class="signup" href="signup.html">Signup</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- BACKGROUND SLIDER -->
    <div class="bg-slider">
        <img src="uploads/slideimage1.png" alt="">
        <img src="uploads/slideimage2.png" alt="">
        <img src="uploads/slideimage3.png" alt="">
        <img src="uploads/slideimage4.png" alt="">
        <img src="uploads/slideimage5.png" alt="">
        <img src="uploads/slideimage6.png" alt="">
        <img src="uploads/slideimage7.png" alt="">
    </div>

    <!-- MAIN CONTAINER -->
    <div class="main">
        <!-- LEFT HERO -->
        <div class="hero-left">
            <img id="modelImage" src="uploads/model.jpg" alt="Model">
            <div class="hero-content">
                <p id="quoteText" class="quote"></p>
                <div id="authButtons" class="auth-row" style="opacity:0;">
                    <?php if(!isset($_SESSION['user_name'])): ?>
                    <a class="login" href="login.html">Login</a>
                    <a class="signup" href="signup.html">Signup</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- RIGHT POSTS + PRODUCTS -->
        <div class="right">
            <div class="column">
                <h2>📰 Latest Posts</h2>
                <?php
            if ($posts_result->num_rows > 0) {
                while ($row = $posts_result->fetch_assoc()) {
                    echo "<div class='post'>";
                    echo "<h3>".$row['title']."</h3>";
                    echo "<p>".$row['content']."</p>";
                    echo "<small>Posted on: ".$row['created_at']."</small>";
                    echo "</div>";
                }
            } else {
                echo "<p>No posts available.</p>";
            }
            ?>
            </div>

            <div class="column">
                <h2>🔥 Recommended Products</h2>
                <div class="products">
                    <?php foreach ($products_to_show as $row): ?>
                    <div class="product-box">
                        <img src="uploads/<?php echo $row['image']; ?>">
                        <div>
                            <h4><?php echo $row['product_name']; ?></h4>
                            <p>Rs. <?php echo $row['price']; ?></p>
                        </div>
                        <form method="POST" action="add_to_cart.php">
                            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="product_name" value="<?php echo $row['product_name']; ?>">
                            <input type="hidden" name="price" value="<?php echo $row['price']; ?>">
                            <input type="hidden" name="image" value="<?php echo $row['image']; ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button>Add</button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Quote typewriter
    const quote = `"Fashion is the armor to survive the reality of everyday life."`;
    let index = 0;
    setTimeout(() => {
        document.getElementById("modelImage").style.opacity = "1";
        setTimeout(typeQuote, 1000);
    }, 2000);

    function typeQuote() {
        const q = document.getElementById("quoteText");
        q.textContent = quote.slice(0, index);
        index++;
        if (index <= quote.length) {
            setTimeout(typeQuote, 50);
        } else {
            document.getElementById("authButtons").style.opacity = "1";
        }
    }

    // User dropdown
    const userIcon = document.getElementById('userIcon');
    const dropdown = document.getElementById('dropdown');
    if (userIcon) {
        userIcon.addEventListener('click', function(e) {
            e.preventDefault();
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        });
        document.addEventListener('click', function(e) {
            if (!userIcon.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
    }
    </script>

</body>

</html>