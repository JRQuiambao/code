<?php
require 'config.php';
require 'auth.php';


// Check user role and redirect accordingly
if ($_SESSION['role'] == 1) { // Role 1: Teacher
    $dashboard_link = '<p><a href="teacher_dashboard.php">Back to Dashboard</a></p>';
} elseif ($_SESSION['role'] == 2) { // Role 2: Student
    $dashboard_link = '<p><a href="student_dashboard.php">Back to Dashboard</a></p>';
} else {
    die("Access denied: Invalid user role.");
}

$class_id = $_GET['class_id'];

// Fetch the class details
$stmt = $pdo->prepare("SELECT class_subject, class_section FROM class_tbl WHERE class_id = ?");
$stmt->execute([$class_id]);
$class = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$class) {
    die("Class not found.");
}

// Fetch students who joined this class, including student_id for linking
$stmt = $pdo->prepare("
    SELECT s.student_id, CONCAT(s.student_first_name, ' ', s.student_last_name) AS full_name, s.username
    FROM student_classes sc
    JOIN student_tbl s ON sc.student_id = s.student_id
    WHERE sc.class_id = ?
");
$stmt->execute([$class_id]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class List for <?php echo htmlspecialchars($class['class_subject']); ?></title>
    <link rel="stylesheet" href="css/classlist.css">
</head>
<body>

<div class="sidebar">
    <div class="logo-container">
        <img src="images/logo/pq_logo_white.webp" class="logo" alt="PeerQuest Logo">
        <img src="images/logo/pq_white_logo_txt.webp" class="logo-text" alt="PeerQuest Logo Text">
    </div>

        <ul class="nav-links">
            <li><a href="teacher_dashboard.php"><img src="images/Home_white_icon.png" alt="Dashboard"> <span>Dashboard</span></a></li>
        </ul>

        <div class="logout">
            <a href="logout.php" class="logout-btn">
                <img src="images/logout_white_icon.png" alt="Logout"> <span>LOG OUT</span>
            </a>
        </div>
    </div>

    <button class="toggle-btn" onclick="toggleSidebar()">
        <img src="images/sidebar_close_icon.png" id="toggleIcon" alt="Toggle Sidebar">
    </button>


<div class="content">
<div class="top-bar">
            <h1 class="dashboard-title">Class List</h1>
            <p class="current-date" id="currentDate"></p>
</div>
    <h2>CLASS: <span class="highlight"><?php echo htmlspecialchars($class['class_subject']); ?> - <?php echo htmlspecialchars($class['class_section']); ?></span></h2>

    <!-- View Leaderboards -->
    <div class="leaderboard-section">
        <form action="leaderboards.php" method="get">
            <input type="hidden" name="class_id" value="<?php echo htmlspecialchars($class_id); ?>">
            <label for="assessment_id">📊 View Leaderboards:</label>
            <select name="assessment_id" id="assessment_id" required>
                <option value="" disabled selected>Select Assessment</option>
                <?php
                // Fetch assessments for this class
                $stmt = $pdo->prepare("SELECT assessment_id, name FROM assessment_tbl WHERE class_id = ?");
                $stmt->execute([$class_id]);
                $assessments = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($assessments as $assessment): ?>
                    <option value="<?php echo htmlspecialchars($assessment['assessment_id']); ?>">
                        <?php echo htmlspecialchars($assessment['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="go-btn">Go</button>
        </form>
    </div>

    <!-- Student List -->
    <?php if (count($students) > 0): ?>
        <ul class="student-list">
            <?php foreach ($students as $student): ?>
                <li>
                    <span class="student-name"><?php echo htmlspecialchars($student['full_name']); ?> (<?php echo htmlspecialchars($student['username']); ?>)</span>

                    <div class="actions">
                        <a href="status.php?student_id=<?php echo $student['student_id']; ?>&class_id=<?php echo $class_id; ?>" class="btn status">📄 Status</a>
                        <a href="achievements.php?student_id=<?php echo $student['student_id']; ?>" class="btn achievements">🏅 Achievements</a>
                        <a href="student_profile.php?student_id=<?php echo $student['student_id']; ?>&class_id=<?php echo $class_id; ?>" class="btn profile">👤 View Profile</a>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="no-students">🚀 No students have joined this class yet.</p>
    <?php endif; ?>
</div>

<script>
 function confirmDelete(class_id) {
                if (confirm("Are you sure you want to delete this class/subject? All data including student's activity will be deleted.")) {
                    window.location.href = 'teacher_dashboard.php?delete=' + class_id;
                }
            }
            function copyToClipboard(text) {
                navigator.clipboard.writeText(text).then(function() {
                    alert('Copied to clipboard: ' + text);
                }).catch(function(err) {
                    alert('Failed to copy: ' + err);
                });
            }

            function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('collapsed');
    document.querySelector('.content').classList.toggle('expanded');
    document.querySelector('.top-bar').classList.toggle('expanded');
    const toggleIcon = document.getElementById('toggleIcon');
            if (document.querySelector('.sidebar').classList.contains('collapsed')) {
                toggleIcon.src = "images/sidebar_open_icon.png";
            } else {
                toggleIcon.src = "images/sidebar_close_icon.png";
            }
        }

        
        function updateDate() {
            const options = { weekday: 'long', month: 'long', day: 'numeric' };
            const currentDate = new Date().toLocaleDateString('en-PH', options);
            document.getElementById('currentDate').textContent = currentDate;
        }
        updateDate();</script>

</body>
</html>
