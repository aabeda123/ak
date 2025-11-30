<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_key = $_POST['cart_key'] ?? null;
    $action = $_POST['action'] ?? '';

    if ($cart_key !== null && isset($_SESSION['cart'][$cart_key])) {
        switch ($action) {
            case 'increase':
                $_SESSION['cart'][$cart_key]['quantity'] += 1;
                break;

            case 'decrease':
                $_SESSION['cart'][$cart_key]['quantity'] -= 1;
                if ($_SESSION['cart'][$cart_key]['quantity'] <= 0) {
                    unset($_SESSION['cart'][$cart_key]);
                }
                break;

          case 'remove':
    $_SESSION['cart'][$cart_key]['quantity'] -= 1;
    if ($_SESSION['cart'][$cart_key]['quantity'] <= 0) {
        unset($_SESSION['cart'][$cart_key]);
    }
    break;


            default:
                // Invalid action
                break;
        }
    }

    header('Location: cart.php');
    exit;
} else {
    header('Location: cart.php');
    exit;
}