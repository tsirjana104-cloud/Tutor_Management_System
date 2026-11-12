<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';
require_once '../includes/notify_helper.php';
requireRole('tutor'); // Only allow tutors to access this page

// Lesson times are compared with the current time, so PHP needs the right timezone.
// (Better: move this line into includes/config.php so every page uses it.)
date_default_timezone_set('Asia/Kathmandu');

$userId = $_SESSION['user_id'];

// Get this tutor's tutor_profiles.id first
$stmt = $conn->prepare("SELECT id FROM tutor_profiles WHERE user_id = ?");
$stmt->execute([$userId]);
$tutorProfile = $stmt->fetch()['id'];

// Message shown when an action is blocked
$error = '';
if (isset($_GET['error']) && $_GET['error'] == 'too_early') {
  $error = "You can only mark a lesson as completed after its scheduled date and time.";
} elseif (isset($_GET['error']) && $_GET['error'] == 'slot_taken') {
  $error = "You already have a lesson at that date and time. Reject this request or ask the student to choose another time.";
}

// Handle accept/reject/complete actions
if (isset($_GET['action']) && isset($_GET['booking_id'])) {
  $bookingId = $_GET['booking_id'];
  $action = $_GET['action'];
  $redirect = "my_bookings.php";

  // Load the booking, only if it belongs to this tutor (a tutor can't touch someone else's booking by guessing an id)
  $infoStmt = $conn->prepare("SELECT student_id, status, preferred_date, preferred_time FROM booking_requests WHERE id = ? AND tutor_id = ?");
  $infoStmt->execute([$bookingId, $tutorProfile]);
  $info = $infoStmt->fetch();

  if ($info) {
    $newStatus = null;

    if ($action === 'accept' && $info['status'] === 'pending') {
      // A tutor cannot accept two requests for the same date and time
      $clashStmt = $conn->prepare("SELECT id FROM booking_requests WHERE tutor_id = ? AND preferred_date = ? AND preferred_time = ? AND status IN ('accepted', 'completed') AND id != ?");
      $clashStmt->execute([$tutorProfile, $info['preferred_date'], $info['preferred_time'], $bookingId]);

      if ($clashStmt->rowCount() > 0) {
        $redirect = "my_bookings.php?error=slot_taken";
      } else {
        $newStatus = 'accepted';
      }
    } elseif ($action === 'reject' && $info['status'] === 'pending') {
      $newStatus = 'rejected';
    } elseif ($action === 'complete' && $info['status'] === 'accepted') {
      // A lesson can only be completed once its scheduled date and time has arrived
      $lessonTime = strtotime($info['preferred_date'] . ' ' . $info['preferred_time']);
      if ($lessonTime <= time()) {
        $newStatus = 'completed';
      } else {
        $redirect = "my_bookings.php?error=too_early";
      }
    }

    if ($newStatus) {
      $updateStmt = $conn->prepare("UPDATE booking_requests SET status = ? WHERE id = ? AND tutor_id = ?");
      $updateStmt->execute([$newStatus, $bookingId, $tutorProfile]);

      createNotification($conn, $info['student_id'], "Your booking status was updated to: " . $newStatus);
    }
  }

  header("Location: " . $redirect); // Redirect to avoid resubmission
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

// Slots (date + time) where this tutor already has an accepted or completed lesson, like "2026-11-20 14:00"
$takenSlots = [];
foreach ($bookings as $b) {
  if ($b['status'] === 'accepted' || $b['status'] === 'completed') {
    $takenSlots[] = $b['preferred_date'] . ' ' . substr($b['preferred_time'], 0, 5);
  }
}
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

  <?php $activePage = 'bookings'; include '../includes/tutor_navbar.php'; ?>

  <main class="page-center">
  <div class="auth-card widest">
    <h1>My booking requests</h1>
    <p class="subtitle">Accept or reject requests from students. Pending ones are listed first.</p>

    <?php if ($error != ''): ?>
      <div class="message error">
        <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>

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
              <?php
                $lessonTs = strtotime($booking['preferred_date'] . ' ' . $booking['preferred_time']);
                $canComplete = $lessonTs <= time();
                $slotTaken = in_array($booking['preferred_date'] . ' ' . substr($booking['preferred_time'], 0, 5), $takenSlots);
              ?>
              <tr data-status="<?php echo htmlspecialchars($booking['status']); ?>" data-lesson-time="<?php echo $lessonTs; ?>">
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
                    <?php if ($slotTaken): ?>
                      <span class="btn-small disabled" title="You already have a lesson at this time">Accept</span>
                    <?php else: ?>
                      <a class="btn-small approve" href="?action=accept&booking_id=<?php echo $booking['id']; ?>">Accept</a>
                    <?php endif; ?>
                    <a class="btn-small reject" href="?action=reject&booking_id=<?php echo $booking['id']; ?>">Reject</a>
                    <?php if ($slotTaken): ?>
                      <span class="muted-text block-text">You already have a lesson at this time</span>
                    <?php endif; ?>
                  <?php elseif ($booking['status'] === 'accepted'): ?>
                    <?php if ($canComplete): ?>
                      <a class="btn-small approve complete-link" href="?action=complete&booking_id=<?php echo $booking['id']; ?>">Mark as completed</a>
                    <?php else: ?>
                      <span class="btn-small disabled" title="Available after the lesson time" data-href="?action=complete&booking_id=<?php echo $booking['id']; ?>">Mark as completed</span>
                      <span class="muted-text block-text">Available after <?php echo date('d M, g:i A', $lessonTs); ?></span>
                    <?php endif; ?>
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

  <!-- Your JS goes here (create assets/js/my_bookings.js) -->
  <script src="../assets/js/confirm_modal.js"></script>
  <script src="../assets/js/my_bookings.js"></script>
</body>
</html>