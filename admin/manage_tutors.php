<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';
requireRole('admin'); // Only allow admins to access this page

// Handle approve/reject actions
if (isset($_GET['action']) && isset($_GET['id'])) {
  $tutor_id = $_GET['id'];
  $action = $_GET['action']; // 'approve' or 'reject'

  if ($action == 'approve') {
    $stmt = $conn->prepare("UPDATE tutor_profiles SET status = 'approved' WHERE id = ?");
    $stmt->execute([$tutor_id]);
  } elseif ($action == 'reject') {
    $stmt = $conn->prepare("UPDATE tutor_profiles SET status = 'rejected' WHERE id = ?");
    $stmt->execute([$tutor_id]);
  }

  header("Location: manage_tutors.php"); // Redirect back to the manage tutors page
  exit();
}

//Fetch all tutor profiles with their user information
$stmt = $conn->prepare("SELECT tp.id, u.name, u.email, u.phone, tp.status FROM tutor_profiles tp JOIN users u ON tp.user_id = u.id
ORDER BY tp.status = 'pending' DESC, tp.id DESC");
$stmt->execute();
$tutors = $stmt->fetchAll();
?>

<h2>Manage Tutors</h2>

<table border="1" cellpadding="8">
  <tr>
    <th>ID</th>
    <th>Name</th>
    <th>Email</th>
    <th>Phone</th>
    <th>Status</th>
    <th>Actions</th>
  </tr>
  <?php foreach ($tutors as $tutor): ?>
    <tr>
      <td><?php echo ($tutor['id']); ?></td>
      <td><?php echo ($tutor['name']); ?></td>
      <td><?php echo ($tutor['email']); ?></td>
      <td><?php echo ($tutor['phone']); ?></td>
      <td><?php echo ($tutor['status']); ?></td>
      <td>
        <?php if ($tutor['status'] == 'pending'): ?>
          <a href="?action=approve&id=<?php echo $tutor['id']; ?>">Approve</a> |
          <a href="?action=reject&id=<?php echo $tutor['id']; ?>">Reject</a>
        <?php else: ?>
          No actions available
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
</table>