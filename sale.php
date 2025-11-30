<?php
session_start();
include 'db_connect.php';

// Fetch sale items
$sql = "SELECT * FROM clothes WHERE sale_type='sale'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

// Build product array with updated rating
$products = [];
while ($row = $result->fetch_assoc()) {

    $sales = (int)$row['sales'];

    if ($sales >= 50) $newRating = 5;
    elseif ($sales >= 40) $newRating = 4;
    elseif ($sales >= 20) $newRating = 3;
    else $newRating = 2;

    // Update rating if changed
    if ($row['rating'] != $newRating) {
        $update = $conn->prepare("UPDATE clothes SET rating=? WHERE id=?");
        $update->bind_param("ii", $newRating, $row['id']);
        $update->execute();
        $update->close();
        $row['rating'] = $newRating;
    }

    $products[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>🔥 Sale Collection</title>

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
        padding: 0;
        margin: 0;
        align-items: center;
    }

    nav a {
        color: #FFD700;
        padding: 10px 15px;
        text-decoration: none;
        font-weight: bold;
    }

    nav a:hover {
        background: #333;
        color: #fff;
        border-radius: 5px;
    }

    nav a.active {
        background: #FFD700;
        color: #000;
        border-radius: 5px;
    }

    /* DROPDOWN */
    #userDropdown {
        display: none;
        position: absolute;
        top: 35px;
        left: 0;
        background: #fff;
        border: 1px solid #FFD700;
        border-radius: 5px;
        min-width: 150px;
        z-index: 1000;
    }

    #userDropdown a {
        display: block;
        padding: 8px 15px;
        color: #000;
        text-decoration: none;
    }

    #userDropdown a:hover {
        background: #FFD700;
        color: #000;
    }

    h2 {
        background: #000;
        color: #FFD700;
        padding: 15px;
        text-align: center;
        margin: 0;
    }

    .product-grid {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        padding: 20px;
        justify-content: center;
    }

    .product {
        background: white;
        border: 2px solid #FFD700;
        border-radius: 10px;
        width: 220px;
        text-align: center;
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
        border-radius: 10px 10px 0 0;
    }

    .product button {
        width: 80%;
        padding: 10px;
        margin-bottom: 10px;
        background: #FFD700;
        border: none;
        border-radius: 6px;
        font-weight: bold;
        cursor: pointer;
    }

    .product button:hover {
        background: #000;
        color: #FFD700;
        border: 1px solid #FFD700;
    }
    </style>
</head>

<body>

    <nav>
        <ul>
            <li><a href="main.php">Home</a></li>
            <li><a href="recommendation.php">Recommendation</a></li>
            <li><a href="men.php">Men</a></li>
            <li><a href="women.php">Women</a></li>
            <li><a href="kids.php">Kids</a></li>
            <li><a href="sale.php" class="active">Sale</a></li>
            <li><a href="cart.php">Cart 🛒</a></li>

            <!-- LOGIN / USER -->
            <?php if(isset($_SESSION['user_name'])): ?>
            <li style="position:relative;">
                <a href="#" id="userIcon">👤 <?= htmlspecialchars($_SESSION['user_name']); ?></a>
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


    <h2>🔥 Sale Collection</h2>

    <div class="product-grid">

        <?php foreach($products as $p): ?>
        <?php
                $image = !empty($p['image']) ? "uploads/" . $p['image'] : "uploads/all_sample.jpg";
                $name = htmlspecialchars($p['product_name']);
            ?>
        <div class="product">
            <img src="<?= $image ?>" alt="<?= $name ?>">

            <h3><?= $name ?></h3>

            <p>
                <del>Rs. <?= number_format($p['price'] + 300, 2) ?></del>
                <strong>Rs. <?= number_format($p['price'], 2) ?></strong>
            </p>

            <p>
                <?php for($i=1;$i<=5;$i++): ?>
                <?= ($i <= $p['rating']) ? "⭐" : "☆" ?>
                <?php endfor; ?>
            </p>

            <form method="POST" action="add_to_cart.php">
                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                <input type="hidden" name="table" value="clothes">
                <button type="submit">Add to Cart</button>
            </form>
        </div>

        <?php endforeach; ?>

    </div>


    <script>
    const icon = document.getElementById('userIcon');
    const drop = document.getElementById('userDropdown');

    if (icon) {
        icon.onclick = function(e) {
            e.preventDefault();
            drop.style.display = drop.style.display === 'block' ? 'none' : 'block';
        };

        document.addEventListener("click", function(e) {
            if (!icon.contains(e.target) && !drop.contains(e.target)) {
                drop.style.display = "none";
            }
        });
    }
    </script>

</body>

</html>