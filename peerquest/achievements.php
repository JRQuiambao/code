<?php
require 'auth.php';
require 'config.php';

// Get student_id from query parameter (e.g., achievements.php?student_id=123)
if (!isset($_GET['student_id'])) {
    echo "Student ID is missing.";
    exit();
}

$student_id = $_GET['student_id'];

// Fetch the student's username
$stmt = $pdo->prepare("SELECT username FROM student_tbl WHERE student_id = ?");
$stmt->execute([$student_id]);
$username = $stmt->fetchColumn();

// Fetch achievements data for the student
$stmt = $pdo->prepare("SELECT ach_streak, ach_modules_read, ach_answered_assessments, ach_collaborated FROM student_tbl WHERE student_id = ?");
$stmt->execute([$student_id]);
$achievements = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$achievements) {
    echo "No achievement data found for the selected student.";
    exit();
}

// Check and award badges based on achievements
$badge_awarded = false;
$badge_message = '';

// Example: Award a badge for streaks over 5 days
if ($achievements['ach_streak'] >= 5) {
    $stmt = $pdo->prepare("SELECT * FROM achievement_tbl WHERE student_id = ? AND badge_name = ?");
    $stmt->execute([$student_id, '5-Day Streak']);
    $existingBadge = $stmt->fetch();

    if (!$existingBadge) {
        $stmt = $pdo->prepare("INSERT INTO achievement_tbl (student_id, badge_name, badge_earned_date) VALUES (?, ?, ?)");
        $stmt->execute([$student_id, '5-Day Streak', date('Y-m-d')]);
        $badge_awarded = true;
        $badge_message = 'Congratulations! You have earned the 5-Day Streak badge!';
    }
}

// Example: Award a badge for reading over 10 modules
if ($achievements['ach_modules_read'] >= 10) {
    $stmt = $pdo->prepare("SELECT * FROM achievement_tbl WHERE student_id = ? AND badge_name = ?");
    $stmt->execute([$student_id, '10 Modules Master']);
    $existingBadge = $stmt->fetch();

    if (!$existingBadge) {
        $stmt = $pdo->prepare("INSERT INTO achievement_tbl (student_id, badge_name, badge_earned_date) VALUES (?, ?, ?)");
        $stmt->execute([$student_id, '10 Modules Master', date('Y-m-d')]);
        $badge_awarded = true;
        $badge_message = 'Congratulations! You have earned the 10 Modules Master badge!';
    }
}

// Example: Award a badge for answering over 20 assessments
if ($achievements['ach_answered_assessments'] >= 5) {
    $stmt = $pdo->prepare("SELECT * FROM achievement_tbl WHERE student_id = ? AND badge_name = ?");
    $stmt->execute([$student_id, 'Assessment Beginner']);
    $existingBadge = $stmt->fetch();

    if (!$existingBadge) {
        $stmt = $pdo->prepare("INSERT INTO achievement_tbl (student_id, badge_name, badge_earned_date) VALUES (?, ?, ?)");
        $stmt->execute([$student_id, 'Assessment Beginner', date('Y-m-d')]);
        $badge_awarded = true;
        $badge_message = 'Congratulations! You have earned the Assessment Beginner badge!';
    }
}

// Example: Award a badge for collaborating over 5 times
if ($achievements['ach_collaborated'] >= 5) {
    $stmt = $pdo->prepare("SELECT * FROM achievement_tbl WHERE student_id = ? AND badge_name = ?");
    $stmt->execute([$student_id, 'Collaboration Novice']);
    $existingBadge = $stmt->fetch();

    if (!$existingBadge) {
        $stmt = $pdo->prepare("INSERT INTO achievement_tbl (student_id, badge_name, badge_earned_date) VALUES (?, ?, ?)");
        $stmt->execute([$student_id, 'Collaboration Novice', date('Y-m-d')]);
        $badge_awarded = true;
        $badge_message = 'Congratulations! You have earned the Collaboration Novice badge!';
    }
}

// Fetch all badges earned by the student
$stmt = $pdo->prepare("SELECT badge_name, badge_image, badge_earned_date FROM achievement_tbl WHERE student_id = ?");
$stmt->execute([$student_id]);
$earned_badges = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Achievements</title>
    <link rel="stylesheet" href="css/achievement.css">
</head>
<body>

