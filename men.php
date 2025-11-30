<?php
session_start();
include 'db_connect.php';

// Get selected subcategory from URL
$category = isset($_GET['category']) ? $_GET['category'] : 'all';

// Prepare SQL for clothes
if ($category === 'all' || $category === '') {
    $sql1 = "SELECT *, 'clothes' AS table_name FROM clothes WHERE gender='men'";
} else {
    $sql1 = "SELECT *, 'clothes' AS table_name FROM clothes WHERE gender='men' AND category=?";
}
$stmt1 = $conn->prepare($sql1);
if ($category !== 'all' && $category !== '') {
    $stmt1->bind_param("s", $category);
}
$stmt1->execute();
$result1 = $stmt1->get_result();

// Prepare SQL for recommendation_clothes
if ($category === 'all' || $category === '') {
    $sql2 = "SELECT *, 'recommendation' AS table_name FROM recommendation_clothes WHERE gender='men'";
} else {
    $sql2 = "SELECT *, 'recommendation' AS table_name FROM recommendation_clothes WHERE gender='men' AND category=?";
}
$stmt2 = $conn->prepare($sql2);
if ($category !== 'all' && $category !== '') {
    $stmt2->bind_param("s", $category);
}
$stmt2->execute();
$result2 = $stmt2->get_result();

// Merge results
$products = [];
while ($row = $result1->fetch_assoc()) { $products[] = $row; }
while ($row = $result2->fetch_assoc()) { $products[] = $row; }

$stmt1->close();
$stmt2->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Men's Collection</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background: #fff;
        margin: 0;
        color: #000;
    }

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

    h2 {
        background: #000;
        color: #FFD700;
        padding: 15px;
        margin: 0;
        text-align: center;
    }

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
    </style>
</head>

<body>

    <nav>
        <ul>
            <li><a href="main.php">Home</a></li>
            <li><a href="recommendation.php">Recommendation</a></li>

            <li class="dropdown" id="menDropdown">
                <a href="#" id="menBtn">Men ▾</a>
                <div class="dropdown-content">
                    <?php 
                $subs = ['all','formal','casual','party','accessories','bags','shoes'];
                foreach($subs as $sub):
                    $style = ($category==$sub) ? 'background:#FFD700;color:#000;font-weight:bold;' : '';
                ?>
                    <a href="men.php?category=<?=$sub?>" style="<?=$style?>"><?=ucfirst($sub)?></a>
                    <?php endforeach; ?>
                </div>
            </li>

            <li><a href="women.php">Women</a></li>
            <li><a href="kids.php">Kids</a></li>
            <li><a href="cart.php">Cart 🛒</a></li>
            <li><a href="sale.php">Sale</a></li>

            <!-- USER LOGIN STATUS -->
            <?php if(isset($_SESSION['user_name'])): ?>
            <li style="position:relative;">
                <a href="#" id="userIcon">👤 <?=htmlspecialchars($_SESSION['user_name'])?> ▾</a>
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


    <h2>👕 Men's Collection</h2>

    <div class="product-grid">
        <?php foreach($products as $row):
    $product_name = $row['product_name'] ?? $row['name'] ?? 'No Name';
    $image = !empty($row['image']) ? 'uploads/'.$row['image'] : 'uploads/all_sample.jpg';
    $price = $row['price'] ?? 0;
    $sales = isset($row['sales']) ? (int)$row['sales'] : 0;

$sales = isset($row['sales']) ? (int)$row['sales'] : 0;

// Determine star rating based on fixed thresholds
if ($sales < 20) {
    $rating = 2;
} elseif ($sales < 30) {
    $rating = 3;
} elseif ($sales < 40) {
    $rating = 4;
} else {
    $rating = 5;
}
?>
        <div class="product">
            <img src="<?=htmlspecialchars($image)?>" alt="<?=htmlspecialchars($product_name)?>">
            <h3><?=htmlspecialchars($product_name)?></h3>
            <p>Rs. <?=number_format($price,2)?></p>
            <p>
                <?php for($i=1;$i<=5;$i++): ?>
                <?=($i <= $rating) ? "⭐" : "☆"?>
                <?php endfor; ?>
            </p>

            <form method="POST" action="add_to_cart.php">
                <input type="hidden" name="product_id" value="<?=$row['id']?>">
                <input type="hidden" name="table" value="<?=$row['table_name']?>">
                <button type="submit">Add to Cart</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>

    <script>
    const menBtn = document.getElementById('menBtn');
    const menDropdown = document.querySelector('#menDropdown .dropdown-content');

    if (menBtn) {
        menBtn.addEventListener('click', e => {
            e.preventDefault();
            menDropdown.style.display = menDropdown.style.display === 'block' ? 'none' : 'block';
        });
        document.addEventListener('click', e => {
            if (!menBtn.contains(e.target) && !menDropdown.contains(e.target)) {
                menDropdown.style.display = 'none';
            }
        });
    }

    // USER ICON DROPDOWN
    const userIcon = document.getElementById('userIcon');
    const userDropdown = document.getElementById('userDropdown');

    if (userIcon) {
        userIcon.addEventListener('click', e => {
            e.preventDefault();
            userDropdown.style.display =
                userDropdown.style.display === 'block' ? 'none' : 'block';
        });
        document.addEventListener('click', e => {
            if (!userIcon.contains(e.target) && !userDropdown.contains(e.target)) {
                userDropdown.style.display = 'none';
            }
        });
    }
    </script>

</body>

</html>