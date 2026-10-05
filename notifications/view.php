<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';
// no requireRole() here - any logged-in user can view notifications

if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit();
}

$userId = $_SESSION['user_id'];

// Mark all as read when this page is visited
$conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$userId]);

// Fetch notifications, newest first
$notifications = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$notifications->execute([$userId]);
$notifications = $notifications->fetchAll();
?>


<h2>My Notifications</h2>

<?php if (count($notifications) == 0): ?>
  <p>No notifications found.</p>
<?php else: ?>
  <ul>
    <?php foreach ($notifications as $notification): ?>
      <li>
        <?php echo $notification['message']; ?>
      </li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>