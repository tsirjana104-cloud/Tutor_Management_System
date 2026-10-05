<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';
requireRole('tutor'); // Only allow tutors to access this page

$userId = $_SESSION['user_id'];

// Get this tutor's tutor_profiles.id
$stmt = $conn->prepare("SELECT id FROM tutor_profiles WHERE user_id = ?");
$stmt->execute([$userId]);
$tutorId = $stmt->fetch()['id'];

// Handle adding a new subject
if (isset($_POST['add_subject'])) {
  $subjectId = $_POST['subject_id'];

  // avoid duplicate assignment
  $check = $conn->prepare("SELECT id FROM tutor_subjects WHERE tutor_id = ? AND subject_id = ?");
  $check->execute([$tutorId, $subjectId]);

  if ($check->rowCount() == 0) {
    $stmt = $conn->prepare("INSERT INTO tutor_subjects (tutor_id, subject_id) VALUES (?, ?)");
    $stmt->execute([$tutorId, $subjectId]);
  }
}

// Handle removing a subject
if (isset($_GET['remove'])) {
  $removeId = $_GET['remove'];
  $delete = $conn->prepare("DELETE FROM tutor_subjects WHERE id = ? AND tutor_id = ?");
  $delete->execute([$removeId, $tutorId]);
  header("Location: my_subjects.php"); // Redirect to avoid resubmission
  exit();
}


// Fetch subjects for dropdown
$allSubjects = $conn->query("SELECT * FROM subjects")->fetchAll();

// Fetch subjects this tutor currently teaches
$myStmt = $conn->prepare("SELECT ts.id as tutor_subject_id, s.name FROM tutor_subjects ts JOIN subjects s ON ts.subject_id = s.id WHERE ts.tutor_id = ?");
$myStmt->execute([$tutorId]);
$mySubjects = $myStmt->fetchAll();
?>

<h2>My Subjects</h2>

<form method="POST">
  <select name="subject_id" required>
    <option value="">-- Select Subject --</option>
    <?php foreach ($allSubjects as $subject): ?>
      <option value="<?php echo $subject['id']; ?>"><?php echo htmlspecialchars($subject['name']); ?></option>
    <?php endforeach; ?>
    </select>
    <button type="submit" name="add_subject">Add Subject</button>
  </form>

  <hr>

  <h3>Subjects I Currently Teach</h3>
  <ul>
    <?php foreach ($mySubjects as $subject): ?>
      <li>
        <?php echo htmlspecialchars($subject['name']); ?>
        (<a href="?remove=<?php echo $subject['tutor_subject_id']; ?>" onclick="return confirm('Are you sure you want to remove this subject?');">Remove</a>)
      </li>
    <?php endforeach; ?>
  </ul>

  <br>
  <a href="profile.php">Back to Profile</a>