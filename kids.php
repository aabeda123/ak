<?php
session_start();
include 'db_connect.php';

// Get selected subcategory from URL
$category = isset($_GET['category']) ? $_GET['category'] : 'all';

// Fetch kids products
if ($category === 'all' || $category === '') {
    $sql = "SELECT * FROM clothes WHERE gender='kids'";
    $result = $conn->query($sql);
    if (!$result) { die("Query failed: ".$conn->error); }
} else {
    $sql = "SELECT * FROM clothes WHERE gender='kids' AND category=?";
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
    <title>Kids' Collection</title>
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
        align-items: center;
        flex-wrap: wrap;
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

    /* DROPDOWN */
    .dropdown-content,
    #userDropdown {
        display: none;
        position: absolute;
        top: 35px;
        left: 0;
        background: #fff;
        color: #000;
        border: 1px solid #FFD700;
        border-radius: 5px;
        min-width: 150px;
        z-index: 1000;
    }

    .dropdown-content a,
    #userDropdown a {
        display: block;
        padding: 8px 15px;
        color: #000;
        text-decoration: none;
    }

    .dropdown-content a:hover,
    #userDropdown a:hover {
        background: #FFD700;
        color: #000;
    }

    h2 {
        text-align: center;
        background: #000;
        color: #FFD700;
        padding: 15px;
        margin: 0;
    }

    /* PRODUCT GRID */
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
        width: 220px;
        border-radius: 10px;
        text-align: center;
        padding-bottom: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        transition: 0.3s;
    }

    .product:hover {
        transform: scale(1.05);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
    }

    .product img {
        width: 100%;
        height: 180px;
        object-fit: cover;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }

    .product h3 {
        margin: 10px 0 5px;
    }

    .product p {
        margin: 5px 0;
    }

    .product button {
        width: 80%;
        padding: 10px;
        background: #FFD700;
        border: none;
        border-radius: 6px;
        font-weight: bold;
        cursor: pointer;
    }

    .product button:hover {
        background: #000;
        color: #FFD700;
    }
    </style>
</head>

<body>

    <nav>
        <ul>
            <li><a href="main.php">Home</a></li>
            <li><a href="recommendation.php">Recommendation</a></li>

            <!-- KIDS DROPDOWN -->
            <li class="dropdown" id="kidsDropdown">
                <a href="#" id="kidsBtn">Kids ▾</a>
                <div class="dropdown-content">
                    <?php 
                $subs = ['all','formal','casual','party','accessories','bags','shoes'];
                foreach ($subs as $sub):
                    $active = ($category == $sub) ? "background:#FFD700;color:#000;font-weight:bold;" : "";
                ?>
                    <a href="kids.php?category=<?php echo $sub; ?>" style="<?php echo $active; ?>">
                        <?php echo ucfirst($sub); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </li>

            <li><a href="men.php">Men</a></li>
            <li><a href="women.php">Women</a></li>
            <li><a href="sale.php">Sale</a></li>
            <li><a href="cart.php">Cart 🛒</a></li>

            <!-- USER -->
            <?php if(isset($_SESSION['user_name'])): ?>
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

    <h2>🧒 Kids' Collection</h2>

    <div class="product-grid">
        <?php 
while($row = $result->fetch_assoc()):
    $sales = (int)$row['sales'];

    if ($sales < 20) $rating = 2;
    elseif ($sales < 40) $rating = 3;
    elseif ($sales < 50) $rating = 4;
    else $rating = 5;
?>
        <div class="product">
            <?php if (!empty($row['image'])): ?>
            <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>">
            <?php endif; ?>

            <h3><?php echo htmlspecialchars($row['product_name']); ?></h3>
            <p>Rs. <?php echo number_format($row['price'], 2); ?></p>

            <p>
                <?php for ($i=1; $i<=5; $i++) echo ($i <= $rating) ? "⭐" : "☆"; ?>
            </p>

            <form method="POST" action="add_to_cart.php">
                <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                <input type="hidden" name="table" value="clothes">
                <button type="submit">Add to Cart</button>
            </form>
        </div>
        <?php endwhile; ?>
    </div>

    <script>
    // Kids dropdown
    const kidsBtn = document.getElementById('kidsBtn');
    const kidsDropdown = document.querySelector('#kidsDropdown .dropdown-content');
    kidsBtn.addEventListener('click', (e) => {
        e.preventDefault();
        kidsDropdown.style.display = (kidsDropdown.style.display === 'block') ? 'none' : 'block';
    });
    document.addEventListener('click', (e) => {
        if (!kidsBtn.contains(e.target) && !kidsDropdown.contains(e.target)) {
            kidsDropdown.style.display = 'none';
        }
    });

    // User dropdown
    const userIcon = document.getElementById('userIcon');
    const userDropdown = document.getElementById('userDropdown');
    if (userIcon) {
        userIcon.addEventListener('click', (e) => {
            e.preventDefault();
            userDropdown.style.display = (userDropdown.style.display === 'block') ? 'none' : 'block';
        });
        document.addEventListener('click', (e) => {
            if (!userIcon.contains(e.target) && !userDropdown.contains(e.target)) {
                userDropdown.style.display = 'none';
            }
        });
    }
    </script>

</body>

</html>