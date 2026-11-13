<?php
// Change-password logic, shared by the admin, tutor and student pages.
// Include it at the TOP of the page, right after requireRole() and BEFORE any HTML is printed,
// because session_regenerate_id() does not work once output has started.
// It sets $message and $messageType, which change_password_card.php then displays.
require_once __DIR__ . '/notify_helper.php';

$message = '';
$messageType = ''; // "success" or "error"

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $current = $_POST['current_password'] ?? '';
  $new = $_POST['new_password'] ?? '';
  $confirm = $_POST['confirm_password'] ?? '';

  // Get the saved (hashed) password of the logged-in user
  $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
  $stmt->execute([$_SESSION['user_id']]);
  $user = $stmt->fetch();

  if ($current === '' || $new === '' || $confirm === '') {
    $message = "Please fill in all the fields.";
    $messageType = "error";
  } elseif (!$user || !password_verify($current, $user['password'])) {
    $message = "Your current password is incorrect.";
    $messageType = "error";
  } elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d).{6,64}$/', $new)) {
    // Same rule as registration: 6 to 64 characters with at least one letter and one number
    $message = "The new password must be 6 to 64 characters long and contain at least one letter and one number.";
    $messageType = "error";
  } elseif ($new !== $confirm) {
    $message = "The new password and the confirmation do not match.";
    $messageType = "error";
  } elseif ($new === $current) {
    $message = "The new password must be different from your current password.";
    $messageType = "error";
  } else {
    // All good: hash the new password and save it
    $hashedPassword = password_hash($new, PASSWORD_DEFAULT);
    $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $updateStmt->execute([$hashedPassword, $_SESSION['user_id']]);

    // New session id after a password change, so an old session id cannot be reused
    session_regenerate_id(true);

    // Leave a note in the bell, so the user can spot a change they did not make
    createNotification($conn, $_SESSION['user_id'], "Your password was changed successfully.");

    $message = "Password changed successfully!";
    $messageType = "success";
  }
}
?>