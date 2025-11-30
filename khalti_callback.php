<?php
session_start();
require 'db_connect.php'; 

// --- REPLACE THIS WITH YOUR KHALTI TEST/LIVE SECRET KEY ---
$khalti_secret_key = "YOUR_KHALTI_LIVE_SECRET_KEY"; 
$verification_url = "https://a.khalti.com/api/v2/epayment/lookup/";

ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. Check for Khalti parameters
if (empty($_GET['pidx']) || empty($_GET['purchase_order_id']) || empty($_GET['transaction_id'])) {
    $_SESSION['transaction_msg'] = '<script>Swal.fire("Error!", "Payment validation failed: Missing Khalti parameters.", "error");</script>';
    header("Location: checkout.php");
    exit;
}

// Get parameters from Khalti's redirect URL
$pidx = $_GET['pidx'];
$order_id = $_GET['purchase_order_id'];
$txn_id = $_GET['transaction_id'];
$payment_date = date('Y-m-d H:i:s');
$user_id = $_SESSION['user_id'] ?? 0;

// CRITICAL: Retrieve customer name from the session (stored in payment-request.php)
$full_name = $_SESSION['temp_full_name'] ?? 'Guest User'; 

try {
    // --- 2. VERIFY TRANSACTION STATUS WITH KHALTI (Server-to-Server Call) ---
    // (cURL logic remains the same)
    
    $args = http_build_query(['pidx' => $pidx]);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $verification_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $args);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Key " . $khalti_secret_key]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($response, true);

    if ($http_code !== 200 || !isset($data['status']) || $data['status'] !== 'Completed') {
        error_log("Khalti Verification Failed for Order ID {$order_id}: HTTP {$http_code}, Status: " . ($data['status'] ?? 'N/A'));
        $conn->query("UPDATE orders SET status = 'Failed', payment_status = 'Incomplete' WHERE id = '{$order_id}'");
        
        $_SESSION['transaction_msg'] = '<script>Swal.fire("Payment Failed!", "Verification failed. Please check your Khalti wallet.", "error");</script>';
        header("Location: checkout.php");
        exit;
    }

    // --- 3. AMOUNT MATCH CHECK (Security) ---

    $stmt_fetch = $conn->prepare("SELECT total_price FROM orders WHERE id = ?");
    $stmt_fetch->bind_param("i", $order_id);
    $stmt_fetch->execute();
    $result = $stmt_fetch->get_result();
    $order = $result->fetch_assoc();
    $stmt_fetch->close();

    if (!$order) {
        error_log("Order ID {$order_id} not found in DB during callback.");
        $_SESSION['transaction_msg'] = '<script>Swal.fire("Error!", "Order not found in our system.", "error");</script>';
        header("Location: checkout.php");
        exit;
    }

    $expected_amount_paisa = round($order['total_price'] * 100);
    if ($data['amount'] != $expected_amount_paisa) {
        error_log("Amount Mismatch: Order ID {$order_id}. Khalti paid {$data['amount']} paisa, expected {$expected_amount_paisa} paisa.");
        $_SESSION['transaction_msg'] = '<script>Swal.fire("Security Error!", "Amount mismatch detected. Contact support.", "error");</script>';
        header("Location: checkout.php");
        exit;
    }

    // --- 4. ORDER FINALIZATION (Database Updates) ---
    
    // A. Update orders table (Status to Paid)
    $stmt_update_order = $conn->prepare("UPDATE orders 
                                         SET status = 'Paid', payment_status = 'Success', 
                                             payment_date = ?, transaction_id = ?, payment_gateway = 'Khalti'
                                         WHERE id = ?");
            
    $stmt_update_order->bind_param("ssi", $payment_date, $txn_id, $order_id); 
    $stmt_update_order->execute();
    $stmt_update_order->close();


    // B. Insert into payments table (Using retrieved full_name)
    $stmt_insert_payment = $conn->prepare("INSERT INTO payments (
                                            user_id, full_name, order_id, 
                                            pidx, amount, payment_date, 
                                            transaction_id, payment_gateway
                                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    $payment_amount_npr = $data['amount'] / 100;
    $payment_gateway = 'Khalti';

    $stmt_insert_payment->bind_param("isidsdss", 
        $user_id, 
        $full_name, // <-- SAVING THE NAME HERE
        $order_id, 
        $pidx, 
        $payment_amount_npr, 
        $payment_date, 
        $txn_id, 
        $payment_gateway
    );
    $stmt_insert_payment->execute();
    $stmt_insert_payment->close();

    // C. Clear the cart and cleanup session flags
    unset($_SESSION['cart']);
    unset($_SESSION['current_order_id']);
    unset($_SESSION['temp_full_name']); 
    
    // --- 5. SUCCESS REDIRECTION ---

    $_SESSION['transaction_msg'] = '<script>Swal.fire("Payment Successful!", "Your order #'.$order_id.' has been placed and paid.", "success");</script>';
    header("Location: thank_you.php?order_id={$order_id}"); 
    exit;

} catch (Exception $e) {
    error_log("Order Finalization Error for Order ID {$order_id}: " . $e->getMessage());
    $_SESSION['transaction_msg'] = '<script>Swal.fire("System Error!", "An internal error occurred after payment. Contact support.", "error");</script>';
    header("Location: checkout.php");
    exit;
}
?>