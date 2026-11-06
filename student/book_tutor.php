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
$messageType = ""; // "success" or "error"

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $subjectId = $_POST['subject_id'];
  $date = $_POST['preferred_date'];
  $time = $_POST['preferred_time'];
  $note = trim($_POST['message']);


  // Server side validation: reject past dates even if the browser allows it
  if ($date < date('Y-m-d')) {
    $message = "You cannot select a past date. Please choose today or a future date.";
    $messageType = "error";
  } else {
    // Insert booking request
  $insertStmt = $conn->prepare("INSERT INTO booking_requests (student_id, tutor_id, subject_id, preferred_date, preferred_time, message) VALUES (?, ?, ?, ?, ?, ?)");

  $insertStmt->execute([$_SESSION['user_id'], $tutorProfileId, $subjectId, $date, $time, $note]);

  $tutorUserStmt = $conn->prepare("SELECT user_id FROM tutor_profiles WHERE id = ?");
  $tutorUserStmt->execute([$tutorProfileId]);
  $tutorUserId = $tutorUserStmt->fetch()['user_id'];
  createNotification($conn, $tutorUserId, "You have a new booking request from " . $_SESSION['user_name']);

  $message = "Booking request sent successfully!";
  $messageType = "success";
  $date = $time = $note = ''; // clear the form after a successful booking
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Book Tutor | Home Tutor</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

  <div class="auth-card wide">
    <h1>Book <?php echo htmlspecialchars($tutor['name']); ?></h1>
    <p class="subtitle">Send a booking request. The tutor will accept or reject it.</p>

    <?php if ($message != ''): ?>
      <div class="message <?php echo $messageType; ?>">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>

    <?php if (count($tutorSubjects) == 0): ?>
      <p class="empty-text">This tutor hasn't added any subjects yet, so they can't be booked right now.</p>
    <?php else: ?>

      <form method="POST" id="bookingForm" novalidate>
        <input type="hidden" name="tutor_id" value="<?php echo htmlspecialchars($tutorProfileId); ?>">

        <div class="form-group">
          <label for="subject_id">Subject</label>
          <select id="subject_id" name="subject_id" required>
            <?php foreach ($tutorSubjects as $subject): ?>
              <option value="<?php echo $subject['id']; ?>"
                <?php echo (($subjectId ?? '') == $subject['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($subject['name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
          <small class="error-text" id="subjectError"></small>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="preferred_date">Preferred date</label>
            <input type="date" id="preferred_date" name="preferred_date"
                   min="<?php echo date('Y-m-d'); ?>"
                   value="<?php echo htmlspecialchars($date ?? ''); ?>" required>
            <small class="error-text" id="dateError"></small>
          </div>

          <div class="form-group">
            <label for="preferred_time">Preferred time</label>
            <input type="time" id="preferred_time" name="preferred_time" min="09:00" max="18:00"
                   value="<?php echo htmlspecialchars($time ?? ''); ?>" required>
            <small class="error-text" id="timeError"></small>
          </div>
        </div>

        <div class="form-group">
          <label for="message">Message</label>
          <textarea id="message" name="message" rows="4"
                    placeholder="Tell the tutor what you want to learn" required><?php echo htmlspecialchars($note ?? ''); ?></textarea>
          <small class="error-text" id="messageError"></small>
        </div>

        <button type="submit" class="btn">Send booking request</button>
      </form>

    <?php endif; ?>

    <a class="btn-outline" href="search_tutors.php">Back to search</a>
  </div>

  <!-- Your JS goes here (create assets/js/book_tutor.js) -->
  <script src="../assets/js/book_tutor.js"></script>
</body>
</html>