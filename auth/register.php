<?php
require_once '../includes/config.php';

$message = '';
$messageType = ''; // "success" or "error" (used for the message box colour)

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  $phone = trim($_POST['phone']);
  $location = trim($_POST['location']);
  $role = $_POST['role'];

  // Check if the email already exists
  $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
  $checkStmt->execute([$email]);

  if ($checkStmt->rowCount() > 0) {
    $message = "Email already registered. Try logging in instead.";
    $messageType = "error";
  } else {
    // Hashing the password before inserting into the database
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $insertStmt = $conn->prepare(
      "INSERT INTO users (name, email, password, phone, location, role) VALUES (?, ?, ?, ?, ?, ?)"
    );
    $insertStmt->execute([$name, $email, $hashedPassword, $phone, $location, $role]);

    $userId = $conn->lastInsertId();

    // If role is tutor, also create a row in tutor_profiles
    if ($role == 'tutor') {
      $tutorStmt = $conn->prepare("INSERT INTO tutor_profiles (user_id) VALUES (?)");
      $tutorStmt->execute([$userId]);
    }

    $message = "Registration successful! Your User ID is: " . $userId;
    $messageType = "success";
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register | Home Tutor</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

  <div class="auth-card">
    <h1>Create your account</h1>
    <p class="subtitle">Join as a student or a tutor.</p>

    <?php if ($message != ''): ?>
      <div class="message <?php echo $messageType; ?>">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>

    <form method="POST" id="registerForm" novalidate>

      <div class="form-group">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" placeholder="Your full name"
               value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
        <small class="error-text" id="nameError"></small>
      </div>

      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="you@example.com"
               value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
        <small class="error-text" id="emailError"></small>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="At least 6 characters" required>
        <small class="error-text" id="passwordError"></small>
      </div>

      <div class="form-group">
        <label for="phone">Phone</label>
        <input type="text" id="phone" name="phone" placeholder="98XXXXXXXX"
               value="<?php echo htmlspecialchars($phone ?? ''); ?>">
        <small class="error-text" id="phoneError"></small>
      </div>

      <div class="form-group">
        <label for="location">Location</label>
        <input type="text" id="location" name="location" placeholder="City or area"
               value="<?php echo htmlspecialchars($location ?? ''); ?>">
      </div>

      <div class="form-group">
        <label for="role">I am a</label>
        <select id="role" name="role" required>
          <option value="">Select role</option>
          <option value="student" <?php echo (($role ?? '') == 'student') ? 'selected' : ''; ?>>Student</option>
          <option value="tutor" <?php echo (($role ?? '') == 'tutor') ? 'selected' : ''; ?>>Tutor</option>
        </select>
        <small class="error-text" id="roleError"></small>
      </div>

      <button type="submit" class="btn">Register</button>
    </form>

    <p class="switch-link">Already have an account? <a href="login.php">Log in</a></p>
  </div>

  <!-- Your JS goes here (create js/register.js) -->
  <script src="../js/register.js"></script>
</body>
</html>