<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';
requireRole('tutor'); // Only allow tutors to access this page

$userId = $_SESSION['user_id'];

// Get this tutor's tutor_profile row
$stmt = $conn->prepare("SELECT * FROM tutor_profiles WHERE user_id = ?");
$stmt->execute([$userId]);
$tutor = $stmt->fetch();

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $bio = trim($_POST['bio']);
  $qualification = trim($_POST['qualification']);
  $experience = trim($_POST['experience_years']);
  $rate = $_POST['hourly_rate'];

  $updateStmt = $conn->prepare("UPDATE tutor_profiles SET bio = ?, qualification = ?, experience_years = ?, hourly_rate = ? WHERE user_id = ?");
  $updateStmt->execute([$bio, $qualification, $experience, $rate, $userId]);

  $message = "Profile updated successfully!";

  //refresh $tutor with new dataset
  $stmt->execute([$userId]);
  $tutor = $stmt->fetch();
}
?>

<h2>My Profile</h2>
<p>Status: <strong><?php echo $tutor['status']; ?></strong></p>
<h3><?php echo $message; ?></h3>

<form method="POST">
  Bio:<br>
  <textarea name="bio"><?php echo htmlspecialchars($tutor['bio']); ?></textarea><br><br>

  Qualification:<br>
  <input type="text" name="qualification" value="<?php echo htmlspecialchars($tutor['qualification']); ?>"><br><br>

  Experience (years):<br>
  <input type="number" name="experience_years" value="<?php echo htmlspecialchars($tutor['experience_years']); ?>"><br><br>

  Hourly Rate (Rs.):<br>
  <input type="number" name="hourly_rate" value="<?php echo htmlspecialchars($tutor['hourly_rate']); ?>"><br><br>

  <button type="submit">Save Profile</button>
</form>

<br>
<a href="my_subjects.php">Manage Subjects I teach</a>