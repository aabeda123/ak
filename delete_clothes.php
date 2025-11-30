<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.html");
  exit;
}

if (!isset($_GET['id'])) {
  die("Invalid request.");
}

$conn = new mysqli("localhost", "root", "", "ak-store");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$id = intval($_GET['id']);
$conn->query("DELETE FROM posts WHERE id = $id");

header("Location: manage_post.php?deleted=1");
exit;
?>
