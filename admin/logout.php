<?php
session_start();

// Unset only admin session variables to not disrupt student session if somehow shared
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_email']);

// Redirect to admin login page
header("Location: login.php");
exit;
?>
