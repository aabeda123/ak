<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_key = $_POST['cart_key'] ?? '';

    if ($cart_key && isset($_SESSION['cart'][$cart_key])) {
        unset($_SESSION['cart'][$cart_key]);
        header("Location: cart.php?removed=1");
        exit;
    } else {
        header("Location: cart.php?error=InvalidItem");
        exit;
    }
} else {
    header("Location: cart.php");
    exit;
}
?>