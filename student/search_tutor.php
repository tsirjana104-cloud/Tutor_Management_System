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
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Search Tutors | Home Tutor</title>
  <link rel="stylesheet" href="../assets/css/style.css">
     <link rel="stylesheet" href="../assets/css/style.css?v=2">
</head>
<body class="dashboard-page">

  <?php $activePage = 'search'; include '../includes/student_navbar.php'; ?>

  <main class="page-center">
  <div class="auth-card wider">
    <h1>Search tutors</h1>
    <p class="subtitle">Find an approved tutor by subject or location.</p>

    <form method="GET" id="searchForm" class="filter-form">
      <div class="form-group">
        <label for="subject_id">Subject</label>
        <select id="subject_id" name="subject_id">
          <option value="">-- All Subjects --</option>
          <?php foreach ($allSubjects as $subject): ?>
            <option value="<?php echo $subject['id']; ?>" <?php echo ($subjectFilter == $subject['id']) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($subject['name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="location">Location</label>
        <input type="text" id="location" name="location" placeholder="e.g. KTM"
               value="<?php echo htmlspecialchars($locationFilter); ?>">
      </div>

      <div class="form-group filter-buttons">
        <button type="submit" class="btn">Search</button>
        <a href="search_tutors.php" class="btn-outline" id="clearFilters">Clear</a>
      </div>
    </form>

    <h2 class="section-title">Results (<?php echo count($tutors); ?>)</h2>

    <?php if (count($tutors) == 0): ?>
      <p class="empty-text">No tutors found matching your criteria.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table class="data-table" id="tutorTable">
          <thead>
            <tr>
              <th>Name</th>
              <th>Location</th>
              <th>Qualification</th>
              <th>Experience (years)</th>
              <th>Hourly rate</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($tutors as $tutor): ?>
              <tr>
                <td><?php echo htmlspecialchars($tutor['name']); ?></td>
                <td><?php echo htmlspecialchars($tutor['location']); ?></td>
                <td><?php echo htmlspecialchars($tutor['qualification']); ?></td>
                <td><?php echo htmlspecialchars($tutor['experience_years']); ?></td>
                <td>Rs. <?php echo htmlspecialchars($tutor['hourly_rate']); ?></td>
                <td>
                  <a class="btn-small approve" href="book_tutor.php?tutor_id=<?php echo $tutor['tutor_profile_id']; ?>">Book</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
  </main>

  <!-- Your JS goes here (create assets/js/search_tutors.js) -->
  <script src="../assets/js/search_tutors.js"></script>
</body>
</html>