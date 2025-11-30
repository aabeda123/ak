<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.html");
  exit;
}

$conn = new mysqli("localhost", "root", "", "ak-store");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
  $id = intval($_GET['id']);

  $sql = "DELETE FROM users WHERE id = $id";
  if ($conn->query($sql)) {
    header("Location: manage_user.php?deleted=1");
    exit;
  } else {
    echo "Error deleting user: " . $conn->error;
  }
} else {
  echo "Invalid user ID.";
}
?>
