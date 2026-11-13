<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';
requireRole('tutor'); // Only allow tutors to access this page
require_once '../includes/change_password_logic.php'; // handles the form BEFORE any HTML is sent
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Change Password | Home Tutor</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-page">

  <?php $activePage = 'password'; include '../includes/tutor_navbar.php'; ?>

  <main class="page-center">
    <?php include '../includes/change_password_card.php'; ?>
  </main>

  <!-- Your JS goes here (same file for all three roles) -->
  <script src="../assets/js/change_password.js"></script>
</body>
</html>