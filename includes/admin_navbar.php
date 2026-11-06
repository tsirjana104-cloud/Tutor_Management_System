<?php
// Admin top bar, shared by all admin pages.
// Set $activePage before including: 'dashboard', 'tutors' or 'subjects'
$activePage = $activePage ?? '';
?>
<header class="topbar">
  <div class="brand">Home Tutor <span>Admin</span></div>

  <nav class="topbar-links">
    <a href="dashboard.php" class="<?php echo $activePage == 'dashboard' ? 'active' : ''; ?>">Dashboard</a>
    <a href="manage_tutors.php" class="<?php echo $activePage == 'tutors' ? 'active' : ''; ?>">Manage Tutors</a>
    <a href="add_subject.php" class="<?php echo $activePage == 'subjects' ? 'active' : ''; ?>">Subjects</a>
  </nav>

  <div class="topbar-right">
    <?php include '../includes/notification_bell.php'; ?>
    <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
    <a href="../auth/logout.php" class="logout-link">Logout</a>
  </div>
</header>