<?php
require_once '../includes/config.php';

session_unset(); // Unset all session variables
session_destroy(); // Destroy the session

header("Location: ../auth/login.php"); // Redirect to login page
exit();
?>