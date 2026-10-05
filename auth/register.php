<?php
require_once '../includes/config.php';

$message = '';

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
    echo "Email already registered. Try logging in instead.";
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
      $tutorStmt = $conn->prepare("
      INSERT INTO tutor_profiles (user_id) VALUES (?)");
      $tutorStmt->execute([$userId]);
    }

    $message = "Registration successful! Your User ID is: " . $userId;
  }
}
?>

<h3><?php echo $message; ?></h3>

<form method="POST">
  Name: <input type="text" name="name" required><br><br>
  Email: <input type="email" name="email" required><br><br>
  Password: <input type="password" name="password" required><br><br>
  Phone: <input type="text" name="phone"><br><br>
  Location: <input type="text" name="location"><br><br>
  Role:
  <select name="role" required>
    <option value="">Select Role</option>
    <option value="student">Student</option>
    <option value="tutor">Tutor</option>
  </select><br><br>
  <button type="submit">Register</button>
</form>