<?php
require 'auth.php';
require 'config.php';

// Check if the user is a student (role 2)
if ($_SESSION['role'] != 2) {
    echo "Access denied: Students only.";
    exit();
}
// Check if student_id is set in session
if (!isset($_SESSION['student_id'])) {
    echo "Student ID is missing.";
    exit();
}
$student_id = $_SESSION['student_id'];
$message = '';

// Handle join class request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['join_class'])) {
    $class_code = trim($_POST['class_code']);
    
    // Check if the class with the provided code exists
    $stmt = $pdo->prepare("SELECT * FROM class_tbl WHERE class_code = ?");
    $stmt->execute([$class_code]);
    $class = $stmt->fetch();

    if ($class) {
        // Check if the student is already enrolled in this class
        $stmt = $pdo->prepare("SELECT * FROM student_classes WHERE student_id = ? AND class_id = ?");
        $stmt->execute([$student_id, $class['class_id']]);
        $existingEnrollment = $stmt->fetch();

        if (!$existingEnrollment) {
            // Insert the student into student_classes table with class_id and student_id
            $stmt = $pdo->prepare("INSERT INTO student_classes (student_id, class_id) VALUES (?, ?)");
            $stmt->execute([$student_id, $class['class_id']]);
            $message = "Successfully joined the class!";
            
            // Redirect to the same page to avoid form resubmission on reload
            header("Location: student_dashboard.php");
            exit();
        } else {
            $message = "You have already joined this class.";
        }
    } else {
        $message = "Class code not found.";
    }
}

