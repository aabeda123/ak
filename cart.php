<?php
session_start();
// Include your DB connection (used for product details, though not explicitly queried here, it's good practice)
include 'db_connect.php'; 

// Fetch cart data safely, initializing to an empty array if not set.
$cart = $_SESSION['cart'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Your Cart</title>
    <style>
    /* (Your existing CSS — kept below) */
    body {
        font-family: Arial, sans-serif;
        background: #FFFFFF;
        margin: 0;
        padding: 0;
        color: #000;
    }

    nav {
        width: 100%;
        background: #000;
        margin-bottom: 20px;
    }

    nav ul {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 10px;
        gap: 18px;
        list-style: none;
        flex-wrap: wrap;
    }

    nav a {
        color: #FFD700;
        text-decoration: none;
        padding: 10px 14px;
        font-weight: bold;
        transition: 0.3s;
    }

    nav a:hover,
    nav a.active {
        background: #333;
        color: #FFF;
        border-radius: 5px;
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
        /* Positioning fix for centered nav */
        left: auto;
    }

    .user-menu {
        position: relative;
        /* Needed for absolute positioning of dropdown */
    }

    /* table and buttons */
    h2 {
        text-align: center;
        margin: 20px 0;
        color: #FFD700;
    }

    table {
        width: 95%;
        margin: auto;
        border-collapse: collapse;
        background: #fff;
        border: 1px solid #FFD700;
        border-radius: 5px;
        overflow: hidden;
    }

    th,
    td {
        padding: 12px;
        border: 1px solid #FFD700;
        text-align: center;
        vertical-align: middle;
    }

    th {
        background: #f0f0f0;
    }

    .cart-product {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .cart-product img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 5px;
        border: 1px solid #FFD700;
    }

    /* Combined form styles for quantity buttons */
    .qty-controls {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
    }

    .qty-btn {
        background-color: #FFD700;
        border: none;
        color: #000;
        font-weight: bold;
        padding: 4px 8px;
        cursor: pointer;
        border-radius: 4px;
        transition: background-color 0.2s;
        line-height: 1;
        /* Fix vertical alignment of text/emoji */
    }

    .qty-btn:hover {
        background-color: #e6c800;
    }

    .remove-btn {
        padding: 6px 10px;
        background-color: #c00;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .remove-btn:hover {
        background-color: #a00;
    }


    .order-btn-container {
        text-align: center;
        margin: 20px 0;
    }

    .order-btn {
        padding: 12px 30px;
        font-size: 16px;
        background-color: #FFD700;
        color: #000;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: bold;
        transition: background-color 0.2s;
    }

    .order-btn:hover {
        background-color: #e6c800;
    }

    .notification {
        padding: 10px;
        border-radius: 5px;
        margin: 10px auto;
        width: 50%;
        text-align: center;
        font-weight: bold;
        border: 1px solid;
    }

    .notification.success {
        background-color: #d4edda;
        color: #155724;
        border-color: #c3e6cb;
    }

    .notification.error {
        background-color: #f8d7da;
        color: #721c24;
        border-color: #f5c6cb;
    }
    </style>
</head>

<body>

    <nav>
        <ul>
            <li><a href="main.php">Home</a></li>
            <li><a href="recommendation.php">Recommend</a></li>
            <li><a href="main.php?category=men">Men</a></li>
            <li><a href="women.php">Women</a></li>
            <li><a href="kids.php">Kids</a></li>
            <li><a href="sale.php">Sale</a></li>

            <li><a class="active" href="cart.php">Cart 🛒</a></li>

            <?php if(isset($_SESSION['user_name'])): ?>
            <li class="user-menu">
                <a href="#" id="userIcon">👤 <?php echo htmlspecialchars($_SESSION['user_name']); ?></a>
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

    <h2>Your Cart 🛒</h2>

    <?php if (!empty($_GET)): ?>
    <?php if (isset($_GET['added'])): ?>
    <div class="notification success">Item added to cart successfully!</div>
    <?php elseif (isset($_GET['removed'])): ?>
    <div class="notification error">Item removed from cart.</div>
    <?php elseif (isset($_GET['error'])): ?>
    <div class="notification error"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>
    <?php endif; ?>

    <?php if (!empty($cart)): ?>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Price (Rs)</th>
                <th>Quantity</th>
                <th>Total</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $grand_total = 0;
            // Iterate over the cart items
            foreach ($cart as $cart_key => $item):
                // 🛡️ Robustness: Safely access and cast values
                $product_name = htmlspecialchars($item['product_name'] ?? 'Unknown');
                $price = floatval($item['price'] ?? 0);
                $quantity = intval($item['quantity'] ?? 1);
                
                // Ensure quantity is positive
                if ($quantity < 1) {
                    $quantity = 1;
                }
                
                $total = $price * $quantity;
                $grand_total += $total;
            ?>
            <tr>
                <td>
                    <div class="cart-product">
                        <?php if (!empty($item['image'])): ?>
                        <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>"
                            alt="<?php echo $product_name; ?>">
                        <?php endif; ?>
                        <span><?php echo $product_name; ?></span>
                    </div>
                </td>
                <td><?php echo number_format($price, 2); ?></td>
                <td>
                    <div class="qty-controls">
                        <form action="update_cart.php" method="POST" style="display:inline;">
                            <input type="hidden" name="cart_key" value="<?php echo htmlspecialchars($cart_key); ?>">
                            <input type="hidden" name="action" value="decrease">
                            <button type="submit" class="qty-btn" title="Decrease Quantity">➖</button>
                        </form>

                        <span><?php echo $quantity; ?></span>

                        <form action="update_cart.php" method="POST" style="display:inline;">
                            <input type="hidden" name="cart_key" value="<?php echo htmlspecialchars($cart_key); ?>">
                            <input type="hidden" name="action" value="increase">
                            <button type="submit" class="qty-btn" title="Increase Quantity">➕</button>
                        </form>
                    </div>
                </td>
                <td><?php echo number_format($total, 2); ?></td>
                <td>
                    <form method="POST" action="update_cart.php"
                        onsubmit="return confirm('Are you sure you want to remove <?php echo $product_name; ?> from the cart?');">
                        <input type="hidden" name="cart_key" value="<?php echo htmlspecialchars($cart_key); ?>">
                        <input type="hidden" name="action" value="remove">
                        <button type="submit" class="remove-btn">Remove</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>

        <tfoot>
            <tr>
                <th colspan="3" style="text-align: right; font-size: 1.1em;">Grand Total</th>
                <th colspan="2" style="font-size: 1.1em;"><?php echo number_format($grand_total, 2); ?></th>
            </tr>
        </tfoot>
    </table>

    <div class="order-btn-container">
        <form method="POST" action="checkout.php">
            <input type="hidden" name="grand_total" value="<?php echo htmlspecialchars($grand_total); ?>">

            <button type="submit" class="order-btn" name="process_order">
                Checkout — Pay NPR <?php echo number_format($grand_total,2); ?>
            </button>
        </form>

        <form method="POST" action="update_cart.php" style="margin-top: 10px;">
            <input type="hidden" name="action" value="clear">
            <button type="submit" class="remove-btn"
                onclick="return confirm('Are you sure you want to empty your entire cart?');">Clear Cart</button>
        </form>
    </div>

    <?php else: ?>
    <p style="text-align:center; color: #FFD700; font-size: 1.2em; padding: 50px;">Your cart is empty. Start shopping
        now!</p>
    <?php endif; ?>

    <script>
    // User dropdown JS remains the same, but ensure 'user-menu' class is applied to the parent <li>
    const userIcon = document.getElementById('userIcon');
    const dropdown = document.getElementById('dropdown');

    // Add logic to fix the dropdown position based on the centered layout
    const userMenuLi = document.querySelector('.user-menu');
    if (userMenuLi) {
        // Calculate dropdown position relative to the user-menu <li> element
        // This ensures the dropdown aligns to the right edge of the user icon in a centered menu
        const liRect = userMenuLi.getBoundingClientRect();
        dropdown.style.left = (liRect.width / 2) - (150 / 2) + 'px'; // Center it under the icon (approx)
        dropdown.style.right = 'auto'; // Disable previous right: 0;
    }

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