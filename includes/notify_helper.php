<?php
// Reusable function to create a notification for any user

function createNotification($conn, $userId, $message) {
  $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
  $stmt->execute([$userId, $message]);
}
?>