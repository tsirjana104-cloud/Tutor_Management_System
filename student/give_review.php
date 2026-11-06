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
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Give Review | Home Tutor</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

  <div class="auth-card wide">
    <h1>Review <?php echo htmlspecialchars($booking['tutor_name']); ?></h1>
    <p class="subtitle">Tell other students how your lesson went.</p>

    <?php if ($message): ?>
      <div class="message success">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php else: ?>

      <form method="POST" id="reviewForm" novalidate>
        <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($bookingId); ?>">

        <div class="form-group">
          <label for="rating">Rating</label>
          <select id="rating" name="rating" required>
            <option value="1">1 - Poor</option>
            <option value="2">2 - Fair</option>
            <option value="3">3 - Good</option>
            <option value="4">4 - Very good</option>
            <option value="5">5 - Excellent</option>
          </select>
          <small class="error-text" id="ratingError"></small>
        </div>

        <div class="form-group">
          <label for="comment">Comment</label>
          <textarea id="comment" name="comment" rows="4"
                    placeholder="Share your experience..."></textarea>
          <small class="error-text" id="commentError"></small>
        </div>

        <button type="submit" class="btn">Submit review</button>
      </form>

    <?php endif; ?>

    <a class="btn-outline" href="my_bookings.php">Back to my bookings</a>
  </div>

  <!-- Your JS goes here (create assets/js/give_review.js) -->
  <script src="../assets/js/give_review.js"></script>
</body>
</html>