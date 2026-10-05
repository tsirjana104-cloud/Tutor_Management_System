<!-- // This is reusable guard so only logged-in admins can access admin pages. Same for tutors and students. 

<?php
// Call this function at the top of any page that requires authentication and role-based access control
// Usage: require_once '../includes/auth_check.php'; call like this requireRole('admin'); // Only allow admins to access this page

function requireRole($allowedRole) {
  if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== $allowedRole) {
    // Redirect to login page if not logged in or role doesn't match
    header("Location: ../auth/login.php");
    exit();
  }
}
?> -->