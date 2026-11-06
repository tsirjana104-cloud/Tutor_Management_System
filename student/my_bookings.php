<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';
requireRole('student'); // Only allow students to access this page

$userId = $_SESSION['user_id']; // booking_requests.student_id references users.id directly

$stmt = $conn->prepare("SELECT br.id, u.name as tutor_name, u.phone as tutor_phone, s.name as subject_name, br.preferred_date, br.preferred_time, br.message, br.status, r.id as review_id
FROM booking_requests br
JOIN tutor_profiles tp ON br.tutor_id = tp.id
JOIN users u ON tp.user_id = u.id
JOIN subjects s ON br.subject_id = s.id
LEFT JOIN reviews r ON br.id = r.booking_id
WHERE br.student_id = ?
ORDER BY br.preferred_date DESC");
$stmt->execute([$userId]);
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Bookings | Home Tutor</title>
  <link rel="stylesheet" href="../assets/css/style.css">
     <link rel="stylesheet" href="../assets/css/style.css?v=2">
</head>
<body class="dashboard-page">

  <?php $activePage = 'bookings'; include '../includes/student_navbar.php'; ?>

  <main class="page-center">
  <div class="auth-card wider">
    <h1>My bookings</h1>
    <p class="subtitle">Track your booking requests and review completed lessons.</p>

    <?php if (count($bookings) === 0): ?>
      <p class="empty-text">You haven't booked any tutors yet.</p>
      <a class="btn-outline" href="search_tutors.php">Find a tutor</a>
    <?php else: ?>

      <div class="toolbar">
        <label for="statusFilter">Show</label>
        <select id="statusFilter">
          <option value="all">All</option>
          <option value="pending">Pending</option>
          <option value="accepted">Accepted</option>
          <option value="completed">Completed</option>
          <option value="rejected">Rejected</option>
        </select>
      </div>

      <div class="table-wrap">
        <table class="data-table" id="bookingTable">
          <thead>
            <tr>
              <th>Tutor</th>
              <th>Subject</th>
              <th>Date</th>
              <th>Time</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($bookings as $booking): ?>
              <tr data-status="<?php echo htmlspecialchars($booking['status']); ?>">
                <td><?php echo htmlspecialchars($booking['tutor_name']); ?></td>
                <td><?php echo htmlspecialchars($booking['subject_name']); ?></td>
                <td><?php echo htmlspecialchars($booking['preferred_date']); ?></td>
                <td><?php echo htmlspecialchars($booking['preferred_time']); ?></td>
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

    <?php endif; ?>
  </div>
  </main>

  <!-- Your JS goes here (create assets/js/student_bookings.js) -->
  <script src="../assets/js/student_bookings.js"></script>
</body>
</html>