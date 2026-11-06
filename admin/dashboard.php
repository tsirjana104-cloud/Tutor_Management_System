<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';
requireRole('admin'); // Only allow admins to access this page

// Numbers for the stat cards
$pendingTutors  = $conn->query("SELECT COUNT(*) FROM tutor_profiles WHERE status = 'pending'")->fetchColumn();
$approvedTutors = $conn->query("SELECT COUNT(*) FROM tutor_profiles WHERE status = 'approved'")->fetchColumn();
$totalStudents  = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$totalSubjects  = $conn->query("SELECT COUNT(*) FROM subjects")->fetchColumn();
$totalBookings  = $conn->query("SELECT COUNT(*) FROM booking_requests")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard | Home Tutor</title>
  <link rel="stylesheet" href="../assets/css/style.css">
     <link rel="stylesheet" href="../assets/css/style.css?v=2">
</head>
<body class="dashboard-page">

  <?php $activePage = 'dashboard'; include '../includes/admin_navbar.php'; ?>

  <!-- Page content -->
  <main class="dashboard-content">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h1>
    <p class="subtitle">Here is what is happening on the platform.</p>

    <div class="stat-grid">
      <a class="stat-card highlight" href="manage_tutors.php">
        <span class="stat-number"><?php echo $pendingTutors; ?></span>
        <span class="stat-label">Tutors waiting for approval</span>
      </a>

      <div class="stat-card">
        <span class="stat-number"><?php echo $approvedTutors; ?></span>
        <span class="stat-label">Approved tutors</span>
      </div>

      <div class="stat-card">
        <span class="stat-number"><?php echo $totalStudents; ?></span>
        <span class="stat-label">Students</span>
      </div>

      <a class="stat-card" href="add_subject.php">
        <span class="stat-number"><?php echo $totalSubjects; ?></span>
        <span class="stat-label">Subjects</span>
      </a>

      <div class="stat-card">
        <span class="stat-number"><?php echo $totalBookings; ?></span>
        <span class="stat-label">Total bookings</span>
      </div>
    </div>

    <h2 class="section-title">Quick actions</h2>
    <div class="quick-actions">
      <a class="btn-small approve" href="manage_tutors.php">Review tutor applications</a>
      <a class="btn-small approve" href="add_subject.php">Add a subject</a>
    </div>
  </main>

  <!-- Your JS goes here (create assets/js/dashboard.js) -->
  <script src="../assets/js/dashboard.js"></script>
</body>
</html>