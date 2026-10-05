<?php
require_once '../includes/config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $name = trim($_POST['name']);

  // Check if the subject already exists
  $checkStmt = $conn->prepare("SELECT id FROM subjects WHERE name = ?");
  $checkStmt->execute([$name]);

  if ($checkStmt->rowCount() > 0) {
    $message = "Subject already exists.";
  } else {
    // Insert the new subject into the database using prepared statements
    $stmt = $conn->prepare("INSERT INTO subjects (name) VALUES (?)");
    if ($stmt->execute([$name])) {
      $message = "Subject added successfully!";
    } else {
      $message = "Error adding subject.";
    }
  }
}
?>

<h3><?php echo $message; ?></h3>

<form method="POST">
  Subject Name: <input type="text" name ="name" required>
  <button type="submit">Add Subject</button>
</form>

<hr>

<h3>All subjects</h3>
<?php
$stmt = $conn->query("SELECT * FROM subjects ORDER BY id DESC");
$subjects = $stmt->fetchAll();
foreach ($subjects as $row) {
  echo $row['id'] . " - " . $row['name'] . "<br>";
}
?>