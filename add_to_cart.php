<?php
session_start();
include 'db_connect.php';

// Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $product_id = intval($_POST['product_id'] ?? 0);
    $quantity = max(1, intval($_POST['quantity'] ?? 1));
    $table = $_POST['table'] ?? 'clothes';   // Default table
    $redirect = $_POST['redirect_back'] ?? 'cart.php';

    // Validate table
    if (!in_array($table, ['clothes', 'recommendation_clothes'])) {
        header("Location: {$redirect}?error=InvalidTable");
        exit;
    }

    if ($product_id > 0) {
        // Fetch product from correct table
        $stmt = $conn->prepare("SELECT product_name, price, image FROM {$table} WHERE id = ? LIMIT 1");
        if (!$stmt) {
            header("Location: {$redirect}?error=DBPrepareFailed");
            exit;
        }

        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $product = $res->fetch_assoc();
        $stmt->close();

        if ($product) {
            $cart_key = $table . '_' . $product_id; // Unique key

            if (isset($_SESSION['cart'][$cart_key])) {
                $_SESSION['cart'][$cart_key]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$cart_key] = [
                    'product_name' => $product['product_name'],
                    'price' => $product['price'],
                    'quantity' => $quantity,
                    'image' => $product['image'] ?? '',
                    'table' => $table,
                    'id' => $product_id
                ];
            }

            header("Location: {$redirect}?added=1");
            exit;
        } else {
            header("Location: {$redirect}?error=ProductNotFound");
            exit;
        }
    }

    header("Location: {$redirect}?error=InvalidProduct");
    exit;
} else {
    header("Location: index.php");
    exit;
}
?>