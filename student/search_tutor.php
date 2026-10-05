<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';
requireRole('student'); // Only allow students to access this page

$subjectFilter = $_GET['subject_id'] ?? '';
$locationFilter = $_GET['location'] ?? '';

// Base query: only approved tutors
$sql = "SELECT DISTINCT tp.id as tutor_profile_id, u.name, u.email, u.location, tp.qualification, tp.experience_years, tp.hourly_rate
        FROM tutor_profiles tp
        JOIN users u ON tp.user_id = u.id
        LEFT JOIN tutor_subjects ts ON tp.id = ts.tutor_id
        WHERE tp.status = 'approved'";

$params = [];

if (!empty($subjectFilter)) {
  $sql .= " AND ts.subject_id = ?";
  $params[] = $subjectFilter;
}

if (!empty($locationFilter)) {
  $sql .= " AND u.location LIKE ?";
  $params[] = "%$locationFilter%";
}

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$tutors = $stmt->fetchAll();


// For the filter dropdown
$allSubjects = $conn->query("SELECT * FROM subjects ORDER BY name")->fetchAll();
?>

<h2>Search Tutors</h2>

<form method="GET">
  Subject:
  <select name="subject_id">
    <option value="">-- All Subjects --</option>
    <?php foreach ($allSubjects as $subject): ?>
      <option value="<?php echo $subject['id']; ?>" <?php echo ($subjectFilter == $subject['id']) ? 'selected' : ''; ?>>
        <?php echo ($subject['name']); ?>

      </option>
    <?php endforeach; ?>
  </select>

  Location:
  <input type="text" name="location" value="<?php echo htmlspecialchars($locationFilter); ?>" placeholder="e.g. KTM">

  <button type="submit">Search</button>
</form>

<hr>

<?php if (count($tutors) == 0): ?>
  <p>No tutors found matching your criteria.</p>
<?php else: ?>
  <table border="1" cellpadding="8">
    <tr>
      <th>Name</th>
      <th>Location</th>
      <th>Qualification</th>
      <th>Experience (years)</th>
      <th>Hourly Rate (Rs.)</th>
      <th>Actions</th>
    </tr>
    <?php foreach ($tutors as $tutor): ?>
      <tr>
        <td><?php echo htmlspecialchars($tutor['name']); ?></td>
        <td><?php echo htmlspecialchars($tutor['location']); ?></td>
        <td><?php echo htmlspecialchars($tutor['qualification']); ?></td>
        <td><?php echo $tutor['experience_years']; ?></td>
        <td>Rs. <?php echo $tutor['hourly_rate']; ?></td>
        <td><a href="book_tutor.php?tutor_id=<?php echo $tutor['tutor_profile_id']; ?>">Book</a></td>
      </tr>
      <?php endforeach; ?>
  </table>
<?php endif; ?>