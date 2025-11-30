<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.html");
  exit;
}

if (!isset($_GET['id'])) {
  die(" Post ID not specified.");
}

$conn = new mysqli("localhost", "root", "", "ak-store");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$id = intval($_GET['id']);

$sql = "DELETE FROM posts WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
  header("Location: manage_posts.php");
  exit;
} else {
  echo " Failed to delete post.";
}

$stmt->close();
$conn->close();
?>
