<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
  die("Please log in first.");
}

// Mark every notification of the logged-in user as read
$stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);

// Send the user back to their own dashboard
if ($_SESSION['user_role'] == 'admin') {
  header("Location: ../admin/dashboard.php");
} elseif ($_SESSION['user_role'] == 'tutor') {
  header("Location: ../tutor/dashboard.php");
} else {
  header("Location: ../student/dashboard.php");
}
exit();