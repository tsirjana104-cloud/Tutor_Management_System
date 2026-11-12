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
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Tutors | Home Tutor</title>
  <link rel="stylesheet" href="../assets/css/style.css">
     <link rel="stylesheet" href="../assets/css/style.css?v=2">
</head>
<body class="dashboard-page">

  <?php $activePage = 'tutors'; include '../includes/admin_navbar.php'; ?>

  <main class="page-center">
  <div class="auth-card wider">
    <h1>Manage tutors</h1>
    <p class="subtitle">Approve or reject tutor applications. Pending ones are listed first.</p>

    <div class="toolbar">
      <label for="statusFilter">Show</label>
      <select id="statusFilter">
        <option value="all">All</option>
        <option value="pending">Pending</option>
        <option value="approved">Approved</option>
        <option value="rejected">Rejected</option>
      </select>
    </div>

    <?php if (count($tutors) == 0): ?>
      <p class="empty-text">No tutors have registered yet.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table class="data-table" id="tutorTable">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($tutors as $tutor): ?>
              <tr data-status="<?php echo htmlspecialchars($tutor['status']); ?>">
                <td><?php echo htmlspecialchars($tutor['id']); ?></td>
                <td><?php echo htmlspecialchars($tutor['name']); ?></td>
                <td><?php echo htmlspecialchars($tutor['email']); ?></td>
                <td><?php echo htmlspecialchars($tutor['phone']); ?></td>
                <td>
                  <span class="badge <?php echo htmlspecialchars($tutor['status']); ?>">
                    <?php echo htmlspecialchars($tutor['status']); ?>
                  </span>
                </td>
                <td>
                  <?php if ($tutor['status'] == 'pending'): ?>
                    <a class="btn-small approve" href="?action=approve&id=<?php echo $tutor['id']; ?>">Approve</a>
                    <a class="btn-small reject" href="?action=reject&id=<?php echo $tutor['id']; ?>">Reject</a>
                  <?php else: ?>
                    <span class="muted-text">No actions available</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
  </main>

  <!-- Your JS goes here (create assets/js/manage_tutors.js) -->
  <script src="../assets/js/confirm_modal.js"></script>
  <script src="../assets/js/manage_tutors.js"></script>
</body>
</html>