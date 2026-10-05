<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';
requireRole('student'); // Only allow students to access this page

$userId = $_SESSION['user_id'];
$bookingId = $_GET['booking_id'] ?? ($_POST['booking_id'] ?? null);

if (!$bookingId) {
  die("No booking specified.");
}

// Verify this booking belongs to this student AND is completed AND not already reviewed
$checkStmt = $conn->prepare("
  SELECT br.id, br.status, u.name AS tutor_name, r.id as existing_review
  FROM booking_requests br
  JOIN tutor_profiles tp ON br.tutor_id = tp.id
  JOIN users u ON tp.user_id = u.id
  LEFT JOIN reviews r ON br.id = r.booking_id
  WHERE br.id = ? AND br.student_id = ?
");
$checkStmt->execute([$bookingId, $userId]);
$booking = $checkStmt->fetch();

if (!$booking) {
  die("Booking not found or does not belong to you.");
}
if ($booking['status'] != 'completed') {
  die("You can only review completed bookings.");
} 
if ($booking['existing_review']) {
  die("You have already reviewed this booking.");
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $rating = $_POST['rating'];
  $comment = trim($_POST['comment']);

  $insertStmt = $conn->prepare("INSERT INTO reviews (booking_id, rating, comment) VALUES (?, ?, ?)");
  $insertStmt->execute([$bookingId, $rating, $comment]);

  $message = "Review submitted successfully!";
}
?>


<h2>Review for <?php echo htmlspecialchars($booking['tutor_name']); ?></h2>
<h3><?php echo $message; ?></h3>


<?php if (!$message): ?>
  <form method = "POST">
    <input type="hidden" name="booking_id" value="<?php echo $bookingId; ?>">

    Rating:
    <select name="rating" required>
      <option value="1">1</option>
      <option value="2">2</option>
      <option value="3">3</option>
      <option value="4">4</option>
      <option value="5">5</option>
    </select><br><br>

    Comment:<br>
    <textarea name="comment" placeholder="Share your experience..."></textarea><br><br>

    <button type="submit">Submit Review</button>
  </form>
<?php endif; ?>


<br>
<a href="my_bookings.php">Back to My Bookings</a>