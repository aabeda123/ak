<?php
session_start();
session_unset();     // Removes all session variables
session_destroy();   // Destroy the session completely

// Optional: remove session cookie
if (ini_get("session.use_cookies")) {
    setcookie(session_name(), '', time() - 3600, '/');
}

header("Location: main.php");
exit;
?>