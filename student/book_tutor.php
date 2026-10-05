<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';
require_once '../includes/notify_helper.php';
requireRole('student'); // Only allow students to access this page

$tutorProfileId = $_GET['tutor_id'] ?? ($_POST['tutor_id'] ?? null);

if (!$tutorProfileId) {
  die("No tutor selected.");
}

// Get tutor's basic info
$tutorStmt = $conn->prepare("SELECT tp.id, u.name FROM tutor_profiles tp JOIN users u ON tp.user_id = u.id WHERE tp.id = ?");
$tutorStmt->execute([$tutorProfileId]);
$tutor = $tutorStmt->fetch();

if (!$tutor) {
  die("Tutor not found.");
}

// Get subjects this tutor teaches (Only let student pick from these)
$subStmt = $conn->prepare("SELECT s.id, s.name FROM tutor_subjects ts JOIN subjects s ON ts.subject_id = s.id WHERE ts.tutor_id = ?");
$subStmt->execute([$tutorProfileId]);
$tutorSubjects = $subStmt->fetchAll();

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $subjectId = $_POST['subject_id'];
  $date = $_POST['preferred_date'];
  $time = $_POST['preferred_time'];
  $note = trim($_POST['message']);


  // Server side validation: reject past dates even if the browser allows it
  if ($date < date('Y-m-d')) {
    $message = "You cannot select a past date. Please choose today or a future date.";
  } else {
    // Insert booking request
  $insertStmt = $conn->prepare("INSERT INTO booking_requests (student_id, tutor_id, subject_id, preferred_date, preferred_time, message) VALUES (?, ?, ?, ?, ?, ?)");

  $insertStmt->execute([$_SESSION['user_id'], $tutorProfileId, $subjectId, $date, $time, $note]);

  $tutorUserStmt = $conn->prepare("SELECT user_id FROM tutor_profiles WHERE id = ?");
  $tutorUserStmt->execute([$tutorProfileId]);
  $tutorUserId = $tutorUserStmt->fetch()['user_id'];
  createNotification($conn, $tutorUserId, "You have a new booking request from " . $_SESSION['user_name']);

  $message = "Booking request sent successfully!";
  }
}
?>

<h2>Book <?php echo htmlspecialchars($tutor['name']); ?></h2>
<h3><?php echo $message; ?></h3>

<form method="POST">
  <input type="hidden" name="tutor_id" value="<?php echo $tutorProfileId; ?>">

  Subject:
  <select name="subject_id" required>
    <?php foreach ($tutorSubjects as $subject): ?>
      <option value="<?php echo $subject['id']; ?>"><?php echo $subject['name']; ?></option>
      <?php endforeach; ?>
  </select><br><br>

  Preferred Date:
  <input type="date" name="preferred_date" min="<?php echo date('Y-m-d'); ?>" required><br><br>

  Preferred Time:
  <input type="time" name="preferred_time" min="09:00" max="18:00" required><br><br>

  Message:
  <textarea name="message" required></textarea><br><br>  

  <button type="submit">Send Booking Request</button>
</form>

<br>
<a href="search_tutor.php">Back to Search</a>