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
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Subjects | Home Tutor</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-page">

  <?php $activePage = 'subjects'; include '../includes/tutor_navbar.php'; ?>

  <main class="page-center">
  <div class="auth-card wide">
    <h1>My subjects</h1>
    <p class="subtitle">Choose the subjects you teach.</p>

    <form method="POST" id="mySubjectForm" novalidate>
      <div class="form-group">
        <label for="subject_id">Add a subject</label>
        <div class="inline-form">
          <select id="subject_id" name="subject_id" required>
            <option value="">-- Select Subject --</option>
            <?php foreach ($allSubjects as $subject): ?>
              <option value="<?php echo $subject['id']; ?>"><?php echo htmlspecialchars($subject['name']); ?></option>
            <?php endforeach; ?>
          </select>
          <button type="submit" name="add_subject" class="btn">Add</button>
        </div>
        <small class="error-text" id="subjectError"></small>
      </div>
    </form>

    <h2 class="section-title">Subjects I currently teach (<?php echo count($mySubjects); ?>)</h2>

    <?php if (count($mySubjects) == 0): ?>
      <p class="empty-text">You haven't added any subjects yet.</p>
    <?php else: ?>
      <ul class="item-list" id="mySubjectList">
        <?php foreach ($mySubjects as $subject): ?>
          <li>
            <span class="item-name"><?php echo htmlspecialchars($subject['name']); ?></span>
            <a class="btn-small reject remove-link"
               href="?remove=<?php echo $subject['tutor_subject_id']; ?>">Remove</a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <a class="btn-outline" href="profile.php">Back to profile</a>
  </div>
  </main>

  <!-- Your JS goes here (create assets/js/my_subjects.js) -->
  <script src="../assets/js/confirm_modal.js"></script>
  <script src="../assets/js/my_subjects.js"></script>
</body>
</html>