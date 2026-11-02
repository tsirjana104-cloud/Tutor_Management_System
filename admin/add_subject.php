<?php
require_once '../includes/config.php';

$message = '';
$messageType = ''; // "success" or "error"

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $name = trim($_POST['name']);

  // Check if the subject already exists
  $checkStmt = $conn->prepare("SELECT id FROM subjects WHERE name = ?");
  $checkStmt->execute([$name]);

  if ($checkStmt->rowCount() > 0) {
    $message = "Subject already exists.";
    $messageType = "error";
  } else {
    // Insert the new subject into the database using prepared statements
    $stmt = $conn->prepare("INSERT INTO subjects (name) VALUES (?)");
    if ($stmt->execute([$name])) {
      $message = "Subject added successfully!";
      $messageType = "success";
      $name = ''; // clear the input after a successful add
    } else {
      $message = "Error adding subject.";
      $messageType = "error";
    }
  }
}

// Get all subjects for the list
$stmt = $conn->query("SELECT * FROM subjects ORDER BY id DESC");
$subjects = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Subject | Home Tutor</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

  <div class="auth-card wide">
    <h1>Add subject</h1>
    <p class="subtitle">Subjects added here can be chosen by tutors and students.</p>

    <?php if ($message != ''): ?>
      <div class="message <?php echo $messageType; ?>">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>

    <form method="POST" id="addSubjectForm" novalidate>
      <div class="form-group">
        <label for="name">Subject name</label>
        <input type="text" id="name" name="name" placeholder="e.g. Mathematics"
               value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
        <small class="error-text" id="nameError"></small>
      </div>

      <button type="submit" class="btn">Add subject</button>
    </form>

    <h2 class="section-title">All subjects (<?php echo count($subjects); ?>)</h2>

    <?php if (count($subjects) == 0): ?>
      <p class="empty-text">No subjects yet. Add the first one above.</p>
    <?php else: ?>
      <ul class="item-list" id="subjectList">
        <?php foreach ($subjects as $row): ?>
          <li>
            <span class="item-id">#<?php echo $row['id']; ?></span>
            <span class="item-name"><?php echo htmlspecialchars($row['name']); ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>

  <!-- Your JS goes here (create assets/js/add_subject.js) -->
  <script src="../assets/js/add_subject.js"></script>
</body>
</html>