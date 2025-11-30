<?php
session_start();
// Get the pidx from the URL
$pidx = $_GET['pidx'] ?? null;

// !!! SECURITY FIX 1: DO NOT HARDCODE YOUR LIVE SECRET KEY IN PUBLIC CODE !!!
// Store this in a secure environment variable or a config file outside the web root.
// For this example, we use a variable. REPLACE 'YOUR_KHALTI_LIVE_SECRET_KEY'
$secretKey = 'YOUR_KHALTI_LIVE_SECRET_KEY';

if ($pidx) {
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://a.khalti.com/api/v2/epayment/lookup/',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode(['pidx' => $pidx]),
        CURLOPT_HTTPHEADER => array(
            // SECURITY FIX 2: Using the key from a variable
            'Authorization: key ' . $secretKey, 
            'Content-Type: application/json',
        ),
    ));

    $response = curl_exec($curl);
    // Get the HTTP status code to check for API errors
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    // Check if a valid response was received and the HTTP code is 200 (Success)
    if ($response && $httpCode === 200) {
        $responseArray = json_decode($response, true);
        
        // FIX 3: Store only the message type and text, not the script
        $messageData = [];
        $redirectPage = 'checkout.php'; // Default failed page

        switch ($responseArray['status']) {
            case 'Completed':
                // **WRITE YOUR DATABASE UPDATE LOGIC HERE**
                
                $messageData['icon'] = 'success';
                $messageData['title'] = 'Transaction successful.';
                $redirectPage = 'message.php';
                break;
                
            case 'Expired':
            case 'User canceled':
                $messageData['icon'] = 'error';
                $messageData['title'] = 'Transaction failed or canceled.';
                break;
                
            default:
                $messageData['icon'] = 'warning';
                $messageData['title'] = 'Transaction status unknown.';
                break;
        }

        // Store the message data in a reliable session key
        $_SESSION['transaction_data'] = $messageData;
        header("Location: " . $redirectPage);
        exit();
        
    } else {
        // Handle API communication errors
        $_SESSION['transaction_data'] = [
            'icon' => 'error',
            'title' => 'Payment verification failed (API Error).'
        ];
        header("Location: checkout.php");
        exit();
    }
} else {
    // Handle missing pidx
    $_SESSION['transaction_data'] = [
        'icon' => 'warning',
        'title' => 'Invalid Request. Payment ID (pidx) is missing.'
    ];
    header("Location: checkout.php");
    exit();
}
?>