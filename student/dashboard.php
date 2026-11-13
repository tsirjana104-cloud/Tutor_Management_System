<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';
requireRole('student'); // Only allow students to access this page

$userId = $_SESSION['user_id']; // booking_requests.student_id references users.id directly

// Number of bookings per status, e.g. ['pending' => 2, 'completed' => 1]
$statusStmt = $conn->prepare("SELECT status, COUNT(*) FROM booking_requests WHERE student_id = ? GROUP BY status");
$statusStmt->execute([$userId]);
$byStatus = $statusStmt->fetchAll(PDO::FETCH_KEY_PAIR);

$totalBookings = array_sum($byStatus);
$pendingCount   = $byStatus['pending'] ?? 0;
$acceptedCount  = $byStatus['accepted'] ?? 0;
$completedCount = $byStatus['completed'] ?? 0;

// Completed lessons that have no review yet
$reviewStmt = $conn->prepare("SELECT COUNT(*) FROM booking_requests br
  LEFT JOIN reviews r ON br.id = r.booking_id
  WHERE br.student_id = ? AND br.status = 'completed' AND r.id IS NULL");
$reviewStmt->execute([$userId]);
$awaitingReview = (int) $reviewStmt->fetchColumn();

// Latest 5 bookings
$recentStmt = $conn->prepare("SELECT br.id, u.name AS tutor_name, s.name AS subject_name, br.preferred_date, br.status, r.id AS review_id
  FROM booking_requests br
  JOIN tutor_profiles tp ON br.tutor_id = tp.id
  JOIN users u ON tp.user_id = u.id
  JOIN subjects s ON br.subject_id = s.id
  LEFT JOIN reviews r ON br.id = r.booking_id
  WHERE br.student_id = ?
  ORDER BY br.id DESC LIMIT 5");
$recentStmt->execute([$userId]);
$recentBookings = $recentStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Dashboard | Home Tutor</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-page">

  <?php $activePage = 'dashboard'; include '../includes/student_navbar.php'; ?>

  <main class="dashboard-content">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h1>
    <p class="subtitle">Find a tutor and keep track of your lessons.</p>

    <?php if ($awaitingReview > 0): ?>
      <div class="message info">
        You have <?php echo $awaitingReview; ?> completed
        <?php echo $awaitingReview == 1 ? 'lesson' : 'lessons'; ?> waiting for your review.
        <a href="my_bookings.php">Leave a review</a>
      </div>
    <?php endif; ?>

    <div class="stat-grid">
      <a class="stat-card" href="my_bookings.php">
        <span class="stat-number"><?php echo $totalBookings; ?></span>
        <span class="stat-label">Total bookings</span>
      </a>

      <a class="stat-card highlight" href="my_bookings.php">
        <span class="stat-number"><?php echo $pendingCount; ?></span>
        <span class="stat-label">Waiting for tutor</span>
      </a>

      <a class="stat-card" href="my_bookings.php">
        <span class="stat-number"><?php echo $acceptedCount; ?></span>
        <span class="stat-label">Accepted lessons</span>
      </a>

      <a class="stat-card" href="my_bookings.php">
        <span class="stat-number"><?php echo $completedCount; ?></span>
        <span class="stat-label">Completed lessons</span>
      </a>
    </div>

    <h2 class="section-title">Recent bookings</h2>

    <div class="panel">
      <?php if (count($recentBookings) == 0): ?>
        <p class="empty-text">You haven't booked any tutors yet.</p>
        <p class="panel-link"><a href="search_tutors.php">Find your first tutor</a></p>
      <?php else: ?>
        <div class="table-wrap">
          <table class="data-table" id="recentTable">
            <thead>
              <tr>
                <th>Tutor</th>
                <th>Subject</th>
                <th>Date</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentBookings as $booking): ?>
                <tr>
                  <td><?php echo htmlspecialchars($booking['tutor_name']); ?></td>
                  <td><?php echo htmlspecialchars($booking['subject_name']); ?></td>
                  <td><?php echo htmlspecialchars($booking['preferred_date']); ?></td>
                  <td>
                    <span class="badge <?php echo htmlspecialchars($booking['status']); ?>">
                      <?php echo htmlspecialchars($booking['status']); ?>
                    </span>
                  </td>
                  <td>
                    <?php if ($booking['status'] === 'completed' && !$booking['review_id']): ?>
                      <a class="btn-small approve" href="give_review.php?booking_id=<?php echo $booking['id']; ?>">Leave review</a>
                    <?php elseif ($booking['review_id']): ?>
                      <span class="muted-text">Reviewed</span>
                    <?php else: ?>
                      <span class="muted-text">N/A</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <p class="panel-link"><a href="my_bookings.php">View all bookings</a></p>
      <?php endif; ?>
    </div>

    <h2 class="section-title">Quick actions</h2>
    <div class="quick-actions">
      <a class="btn-small approve" href="search_tutor.php">Find a tutor</a>
      <a class="btn-small approve" href="my_bookings.php">My bookings</a>
    </div>
  </main>

  <!-- Your JS goes here (uses the same assets/js/dashboard.js) -->
  <script src="../assets/js/dashboard.js"></script>
</body>
</html>