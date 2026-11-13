<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';
requireRole('tutor'); // Only allow tutors to access this page

$userId = $_SESSION['user_id'];

// This tutor's profile row (booking_requests.tutor_id points to tutor_profiles.id)
$stmt = $conn->prepare("SELECT * FROM tutor_profiles WHERE user_id = ?");
$stmt->execute([$userId]);
$tutor = $stmt->fetch();
$tutorId = $tutor['id'];

// Small helper: run a COUNT query for this tutor
function countFor($conn, $sql, $tutorId) {
  $s = $conn->prepare($sql);
  $s->execute([$tutorId]);
  return (int) $s->fetchColumn();
}

$pendingRequests   = countFor($conn, "SELECT COUNT(*) FROM booking_requests WHERE tutor_id = ? AND status = 'pending'", $tutorId);
$acceptedLessons   = countFor($conn, "SELECT COUNT(*) FROM booking_requests WHERE tutor_id = ? AND status = 'accepted'", $tutorId);
$completedLessons  = countFor($conn, "SELECT COUNT(*) FROM booking_requests WHERE tutor_id = ? AND status = 'completed'", $tutorId);
$subjectsTaught    = countFor($conn, "SELECT COUNT(*) FROM tutor_subjects WHERE tutor_id = ?", $tutorId);

// Average rating from student reviews
$ratingStmt = $conn->prepare("SELECT AVG(r.rating) AS avg_rating, COUNT(r.id) AS total_reviews
  FROM reviews r JOIN booking_requests br ON r.booking_id = br.id WHERE br.tutor_id = ?");
$ratingStmt->execute([$tutorId]);
$ratingRow = $ratingStmt->fetch();
$totalReviews = (int) $ratingRow['total_reviews'];
$avgRating = $totalReviews > 0 ? number_format($ratingRow['avg_rating'], 1) : '-';

// Latest 5 booking requests
$recentStmt = $conn->prepare("SELECT br.id, u.name AS student_name, s.name AS subject_name, br.preferred_date, br.status
  FROM booking_requests br
  JOIN users u ON br.student_id = u.id
  JOIN subjects s ON br.subject_id = s.id
  WHERE br.tutor_id = ?
  ORDER BY br.id DESC LIMIT 5");
$recentStmt->execute([$tutorId]);
$recentBookings = $recentStmt->fetchAll();

// Is the profile still incomplete?
// Same rule the student search uses: bio, qualification and an hourly rate above 0
$profileIncomplete = empty($tutor['bio']) || empty($tutor['qualification']) || (float) $tutor['hourly_rate'] <= 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tutor Dashboard | Home Tutor</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-page">

  <?php $activePage = 'dashboard'; include '../includes/tutor_navbar.php'; ?>

  <main class="dashboard-content">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h1>
    <p class="subtitle">
      Account status:
      <span class="badge <?php echo htmlspecialchars($tutor['status']); ?>">
        <?php echo htmlspecialchars($tutor['status']); ?>
      </span>
    </p>

    <?php if ($tutor['status'] == 'pending'): ?>
      <div class="message warning">
        Your account is waiting for admin approval. You will appear in student searches once you are approved.
      </div>
    <?php elseif ($tutor['status'] == 'rejected'): ?>
      <div class="message error">
        Your application was rejected by the admin, so students cannot find you right now.
      </div>
    <?php endif; ?>

    <?php if ($profileIncomplete): ?>
      <div class="message warning">
        Your profile is incomplete, so students cannot find you yet. <a href="profile.php">Complete your profile</a> (bio, qualification and hourly rate) to appear in searches.
      </div>
    <?php endif; ?>

    <div class="stat-grid">
      <a class="stat-card highlight" href="my_bookings.php">
        <span class="stat-number"><?php echo $pendingRequests; ?></span>
        <span class="stat-label">Pending requests</span>
      </a>

      <a class="stat-card" href="my_bookings.php">
        <span class="stat-number"><?php echo $acceptedLessons; ?></span>
        <span class="stat-label">Accepted lessons</span>
      </a>

      <a class="stat-card" href="my_bookings.php">
        <span class="stat-number"><?php echo $completedLessons; ?></span>
        <span class="stat-label">Completed lessons</span>
      </a>

      <a class="stat-card" href="my_subjects.php">
        <span class="stat-number"><?php echo $subjectsTaught; ?></span>
        <span class="stat-label">Subjects I teach</span>
      </a>

      <div class="stat-card">
        <span class="stat-number"><?php echo $avgRating; ?></span>
        <span class="stat-label">
          <?php echo $totalReviews > 0 ? "Average rating ($totalReviews reviews)" : "No reviews yet"; ?>
        </span>
      </div>
    </div>

    <h2 class="section-title">Recent booking requests</h2>

    <div class="panel">
      <?php if (count($recentBookings) == 0): ?>
        <p class="empty-text">No booking requests yet.</p>
      <?php else: ?>
        <div class="table-wrap">
          <table class="data-table" id="recentTable">
            <thead>
              <tr>
                <th>Student</th>
                <th>Subject</th>
                <th>Date</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentBookings as $booking): ?>
                <tr>
                  <td><?php echo htmlspecialchars($booking['student_name']); ?></td>
                  <td><?php echo htmlspecialchars($booking['subject_name']); ?></td>
                  <td><?php echo htmlspecialchars($booking['preferred_date']); ?></td>
                  <td>
                    <span class="badge <?php echo htmlspecialchars($booking['status']); ?>">
                      <?php echo htmlspecialchars($booking['status']); ?>
                    </span>
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
      <a class="btn-small approve" href="my_bookings.php">Manage bookings</a>
      <a class="btn-small approve" href="profile.php">Edit profile</a>
      <a class="btn-small approve" href="my_subjects.php">Manage subjects</a>
    </div>
  </main>

  <!-- Your JS goes here (uses the same assets/js/dashboard.js) -->
  <script src="../assets/js/dashboard.js"></script>
</body>
</html>