<?php
// Tutor top bar, shared by all tutor pages.
// Set $activePage before including: 'dashboard', 'profile', 'subjects' or 'bookings'
$activePage = $activePage ?? '';
?>
<header class="topbar">
  <div class="brand">Home Tutor <span>Tutor</span></div>

  <nav class="topbar-links">
    <a href="dashboard.php" class="<?php echo $activePage == 'dashboard' ? 'active' : ''; ?>">Dashboard</a>
    <a href="profile.php" class="<?php echo $activePage == 'profile' ? 'active' : ''; ?>">My Profile</a>
    <a href="my_subjects.php" class="<?php echo $activePage == 'subjects' ? 'active' : ''; ?>">My Subjects</a>
    <a href="my_bookings.php" class="<?php echo $activePage == 'bookings' ? 'active' : ''; ?>">Bookings</a>
  </nav>

  <div class="topbar-right">
    <?php include '../includes/notification_bell.php'; ?>
    <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
    <a href="../auth/logout.php" class="logout-link">Logout</a>
  </div>
</header>