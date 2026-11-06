<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';
require_once '../includes/notify_helper.php';
requireRole('tutor'); // Only allow tutors to access this page

$userId = $_SESSION['user_id'];

// Get this tutor's tutor_profiles.id first
$stmt = $conn->prepare("SELECT id FROM tutor_profiles WHERE user_id = ?");
$stmt->execute([$userId]);
$tutorProfile = $stmt->fetch()['id'];

// Handle accept/reject actions
if (isset($_GET['action']) && isset($_GET['booking_id'])) {
  $bookingId = $_GET['booking_id'];
  $action = $_GET['action']; 

  if ($action === 'accept') {
    $newStatus = 'accepted';
  } elseif ($action === 'reject') {
    $newStatus = 'rejected';
  } elseif ($action === 'complete') {
    $newStatus = 'completed';
  } else {
    $newStatus = null;
  }

  if ($newStatus) {
    // check tutor_id matches, so a tutor can't update someone else's booking by just guessing a booking_id in the URL
    $updateStmt = $conn->prepare("UPDATE booking_requests SET status = ? WHERE id = ? AND tutor_id = ?");
    $updateStmt->execute([$newStatus, $bookingId, $tutorProfile]);

    $bookingInfoStmt = $conn->prepare("SELECT student_id FROM booking_requests WHERE id = ?");
    $bookingInfoStmt->execute([$bookingId]);
    $studentId = $bookingInfoStmt->fetch()['student_id'];

    createNotification($conn, $studentId, "Your booking status was updated to: " . $newStatus);
  }

  header("Location: my_bookings.php"); // Redirect to avoid resubmission
  exit();
}

// Fetch all bookings for this tutor, with student names and subject names
$bookingStmt = $conn->prepare("SELECT br.id, u.name as student_name, u.phone as student_phone, s.name as subject_name, br.preferred_date, br.preferred_time, br.message, br.status
FROM booking_requests br
JOIN users u ON br.student_id = u.id
JOIN subjects s ON br.subject_id = s.id
WHERE br.tutor_id = ?
ORDER BY br.status = 'pending' DESC, br.preferred_date ASC");
$bookingStmt->execute([$tutorProfile]);
$bookings = $bookingStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Bookings | Home Tutor</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

  <div class="auth-card widest">
    <h1>My booking requests</h1>
    <p class="subtitle">Accept or reject requests from students. Pending ones are listed first.</p>

    <?php if (count($bookings) === 0): ?>
      <p class="empty-text">No booking requests found.</p>
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
              <th>Student</th>
              <th>Phone</th>
              <th>Subject</th>
              <th>Date</th>
              <th>Time</th>
              <th>Message</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($bookings as $booking): ?>
              <tr data-status="<?php echo htmlspecialchars($booking['status']); ?>">
                <td><?php echo htmlspecialchars($booking['student_name']); ?></td>
                <td><?php echo htmlspecialchars($booking['student_phone']); ?></td>
                <td><?php echo htmlspecialchars($booking['subject_name']); ?></td>
                <td><?php echo htmlspecialchars($booking['preferred_date']); ?></td>
                <td><?php echo htmlspecialchars($booking['preferred_time']); ?></td>
                <td class="cell-wrap"><?php echo htmlspecialchars($booking['message']); ?></td>
                <td>
                  <span class="badge <?php echo htmlspecialchars($booking['status']); ?>">
                    <?php echo htmlspecialchars($booking['status']); ?>
                  </span>
                </td>
                <td>
                  <?php if ($booking['status'] === 'pending'): ?>
                    <a class="btn-small approve" href="?action=accept&booking_id=<?php echo $booking['id']; ?>">Accept</a>
                    <a class="btn-small reject" href="?action=reject&booking_id=<?php echo $booking['id']; ?>">Reject</a>
                  <?php elseif ($booking['status'] === 'accepted'): ?>
                    <a class="btn-small approve" href="?action=complete&booking_id=<?php echo $booking['id']; ?>">Mark as completed</a>
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

  <!-- Your JS goes here (create assets/js/my_bookings.js) -->
  <script src="../assets/js/my_bookings.js"></script>
</body>
</html>