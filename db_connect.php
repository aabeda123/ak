<?php
$conn = new mysqli("localhost", "root", "", "ak-store");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
