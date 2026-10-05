<?php
require_once 'includes/config.php';

if (isset($_SESSION['user_id'])) {
    // already logged in - go straight to their dashboard
    if ($_SESSION['user_role'] == 'admin') {
        header("Location: admin/dashboard.php");
    } elseif ($_SESSION['user_role'] == 'tutor') {
        header("Location: tutor/dashboard.php");
    } else {
        header("Location: student/dashboard.php");
    }
} else {
    header("Location: auth/login.php");
}
exit();
?>