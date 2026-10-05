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

<h3><?php echo $message; ?></h3>

<form method="POST">
  Email: <input type="email" name="email" required><br><br>
  Password: <input type="password" name="password" required><br><br>
  <button type="submit">Login</button>
</form>