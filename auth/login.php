<?php
require_once '../includes/config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $email = trim($_POST['email']);
  $password = $_POST['password'];

  // Find user by email
  $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
  $stmt->execute([$email]);
  $user = $stmt->fetch();

  if ($user && password_verify($password, $user['password'])) {
    // Password matches then store info in session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_role'] = $user['role'];

    // Redirect based on role
    if ($user['role'] == 'admin') {
      header("Location: ../admin/dashboard.php");
    } elseif ($user['role'] == 'tutor') {
      header("Location: ../tutor/dashboard.php");
    } else {
      header("Location: ../student/dashboard.php");
    }
    exit();
  } else {
    $message = "Invalid email or password.";
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | Home Tutor</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

  <div class="auth-card">
    <h1>Welcome back</h1>
    <p class="subtitle">Log in to your account.</p>

    <?php if ($message != ''): ?>
      <div class="message error">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>

    <form method="POST" id="loginForm" novalidate>

      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="you@example.com"
               value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
        <small class="error-text" id="emailError"></small>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Your password" required>
        <small class="error-text" id="passwordError"></small>
      </div>

      <button type="submit" class="btn">Log in</button>
    </form>

    <p class="switch-link">New here? <a href="register.php">Create an account</a></p>
  </div>

  <!-- Your JS goes here (create js/login.js) -->
  <script src="../assets/js/login.js"></script>
</body>
</html>