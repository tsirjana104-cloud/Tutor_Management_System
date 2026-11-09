<?php
// Notification bell, shared by the admin, tutor and student dashboards.
// Needs $conn and an active session (include it after config.php).
// Assumes a `notifications` table with: id, user_id, message, is_read (0/1)

$bellStmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$bellStmt->execute([$_SESSION['user_id']]);
$unreadCount = (int) $bellStmt->fetchColumn();

$listStmt = $conn->prepare("SELECT message, is_read FROM notifications WHERE user_id = ? ORDER BY id DESC LIMIT 5");
$listStmt->execute([$_SESSION['user_id']]);
$latestNotifications = $listStmt->fetchAll();
?>
<details class="bell" id="notificationBell">
  <summary aria-label="Notifications">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"></path>
      <path d="M13.7 21a2 2 0 0 1-3.4 0"></path>
    </svg>
    <?php if ($unreadCount > 0): ?>
      <span class="bell-count" id="bellCount"><?php echo $unreadCount; ?></span>
    <?php endif; ?>
  </summary>

  <div class="bell-panel">
    <div class="bell-header">
      <strong>Notifications</strong>
      <?php if ($unreadCount > 0): ?>
        <a href="../includes/mark_read.php">Mark all as read</a>
      <?php endif; ?>
    </div>

    <?php if (count($latestNotifications) == 0): ?>
      <p class="bell-empty">No notifications yet.</p>
    <?php else: ?>
      <ul class="bell-list">
        <?php foreach ($latestNotifications as $bellItem): ?>
          <li class="<?php echo $bellItem['is_read'] ? '' : 'unread'; ?>">
            <?php echo htmlspecialchars($bellItem['message']); ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</details>