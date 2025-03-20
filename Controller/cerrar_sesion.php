<?php
// Start the session
session_start();
// Prevent caching
header("Cache-Control: no-cache, must-revalidate"); // HTTP 1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
// Unset all session variables
session_unset();
// Destroy the session
session_destroy();
// Redirect to the index page
header('Location: ../index.php');
exit;
?>
