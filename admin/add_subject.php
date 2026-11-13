<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';
requireRole('admin'); // Only allow admins to access this page

$message = '';
$messageType = ''; // "success" or "error"
$editId = null;    // id of the subject being updated ("update mode"), or null when adding a new one

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $name = trim($_POST['name']);
  $subjectId = $_POST['subject_id'] ?? ''; // filled only when the form is in update mode

  if ($name === '' || mb_strlen($name) > 50) {
    $message = "Subject name must be between 1 and 50 characters.";
    $messageType = "error";
    if ($subjectId !== '') $editId = $subjectId; // stay in update mode

  } elseif ($subjectId !== '') {
    // ---------- UPDATE an existing subject ----------
    $existsStmt = $conn->prepare("SELECT id FROM subjects WHERE id = ?");
    $existsStmt->execute([$subjectId]);

    if ($existsStmt->rowCount() == 0) {
      $message = "Subject not found.";
      $messageType = "error";
    } else {
      // Another subject (not this one) must not already have the same name
      $checkStmt = $conn->prepare("SELECT id FROM subjects WHERE name = ? AND id != ?");
      $checkStmt->execute([$name, $subjectId]);

      if ($checkStmt->rowCount() > 0) {
        $message = "Another subject already has this name.";
        $messageType = "error";
        $editId = $subjectId; // stay in update mode so the admin can fix the name
      } else {
        $stmt = $conn->prepare("UPDATE subjects SET name = ? WHERE id = ?");
        if ($stmt->execute([$name, $subjectId])) {
          $message = "Subject updated successfully!";
          $messageType = "success";
          $name = ''; // clear the input, back to "add" mode
        } else {
          $message = "Error updating subject.";
          $messageType = "error";
          $editId = $subjectId;
        }
      }
    }

  } else {
    // ---------- ADD a new subject ----------
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

} elseif (isset($_GET['edit'])) {
  // The admin clicked "Update" beside a subject: put its current name into the form
  $editStmt = $conn->prepare("SELECT id, name FROM subjects WHERE id = ?");
  $editStmt->execute([$_GET['edit']]);
  $editRow = $editStmt->fetch();
  if ($editRow) {
    $editId = $editRow['id'];
    $name = $editRow['name'];
  }
}

// In update mode, remember the saved name, so the JS can tell when nothing was changed
$editOriginal = '';
if ($editId !== null) {
  $origStmt = $conn->prepare("SELECT name FROM subjects WHERE id = ?");
  $origStmt->execute([$editId]);
  $editOriginal = $origStmt->fetchColumn() ?: '';
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
     <link rel="stylesheet" href="../assets/css/style.css?v=2">
</head>
<body class="dashboard-page">

  <?php $activePage = 'subjects'; include '../includes/admin_navbar.php'; ?>

  <main class="page-center">
  <div class="auth-card wide">
    <h1><?php echo $editId !== null ? 'Update subject' : 'Add subject'; ?></h1>
    <p class="subtitle">
      <?php if ($editId !== null): ?>
        Correct the name below. The change shows up everywhere the subject is used.
      <?php else: ?>
        Subjects added here can be chosen by tutors and students.
      <?php endif; ?>
    </p>

    <?php if ($message != ''): ?>
      <div class="message <?php echo $messageType; ?>">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>

    <form method="POST" id="addSubjectForm" novalidate>
      <?php if ($editId !== null): ?>
        <input type="hidden" name="subject_id" value="<?php echo htmlspecialchars($editId); ?>">
      <?php endif; ?>

      <div class="form-group">
        <label for="name">Subject name</label>
        <input type="text" id="name" name="name" placeholder="e.g. Mathematics"
               <?php if ($editId !== null): ?>data-original="<?php echo htmlspecialchars($editOriginal); ?>"<?php endif; ?>
               value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
        <small class="error-text" id="nameError"></small>
      </div>

      <button type="submit" class="btn"><?php echo $editId !== null ? 'Update subject' : 'Add subject'; ?></button>

      <?php if ($editId !== null): ?>
        <a class="btn-outline" href="add_subject.php">Cancel</a>
      <?php endif; ?>
    </form>

    <h2 class="section-title">All subjects (<?php echo count($subjects); ?>)</h2>

    <?php if (count($subjects) == 0): ?>
      <p class="empty-text">No subjects yet. Add the first one above.</p>
    <?php else: ?>
      <ul class="item-list" id="subjectList">
        <?php foreach ($subjects as $row): ?>
          <li data-id="<?php echo $row['id']; ?>"
              <?php if ($editId !== null && $row['id'] == $editId): ?>style="background:#dbeafe"<?php endif; ?>>
            <span class="item-id">#<?php echo $row['id']; ?></span>
            <span class="item-name"><?php echo htmlspecialchars($row['name']); ?></span>
            <?php if ($editId !== null && $row['id'] == $editId): ?>
              <span class="muted-text">Editing...</span>
            <?php else: ?>
              <a class="btn-small approve edit-link" href="?edit=<?php echo $row['id']; ?>">Update</a>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
  </main>

  <!-- Your JS goes here (create assets/js/add_subject.js) -->
  <script src="../assets/js/add_subject.js"></script>
</body>
</html>