<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Allow only POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method. Please submit the login form.");
}

// DB Connection
$conn = new mysqli("localhost", "root", "", "ak-store");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Validate input
if (empty($_POST['email']) || empty($_POST['password'])) {
    die("Email or password missing.");
}

$email = trim($_POST['email']);
$password = $_POST['password'];

// SQL to fetch user
$sql = "SELECT id, firstname, lastname, email, phone, password, role 
        FROM users 
        WHERE email = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

// User not found?
if ($stmt->num_rows === 0) {
    echo "User not found. Please check your email.";
    $stmt->close();
    $conn->close();
    exit;
}

// Bind user data
$stmt->bind_result($id, $firstname, $lastname, $email_db, $phone, $hashed_password, $role);
$stmt->fetch();

// Verify password
if (password_verify($password, $hashed_password)) {

    // SESSION FIX: use user_id (same as notification.php)
    // SESSION FIX: use consistent session variable
$_SESSION['user_id'] = $id;
$_SESSION['email'] = $email_db;
$_SESSION['role'] = $role;
$_SESSION['user_name'] = $firstname;  // <-- changed to match main.php

    // Redirect based on role
    if ($role === 'admin') {
        header("Location: admin-dashboard.php");
        exit;
    } else {
        header("Location: main.php");
        exit;
    }

} else {
    echo "Incorrect password.";
}

$stmt->close();
$conn->close();
?>