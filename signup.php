<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli("localhost", "root", "", "ak-store");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/* -----------------------------
   1. Check Required Fields
------------------------------*/
if (
    empty($_POST['firstname']) || empty($_POST['lastname']) ||
    empty($_POST['email']) || empty($_POST['phone']) ||
    empty($_POST['password']) || empty($_POST['repassword'])
) {
    die("Please fill all required fields.");
}

// Escape values
$firstname = trim($_POST['firstname']);
$lastname = trim($_POST['lastname']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$password = $_POST['password'];
$repassword = $_POST['repassword'];

/* -----------------------------
   2. Validation (matches your HTML)
------------------------------*/

// Firstname & Lastname only letters
if (!preg_match("/^[A-Za-z]{2,}$/", $firstname)) {
    die("Invalid firstname. Only letters allowed.");
}
if (!preg_match("/^[A-Za-z]{2,}$/", $lastname)) {
    die("Invalid lastname. Only letters allowed.");
}

// Phone must be 10 digits
if (!preg_match("/^[0-9]{10}$/", $phone)) {
    die("Phone number must be exactly 10 digits.");
}

// Email format validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Invalid email format.");
}

// Check password match
if ($password !== $repassword) {
    die("Passwords do not match.");
}

// Password minimum length
if (strlen($password) < 6) {
    die("Password must be at least 6 characters.");
}

/* -----------------------------
   3. Check Duplicate Email
------------------------------*/
$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    die("Email already exists. Use another email.");
}
$check->close();

/* -----------------------------
   4. Insert New User
------------------------------*/
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$role = "user";

$sql = "INSERT INTO users (firstname, lastname, email, phone, password, role) 
        VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("ssssss", $firstname, $lastname, $email, $phone, $hashed_password, $role);

if ($stmt->execute()) {

    echo "
    <html>
        <head>
            <script>
                setTimeout(function() {
                    window.location.href = 'login.html';
                }, 1000);
            </script>
        </head>
        <body></body>
    </html>
    ";

} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>