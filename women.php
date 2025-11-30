<?php
session_start();
include 'db_connect.php';

// Get category from URL
$category = isset($_GET['category']) ? $_GET['category'] : 'all';

// Fetch women products
if ($category == 'all' || $category == '') {
    $sql = "SELECT * FROM clothes WHERE gender='women'";
    $result = $conn->query($sql);
    if (!$result) {
        die("Query failed: " . $conn->error);
    }
} else {
    $sql = "SELECT * FROM clothes WHERE gender='women' AND category=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Women's Collection</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background: #fff;
        margin: 0;
        color: #000;
    }

    /* NAVBAR */
    nav {
        background: #000;
        padding: 10px 0;
        position: sticky;
        top: 0;
        z-index: 1000;
    }

    nav ul {
        list-style: none;
        display: flex;
        justify-content: center;
        gap: 15px;
        margin: 0;
        padding: 0;
        flex-wrap: wrap;
        align-items: center;
    }

    nav ul li {
        position: relative;
    }

    nav a {
        color: #FFD700;
        text-decoration: none;
        padding: 10px 15px;
        font-weight: bold;
        transition: 0.3s;
    }

    nav a:hover {
        background: #333;
        color: #fff;
        border-radius: 5px;
    }

    /* Dropdown menus */
    .dropdown-content,
    #userDropdown {
        display: none;
        position: absolute;
        top: 35px;
        background: #fff;
        color: #000;
        border: 1px solid #FFD700;
        border-radius: 5px;
        z-index: 1000;
        min-width: 150px;
    }

    .dropdown-content a,
    #userDropdown a {
        display: block;
        padding: 8px 15px;
        text-decoration: none;
        color: #000;
    }

    .dropdown-content a:hover,
    #userDropdown a:hover {
        background: #FFD700;
        color: black;
    }

    /* Page title */
    h2 {
        background: #000;
        color: #FFD700;
        padding: 15px;
        margin: 0;
        text-align: center;
    }

    /* Products grid */
    .product-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        padding: 20px;
        justify-content: center;
        background: #f9f9f9;
    }

    .product {
        background: #fff;
        border: 2px solid #FFD700;
        border-radius: 10px;
        width: 220px;
        text-align: center;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .product:hover {
        transform: scale(1.05);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
    }

    .product img {
        width: 100%;
        height: 180px;
        object-fit: cover;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }

    .product h3 {
        color: #000;
        margin: 10px 0 5px 0;
    }

    .product p {
        margin: 5px 0;
    }

    .product button {
        width: 80%;
        padding: 10px;
        margin-bottom: 10px;
        background: #FFD700;
        color: #000;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: bold;
        transition: 0.3s;
    }

    .product button:hover {
        background: #000;
        color: #FFD700;
        border: 1px solid #FFD700;
    }

    /* User dropdown */
    #userDropdown {
        right: 0;
    }
    </style>
</head>

<body>

    <nav>
        <ul>
            <li><a href="main.php">Home</a></li>
            <li><a href="recommendation.php">Recommendation</a></li>

            <!-- WOMEN DROPDOWN -->
            <li class="dropdown" style="position:relative;">
                <a href="#" id="womenBtn">Women ▾</a>
                <div class="dropdown-content" id="womenCategories">
                    <?php 
                $subcategories = ['all','formal','casual','party','accessories','bags','shoes'];
                foreach($subcategories as $sub): 
                    $activeStyle = ($category == $sub) ? 'background:#FFD700;color:#000;font-weight:bold;' : '';
                ?>
                    <a href="women.php?category=<?php echo $sub; ?>" style="<?php echo $activeStyle; ?>">
                        <?php echo ucfirst($sub); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </li>

            <li><a href="men.php">Men</a></li>
            <li><a href="kids.php">Kids</a></li>
            <li><a href="cart.php">Cart 🛒</a></li>
            <li><a href="sale.php">Sale</a></li>

            <!-- USER LOGIN / DROPDOWN -->
            <?php if (isset($_SESSION['user_name'])): ?>
            <li style="position:relative;">
                <a href="#" id="userIcon">👤 <?php echo htmlspecialchars($_SESSION['user_name']); ?></a>
                <div id="userDropdown">
                    <a href="notification.php">🔔 Notifications</a>
                    <a href="logout.php">Logout</a>
                </div>
            </li>
            <?php else: ?>
            <li><a href="login.html">Login</a></li>
            <li><a href="signup.html">Signup</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <h2>👗 Women's Collection</h2>

    <div class="product-grid">
        <?php while ($row = $result->fetch_assoc()):
        $sales = isset($row['sales']) ? (int)$row['sales'] : 0;
        if ($sales < 20) $rating = 2;
        elseif ($sales < 40) $rating = 3;
        elseif ($sales < 50) $rating = 4;
        else $rating = 5;
    ?>
        <div class="product">
            <?php if(!empty($row['image'])): ?>
            <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Product Image">
            <?php endif; ?>
            <h3><?php echo htmlspecialchars($row['product_name']); ?></h3>
            <p>Rs. <?php echo number_format($row['price'], 2); ?></p>
            <p>
                <?php for ($i=1; $i<=5; $i++): ?>
                <?php echo ($i <= $rating) ? "⭐" : "☆"; ?>
                <?php endfor; ?>
            </p>
            <form method="POST" action="add_to_cart.php">
                <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                <button type="submit">Add to Cart</button>
            </form>
        </div>
        <?php endwhile; ?>
    </div>

    <script>
    const userIcon = document.getElementById('userIcon');
    const userDropdown = document.getElementById('userDropdown');
    if (userIcon) {
        userIcon.addEventListener('click', function(e) {
            e.preventDefault();
            userDropdown.style.display = (userDropdown.style.display === 'block') ? 'none' : 'block';
        });
        document.addEventListener('click', function(e) {
            if (!userIcon.contains(e.target) && !userDropdown.contains(e.target)) {
                userDropdown.style.display = 'none';
            }
        });
    }

    const womenBtn = document.getElementById('womenBtn');
    const womenCategories = document.getElementById('womenCategories');
    if (womenBtn) {
        womenBtn.addEventListener('click', function(e) {
            e.preventDefault();
            womenCategories.style.display = (womenCategories.style.display === 'block') ? 'none' : 'block';
        });
        document.addEventListener('click', function(e) {
            if (!womenBtn.contains(e.target) && !womenCategories.contains(e.target)) {
                womenCategories.style.display = 'none';
            }
        });
    }
    </script>

</body>

</html>