// Fetch all classes the student has joined
$stmt = $pdo->prepare("
    SELECT class_tbl.class_id, class_tbl.class_subject, class_tbl.class_section, class_tbl.class_code 
    FROM class_tbl 
    INNER JOIN student_classes ON class_tbl.class_id = student_classes.class_id 
    WHERE student_classes.student_id = ?
");
$stmt->execute([$student_id]);
$joined_classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all pending assessments across all joined classes
$todo_list = [];
foreach ($joined_classes as $class) {
    $stmt = $pdo->prepare("
        SELECT assessment_id, name, type, time_limit 
        FROM assessment_tbl 
        WHERE class_id = ? AND status = 'Published'
    ");
    $stmt->execute([$class['class_id']]);
    $assessments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($assessments as $assessment) {
        // Check if the student has attempted this assessment in any of the attempt tables
        $attempted = false;

        // Check answers_esy_tbl
        $stmt = $pdo->prepare("SELECT Attempt FROM answers_esy_tbl WHERE student_id = ? AND assessment_id = ?");
        $stmt->execute([$student_id, $assessment['assessment_id']]);
        if ($stmt->fetch()) {
            $attempted = true;
        }

        // Check answers_mcq_tbl
        if (!$attempted) {
            $stmt = $pdo->prepare("SELECT Attempt FROM answers_mcq_tbl WHERE student_id = ? AND assessment_id = ?");
            $stmt->execute([$student_id, $assessment['assessment_id']]);
            if ($stmt->fetch()) {
                $attempted = true;
            }
        }

        // Check answers_tf_tbl
        if (!$attempted) {
            $stmt = $pdo->prepare("SELECT Attempt FROM answers_tf_tbl WHERE student_id = ? AND assessment_id = ?");
            $stmt->execute([$student_id, $assessment['assessment_id']]);
            if ($stmt->fetch()) {
                $attempted = true;
            }
        }

        // Check answers_mcq_collab_tbl
        if (!$attempted) {
            $stmt = $pdo->prepare("SELECT attempt FROM answers_mcq_collab_tbl WHERE submitted_by = ? AND assessment_id = ?");
            $stmt->execute([$student_id, $assessment['assessment_id']]);
            if ($stmt->fetch()) {
                $attempted = true;
            }
        }

        // Add to To-Do List if not attempted
        if (!$attempted) {
            $todo_list[] = [
                'name' => $assessment['name'],
                'type' => $assessment['type'],
                'time_limit' => $assessment['time_limit'],
                'class_subject' => $class['class_subject'],
            ];
        }
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="css/student_dashboard.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>

<body>

    <div class="sidebar">
    <div class="logo-container">
        <img src="images/logo/pq_logo_white.webp" class="logo" alt="PeerQuest Logo">
        <img src="images/logo/pq_white_logo_txt.webp" class="logo-text" alt="PeerQuest Logo Text">
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
            <h1 class="dashboard-title">Student’s Dashboard</h1>
            <p class="current-date" id="currentDate"></p>
</div>
<div class="welcome-section">
    <img src="images/C_eyesmile.gif" alt="Welcome Owl">
    <div class="welcome-box">
        <p class="welcome-text">Welcome to your dashboard, <strong><?php echo htmlspecialchars($_SESSION['username']); ?>!</strong></p>
        <p class="welcome-subtext">Ready for some learning and quest?</p>
    </div>
</div>




    <!-- Display success/error message -->
    <?php if ($message): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <h3>Join a Class</h3>
        <form class="join-class-form" method="post" action="student_dashboard.php">
            <input type="text" id="class_code" name="class_code" placeholder="Enter Class Code" required>
            <button type="submit" name="join_class">Join</button>
        </form>

    <!-- Display list of classes the student has joined -->
    <h3>My Classes</h3>
    <?php 
        $headerColors = ['#392B65', '#9D65FC', '#58CDFF', '#FF578F', '#4F5D7A']; 
        $colorCount = count($headerColors);
        $index = 0;
    ?>

    <div class="class-container">
    <?php if (!empty($joined_classes)): ?>
        <?php foreach ($joined_classes as $class): ?>
            <div class="class-card">
            <div class="class-header" style="background: <?php echo $headerColors[$index % $colorCount]; ?>">
                    <span class="class-title"><?php echo htmlspecialchars($class['class_subject']); ?></span>
                </div>
                <div class="class-body">
                    <img src="images/assessment_img.png" alt="Class Image" class="class-image">
                    <div class="class-content">
                        <div class="class-info">
                            <p><strong>Section:</strong> <?php echo htmlspecialchars($class['class_section']); ?></p>
                            <p><strong>Code:</strong> <?php echo htmlspecialchars($class['class_code']); ?></p>
                        </div>
                        <div class="class-actions">
                            <a href="view_assessment_student.php?class_id=<?php echo $class['class_id']; ?>" class="btn">View Assessments</a>
                            <a href="student_modules.php?class_id=<?php echo $class['class_id']; ?>" class="btn">View Modules</a>
                            <a href="status.php?class_id=<?php echo $class['class_id']; ?>&student_id=<?php echo $student_id; ?>" class="btn">View Status</a>
                        </div>
                    </div>
                    <?php $index++; ?>  <!-- Increase index for next iteration -->
        </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="no-assessment-container">
            <img src="images/c_asleep.gif" alt="Charubelle Sleeping" class="charubelle-gif">
            <h3 class="no-assessment-message">No assessment, Charubelle is sleeping. Rest well, player!</h3>
            <h3 style="color: red;">No assessment, Charubelle is sleeping. Rest well, player!</h3>

        </div>
    <?php endif; ?>
</div>



<h3 class="quest-title">My Quests</h3>
<div class="quest-container">
    <?php if (!empty($todo_list)): ?>
        <?php foreach ($todo_list as $todo): ?>
            <div class="quest-card">
                <div class="quest-details">
                    <p class="quest-assessment">
                        <strong><?php echo htmlspecialchars($todo['type']); ?>:</strong> 
                        <?php echo htmlspecialchars($todo['name']); ?>
                    </p>
                    <p class="quest-class">
                        <strong>Class:</strong> 
                        <?php echo htmlspecialchars($todo['class_subject']); ?>
                    </p>
                </div>
                <div class="quest-timer">
                    <span>Time Limit: <?php echo htmlspecialchars($todo['time_limit']); ?> minutes</span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="no-quests">No pending assessments at this time.</p>
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
    </script>

</body>
</html>