<div class="sidebar">
    <div class="logo-container">
        <img src="images/logo/pq_logo_white.webp" class="logo" alt="PeerQuest Logo">
        <img src="images/logo/pq_white_logo_txt.webp" class="logo-text" alt="PeerQuest Logo Text">
        
    </div>

    <ul class="nav-links"> 
        <?php if ($_SESSION['role'] == 2): ?> 
            <!-- Navigation links for students --> 
            <li><a href="profile.php"><img src="images/account_white_icon.png" alt="Profile"> <span>Profile</span></a></li> 
            <li><a href="student_dashboard.php"><img src="images/Home_white_icon.png" alt="Dashboard"> <span>Dashboard</span></a></li> 
            <li><a href="achievements.php?student_id=<?php echo $_SESSION['student_id']; ?>"><img src="images/achievements_white_icon.png" alt="Achievements"> <span>Achievements</span></a></li> 
        <?php elseif ($_SESSION['role'] == 1): ?> 
            <!-- Navigation links for teachers --> 
            <li><a href="teacher_dashboard.php"><img src="images/Home_white_icon.png" alt="Dashboard"> <span>Dashboard</span></a></li> 
        <?php endif; ?> 
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
                <h1 class="dashboard-title">Achievements</h1>
                <p class="current-date" id="currentDate"></p>
    </div>
    
    <div class="welcome-section">
        <img src="images/C_eyesmile.gif" alt="Welcome Owl">
        <div class="welcome-box">
            <p class="welcome-text">This is your achievements page, <strong><?php echo htmlspecialchars($_SESSION['username']); ?>!</strong></p>
            <p class="welcome-subtext"> a reflection of your dedication and progress! Every milestone here is proof of how far you’ve come—keep pushing forward, you’re doing amazing!</p>
        </div>
    </div>


    <div class="achievement-container">
    <div class="achievement-card streak">
        <div class="achievement-content">
            <span class="emoji">🔥</span>
            <h3>Streak</h3>
            <p><span class="highlight"><?php echo htmlspecialchars($achievements['ach_streak']); ?></span> 
                <?php echo ($achievements['ach_streak'] == 1) ? 'day' : 'days'; ?> </p>
        </div>
        <div class="progress-bar">
            <div class="progress" style="width: <?php echo min(100, $achievements['ach_streak']); ?>%;"></div>
        </div>
    </div>

    <div class="achievement-card modules">
        <div class="achievement-content">
            <span class="emoji">📖</span>
            <h3>Modules Read</h3>
            <p> <span class="highlight"><?php echo htmlspecialchars($achievements['ach_modules_read']); ?></span> 
                <?php echo ($achievements['ach_modules_read'] == 1) ? 'module' : 'modules'; ?></p>

        </div>
        <div class="progress-bar">
            <div class="progress" style="width: <?php echo min(100, $achievements['ach_modules_read']); ?>%;"></div>
        </div>
    </div>

    <div class="achievement-card answered">
        <div class="achievement-content">
            <span class="emoji">✅</span>
            <h3>Assessments Answered</h3>
            <p><span class="highlight"><?php echo htmlspecialchars($achievements['ach_answered_assessments']); ?></span> 
                <?php echo ($achievements['ach_answered_assessments'] == 1) ? 'assessment' : 'assessments'; ?></p>

        </div>
        <div class="progress-bar">
            <div class="progress" style="width: <?php echo min(100, $achievements['ach_answered_assessments']); ?>%;"></div>
        </div>
    </div>

    <div class="achievement-card collabs">
        <div class="achievement-content">
            <span class="emoji">🤝</span>
            <h3>Collaborations</h3>
            <p><span class="highlight"><?php echo htmlspecialchars($achievements['ach_collaborated']); ?></span> 
                <?php echo ($achievements['ach_collaborated'] == 1) ? 'collaboration' : 'collaborations'; ?></p>

        </div>
        <div class="progress-bar">
            <div class="progress" style="width: <?php echo min(100, $achievements['ach_collaborated']); ?>%;"></div>
        </div>
    </div>
</div>
    <h2 class="badge-title">🏅 Earned Badges</h2>
    
    <?php if ($earned_badges): ?>
        <div class="badge-gallery">
            <?php foreach ($earned_badges as $badge): ?>
                <div class="badge-card">
                    <!-- Validate Image Exists -->
                    <?php 
                    $imagePath = "images/badges/" . htmlspecialchars($badge['badge_image']);
                    if (!file_exists($imagePath) || empty($badge['badge_image'])) {
                        $imagePath = "images/badges/default_badge.png"; // Default badge image
                    }
                    ?>
                    <img src="<?php echo $imagePath; ?>" alt="Badge Image" class="badge-img">
                    <div class="badge-info">
                        <h3><?php echo htmlspecialchars($badge['badge_name']); ?></h3>
                        <p>Earned on: <?php echo htmlspecialchars($badge['badge_earned_date']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="no-badges">🏆 You haven't earned any badges yet. Keep pushing forward!</p>
    <?php endif; ?>
</div>


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

        function getBadgeImage($badgeName) {
    $badgeImages = [
        "10 Modules Master" => "10_modules_master.svg",
        "Assessment Beginner" => "assessment_beginner.svg",
        "Collaboration Novice" => "collaboration_novice.svg"
    ];
    return $badgeImages[$badgeName] ?? "default_badge.svg"; // Default image if badge not found
}

    </script>
</body>
</html>