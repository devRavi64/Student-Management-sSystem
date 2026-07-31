<?php
session_start();

// Unset only student session variables
unset($_SESSION['student_logged_in']);
unset($_SESSION['student_db_id']);
unset($_SESSION['student_id']);
unset($_SESSION['student_name']);
unset($_SESSION['student_email']);
unset($_SESSION['student_class']);

// Redirect to student login page
header("Location: login.php");
exit;
?>
