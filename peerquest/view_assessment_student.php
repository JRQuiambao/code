<?php
require 'auth.php';
require 'config.php';

// Check if the user is a student
if ($_SESSION['role'] != 2) {
    echo "Access denied: Students only.";
    exit();
}

// Get class_id from session or URL
$class_id = $_SESSION['class_id'] ?? $_GET['class_id'] ?? null;

if (!$class_id) {
    echo "Invalid or missing class ID.";
    exit();
}

// Fetch the student_id from the session (or database if not available in the session)
$student_id = $_SESSION['student_id'] ?? null;

if (!$student_id) {
    // Attempt to fetch student_id from the database
    $stmt = $pdo->prepare("SELECT student_id FROM student_tbl WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($student) {
        $student_id = $student['student_id'];
    } else {
        echo "Student not found.";
        exit();
    }
}

// Fetch published assessments for the specific class
$stmt = $pdo->prepare("SELECT * FROM assessment_tbl WHERE status = 'Published' AND class_id = ?");
$stmt->execute([$class_id]);
$assessments = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getTotalPointsForMC($pdo, $assessment_id) {
    $stmt = $pdo->prepare("SELECT SUM(points) AS total_points FROM questions_mcq_tbl WHERE assessment_id = ?");
    $stmt->execute([$assessment_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total_points'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Assessments</title>
    <link rel="stylesheet" href="css/student_assessment.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>

<div class="sidebar">
    <div class="logo-container">
        <img src="images/pq_logo_white.webp" class="logo" alt="PeerQuest Logo">
        <img src="images/pq_white_logo_txt.webp" class="logo-text" alt="PeerQuest Logo Text">
        
    </div>

        <ul class="nav-links">
            <li><a href="profile.php"><img src="images/account_white_icon.png" alt="Profile"> <span>Profile</span></a></li>
            <li><a href="student_dashboard.php"><img src="images/Home_white_icon.png" alt="Dashboard"> <span>Dashboard</span></a></li>
            <li><a href="achievements.php?student_id=<?php echo $student_id; ?>"><img src="images/achievements_white_icon.png" alt="Achievements"> <span>Achievements</span></a></li>
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
                <h1 class="dashboard-title">Available Assessments</h1>
                <p class="current-date" id="currentDate"></p>
    </div>

    <?php if (empty($assessments)): ?>
    <div class="no-assessment-container">
        <img src="images/C_asleep.gif" alt="Charubelle Sleeping" class="charubelle-gif">
        <h3 class="no-assessment-message">No assessment, Charaubelle is sleeping. Rest well, player!</h3>
    </div>
<?php else: ?>
    <div class="assessment-list">
    <?php foreach ($assessments as $assessment): ?>
        <div class="assessment-card">
            <div class="assessment-image">
                <img src="images/assessment_essay.svg" alt="Assessment Image">
            </div>
            <div class="assessment-details">
                <h3 class="assessment-title"><?php echo htmlspecialchars($assessment['name']); ?></h3>
                <span class="assessment-type"> 
                    <strong>Assessment Type:</strong> </span>
                    <span class="assessment-mode"><?php echo strtoupper($assessment['type']); ?></span>
                </p>
                <div class="assessment-meta">
                    <div class="meta-item">
                        <img src="images/assessment_clock.svg" alt="Time Icon">
                        <span><?php echo $assessment['time_limit']; ?> minutes</span>
                    </div>
                    <div class="meta-item">
                        <img src="images/assessment_points.svg" alt="Points Icon">
                        <span><?php echo $assessment['total_points']; ?> /  <?php echo $assessment['total_points']; ?> Points</span>
                    </div>
                </div>
            </div>
            <div class="assessment-action">
                <a href="take_assessment.php?assessment_id=<?php echo $assessment['assessment_id']; ?>" class="take-assessment-btn">Take Assessment</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php endif; ?>

<script>

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
        updateDate();
    </script>

</body>
</html>
