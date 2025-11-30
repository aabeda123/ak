<?php
session_start();
// Database connection file
require 'db_connect.php'; 

// 🚨 CRITICAL: Enable detailed error reporting for debugging
// KEEP these lines UNTIL the script runs perfectly without errors.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Set MySQLi to throw exceptions on errors (easier debugging)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); 

// --- Configuration ---
// !!! IMPORTANT: Secure this key in a real environment (e.g., config file outside web root) !!!
$khalti_secret_key = '4b279de177094772b44f1ac7b18bbe23';
$verify_url = 'https://dev.khalti.com/api/v2/epayment/lookup/';
// ---------------------

// Check for the required payment ID (pidx) from Khalti
if (!isset($_GET['pidx'])) {
    echo "<h2>Invalid payment request. Missing pidx.</h2>";
    exit;
}

$pidx = $_GET['pidx'];

// --- 1. Khalti Payment Lookup (Verification) ---
$payload = ['pidx' => $pidx];
$ch = curl_init($verify_url);

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Key $khalti_secret_key",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$response_data = json_decode($response, true);

// --- 2. Verification and Database Operations ---
if ($http_code === 200 && isset($response_data['status']) && $response_data['status'] === 'Completed') {
    
    // Khalti Data
    $order_id = $response_data['purchase_order_id'];
    $amount_paid_khalti = floatval($response_data['total_amount'] ?? 0) / 100.0; 
    $txn_id = $response_data['transaction_id'] ?? null;
    $payment_status_text = 'Paid';
    $payment_gateway = 'Khalti'; // Set gateway for DB insertion
    $payment_date = date('Y-m-d H:i:s');
    $db_success = false; // Flag to track database completion

    // --- A. Fetch Order and User Info ---
    try {
        $sql = "SELECT o.*, u.firstname, u.lastname, u.email
                 FROM orders o
                 JOIN users u ON o.user_id = u.id
                 WHERE o.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    } catch (Exception $e) {
        error_log("DB Fetch Error: " . $e->getMessage());
        echo "<h2>Database Error (Order Fetch). Please contact support.</h2>";
        exit;
    }

    if (!$order) {
        error_log("CRITICAL: Khalti payment succeeded for Order ID {$order_id} but order not found.");
        echo "<h2>Order not found in our system (ID: $order_id). Please contact support.</h2>";
        exit;
    }

    $expected_amount = (float) $order['total_amount'];
    $full_name = $order['firstname'] . " " . $order['lastname'];
    
    // --- B. CRITICAL: Amount Verification (Fraud Check) ---
    if (abs($expected_amount - $amount_paid_khalti) > 0.01) { 
        error_log("SECURITY ALERT: Order #$order_id. Expected: $expected_amount, Paid: $amount_paid_khalti. TXN ID: $txn_id");
        echo "<h2>❌ Payment Amount Mismatch. Please contact support.</h2>";
        exit;
    }

    // --- C, D, E: Critical Database Updates (Protected) ---
    // Check if already processed (Idempotency)
    if (strtolower($order['status']) !== 'paid' && strtolower($order['status']) !== 'processing') {
        try {
            // C. Update orders table 
            $stmt = $conn->prepare("UPDATE orders 
                                     SET status = 'paid',
                                         payment_date = ?,
                                         transaction_id = ?
                                     WHERE id = ?");
            $stmt->bind_param("ssi", $payment_date, $txn_id, $order_id);
            $stmt->execute();
            $stmt->close();
            
            // D. Save Payment Record to 'payments' Table
            // NOW INCLUDES 'payment_gateway'
            $stmt = $conn->prepare("INSERT INTO payments 
                 (user_id, full_name, order_id, pidx, amount, payment_gateway, status, transaction_id, payment_date)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                 
            // Bind parameters: (i)user_id, (s)full_name, (i)order_id, (s)pidx, (d)amount, (s)gateway, (s)status, (s)txn_id, (s)date
            $stmt->bind_param("isisdssss", 
                $order['user_id'], 
                $full_name,
                $order_id, 
                $pidx, 
                $amount_paid_khalti, 
                $payment_gateway, 
                $payment_status_text, 
                $txn_id,
                $payment_date
            );
            $stmt->execute();
            $stmt->close();
            
            // E. Insert Notification
            $msg = "Your payment for Order #$order_id is completed. Transaction ID: $txn_id";
            $stmt = $conn->prepare("INSERT INTO notification (user_id, message, created_at) VALUES (?, ?, NOW())");
            if ($stmt) {
                $stmt->bind_param("is", $order['user_id'], $msg);
                $stmt->execute();
                $stmt->close();
            }
            
            $db_success = true; // All database updates succeeded
            
        } catch (Exception $e) {
            error_log("CRITICAL DB FAILURE on Order #$order_id: " . $e->getMessage());
            $db_success = false; 
        }
    } else {
        error_log("INFO: Order #$order_id already processed or paid. Status: {$order['status']}.");
        $db_success = true; // Still show success if Khalti confirmed and DB status is paid
    }
    
    // 🛒 F. Clear the user's cart automatically if Khalti confirmed the payment
    if (isset($_SESSION['cart'])) {
        unset($_SESSION['cart']); 
    }

    // Variables for display
    $amount_paid = $amount_paid_khalti;
    
    // --- G. Fetch order items for display ---
    $sql_items = "SELECT oi.*, c.product_name, c.image
                  FROM order_items oi
                  JOIN clothes c ON oi.product_id = c.id
                  WHERE oi.order_id = ?";
    $stmt = $conn->prepare($sql_items);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $items = $stmt->get_result();
    $stmt->close();
    
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Payment Successful</title>
    <style>
    body {
        font-family: Arial;
        background: #f2f5f9;
    }

    .content-container {
        max-width: 900px;
        margin: 80px auto;
        background: #fff;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .product {
        display: flex;
        background: #f8f8f8;
        padding: 15px;
        margin-bottom: 10px;
        border-radius: 10px;
    }

    .product img {
        width: 120px;
        height: 120px;
        border-radius: 10px;
        object-fit: cover;
        margin-right: 15px;
    }

    .back-home {
        display: inline-block;
        padding: 10px 20px;
        margin-top: 20px;
        background-color: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-weight: bold;
    }
    </style>
</head>

<body>

    <div class="content-container">

        <h2 style="color:green; text-align:center;">✔ Payment Successful!</h2>
        <p style="text-align: center; font-size: 1.1em; color: #333;">Your order has been confirmed and is now being
            processed. **Your cart has been cleared.**</p>

        <?php if (!$db_success): ?>
        <div
            style="background:#ffdddd; padding:15px; margin-top:15px; border-radius: 8px; border: 1px solid #ff0000; color: #cc0000;">
            <p style="margin: 0;">⚠️ **Warning:** A database error occurred while saving your payment details. **Your
                cart was cleared,** but please contact support with your Transaction ID to ensure the order is processed
                correctly.</p>
        </div>
        <?php endif; ?>

        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

        <p><strong>Name:</strong> <?= htmlspecialchars($full_name) ?></p>
        <p><strong>Order ID:</strong> <?= $order_id ?></p>
        <p><strong>Total Paid:</strong> NPR <?= number_format($amount_paid, 2) ?></p>
        <p><strong>Payment Date:</strong> <?= htmlspecialchars($payment_date) ?></p>
        <p><strong>Transaction ID:</strong> <?= htmlspecialchars($txn_id) ?></p>

        <h3>Purchased Items:</h3>

        <?php while ($item = $items->fetch_assoc()): ?>
        <div class="product">
            <img src="uploads/<?= htmlspecialchars($item['image'] ?? 'placeholder.png') ?>"
                alt="<?= htmlspecialchars($item['product_name'] ?? 'Product') ?>">
            <div>
                <p><strong><?= htmlspecialchars($item['product_name'] ?? 'N/A') ?></strong></p>
                <p>Quantity: <?= $item['quantity'] ?></p>
                <p>Price: NPR <?= number_format($item['price'], 2) ?></p>
            </div>
        </div>
        <?php endwhile; ?>

        <div style="text-align: center;">
            <a href="main.php" class="back-home">Continue Shopping</a>
        </div>

    </div>

</body>

</html>

<?php
} else {
    // Payment Failed or Canceled Logic (Unchanged)
    
    $error_msg = "❌ Payment Failed or Canceled";
    $khalti_msg = "";
    
    if ($http_code !== 200) {
        $khalti_msg = "HTTP Status Code Error: " . htmlspecialchars($http_code) . ". Could not connect to Khalti.";
    } elseif (isset($response_data['status'])) {
        $error_msg = "⚠️ Transaction Status: " . htmlspecialchars($response_data['status']);
        $khalti_msg = "Khalti message: " . htmlspecialchars($response_data['message'] ?? 'No detail provided.');
    } else {
        $khalti_msg = "Unknown error during Khalti lookup.";
    }
    
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Payment Status</title>
        <style>
        body { font-family: Arial;
              background: #f2f5f9; text-align: center; padding: 50px; }
        .error-box { max-width: 600px; margin: 0 auto; background: #f8d7da; padding: 20px; border-radius: 8px; border: 1px solid #f5c6cb; color: #721c24; }
        .debug-info { text-align: left; background: #fff; padding: 15px; margin-top: 15px; border-radius: 5px; overflow-x: auto; }
        .back-home { display: inline-block; padding: 10px 20px; margin-top: 20px; background-color: #dc3545; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="error-box">
            <h2>$error_msg</h2>
            <p>$khalti_msg</p>
            <p>Please try again or contact support with the PIDX: <strong>$pidx</strong>.</p>
            <div class="debug-info">
                <h3>Debug Info:</h3>
                <pre>Khalti Response: <br>{$response}</pre>
            </div>
             <a href="checkout.php" class="back-home">Back to Checkout</a>
        </div>
    </body>
    </html>
    HTML;

}
// Close the connection
if (isset($conn)) {
    $conn->close();
}
?>