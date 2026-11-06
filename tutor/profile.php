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

  $updateStmt = $conn->prepare("UPDATE tutor_profiles SET bio = ?, qualification = ?, experience_years =  ?, hourly_rate = ? WHERE user_id = ?");
  $updateStmt->execute([$bio, $qualification, $experience, $rate, $userId]);

  $message = "Profile updated successfully!";

  //refresh $tutor with new dataset
  $stmt->execute([$userId]);
  $tutor = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile | Home Tutor</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

  <div class="auth-card wide">
    <h1>My profile</h1>
    <p class="subtitle">
      Status:
      <span class="badge <?php echo htmlspecialchars($tutor['status'] ?? ''); ?>">
        <?php echo htmlspecialchars($tutor['status'] ?? ''); ?>
      </span>
    </p>

    <?php if ($message != ''): ?>
      <div class="message success">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>

    <form method="POST" id="profileForm" novalidate>

      <div class="form-group">
        <label for="bio">Bio</label>
        <textarea id="bio" name="bio" rows="4"
                  placeholder="Tell students a little about yourself and how you teach"><?php echo htmlspecialchars($tutor['bio'] ?? ''); ?></textarea>
        <small class="error-text" id="bioError"></small>
      </div>

      <div class="form-group">
        <label for="qualification">Qualification</label>
        <input type="text" id="qualification" name="qualification" placeholder="e.g. BSc Mathematics"
               value="<?php echo htmlspecialchars($tutor['qualification'] ?? ''); ?>">
        <small class="error-text" id="qualificationError"></small>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="experience_years">Experience (years)</label>
          <input type="number" id="experience_years" name="experience_years" min="0" placeholder="0"
                 value="<?php echo htmlspecialchars($tutor['experience_years'] ?? ''); ?>">
          <small class="error-text" id="experienceError"></small>
        </div>

        <div class="form-group">
          <label for="hourly_rate">Hourly rate (Rs.)</label>
          <input type="number" id="hourly_rate" name="hourly_rate" min="0" placeholder="500"
                 value="<?php echo htmlspecialchars($tutor['hourly_rate'] ?? ''); ?>">
          <small class="error-text" id="rateError"></small>
        </div>
      </div>

      <button type="submit" class="btn">Save profile</button>
    </form>

    <a class="btn-outline" href="my_subjects.php">Manage subjects I teach</a>
  </div>

  <!-- Your JS goes here (create assets/js/profile.js) -->
  <script src="../assets/js/profile.js"></script>
</body>
</html>