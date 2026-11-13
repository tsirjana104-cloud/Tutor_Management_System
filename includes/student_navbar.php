<?php
// Student top bar, shared by all student pages.
// Set $activePage before including: 'dashboard', 'search' or 'bookings'
$activePage = $activePage ?? '';
?>
<header class="topbar">
  <div class="brand">Home Tutor <span>Student</span></div>

  <nav class="topbar-links">
    <a href="dashboard.php" class="<?php echo $activePage == 'dashboard' ? 'active' : ''; ?>">Dashboard</a>
    <a href="search_tutor.php" class="<?php echo $activePage == 'search' ? 'active' : ''; ?>">Find Tutors</a>
    <a href="my_bookings.php" class="<?php echo $activePage == 'bookings' ? 'active' : ''; ?>">My Bookings</a>
  </nav>

  <div class="topbar-right">
    <?php include '../includes/notification_bell.php'; ?>
    <?php include '../includes/user_menu.php'; ?>
    <a href="../auth/logout.php" class="logout-link">Logout</a>
  </div>
</header>
<script src="../assets/js/dashboard.js"></script>