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


<h2>My Booking Requests</h2>

<?php if (count($bookings) === 0): ?>
    <p>No booking requests found.</p>
<?php else: ?>
  <table border="1" cellpadding="8">
    <tr>
      <th>Student Name</th>
      <th>Phone</th>
      <th>Subject</th>
      <th>Preferred Date</th>
      <th>Preferred Time</th>
      <th>Message</th>
      <th>Status</th>
      <th>Actions</th>
    </tr>
    <?php foreach ($bookings as $booking): ?>
      <tr>
        <td><?php echo htmlspecialchars($booking['student_name']); ?></td>
        <td><?php echo htmlspecialchars($booking['student_phone']); ?></td>
        <td><?php echo htmlspecialchars($booking['subject_name']); ?></td>
        <td><?php echo $booking['preferred_date']; ?></td>
        <td><?php echo $booking['preferred_time']; ?></td>
        <td><?php echo htmlspecialchars($booking['message']); ?></td>
        <td><?php echo $booking['status']; ?></td>
        <td>
          <?php if ($booking['status'] === 'pending'): ?>
            <a href="?action=accept&booking_id=<?php echo $booking['id']; ?>">Accept</a> | 
            <a href="?action=reject&booking_id=<?php echo $booking['id']; ?>">Reject</a>
          <?php elseif ($booking['status'] === 'accepted'): ?>
            <a href="?action=complete&booking_id=<?php echo $booking['id']; ?>">Mark as Completed</a>
          <?php else: ?>
            N/A
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
  </table>
<?php endif; ?>