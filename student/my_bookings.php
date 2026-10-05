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

<h2>My Bookings</h2>

<?php if (count($bookings) === 0): ?>
    <p>You haven't booked any tutors yet.</p>
<?php else: ?>
  <table border="1" cellpadding="8">
    <tr>
      <th>Tutor Name</th>
      <th>Subject</th>
      <th>Date</th>
      <th>Time</th>
      <th>Status</th>
      <th>Action</th>
    </tr>
    <?php foreach ($bookings as $booking): ?>
        <tr>
            <td><?php echo htmlspecialchars($booking['tutor_name']); ?></td>
            <td><?php echo htmlspecialchars($booking['subject_name']); ?></td>
            <td><?php echo $booking['preferred_date']; ?></td>
            <td><?php echo $booking['preferred_time']; ?></td>
            <td><?php echo $booking['status']; ?></td>
            <td>
                <?php if ($booking['status'] === 'completed' && !$booking['review_id']): ?>
                    <a href="give_review.php?booking_id=<?php echo $booking['id']; ?>">Leave Review</a>
                <?php elseif ($booking['review_id']): ?>
                    Reviewed
                <?php else: ?>
                    N/A
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
  </table>
<?php endif; ?>