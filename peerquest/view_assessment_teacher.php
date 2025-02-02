<?php
require 'auth.php';
require 'config.php';

$class_id = $_GET['class_id'] ?? null;
$message = $_GET['message'] ?? '';

if (!isset($class_id) || !$class_id) {
    echo "Invalid class ID.";
    exit();
}

// Check if the user is a teacher (role 1)
if ($_SESSION['role'] != 1) {
    echo "Access denied: Teachers only.";
    exit();
}

// Handle form submission to create or update an assessment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assessment_id = $_POST['assessment_id'] ?? null;
    $name = $_POST['name'] ?? 'Untitled Assessment';
    $type = $_POST['type'] ?? 'Essay'; // Default type
    $status = ($type === 'Recitation') ? 'Published' : 'Saved'; // Set "Published" for Recitation
    $time_limit = $_POST['time_limit'] ?? 10;
    $assessment_mode = $_POST['mode'] ?? 'Individual';
    $instructions = $_POST['instructions'] ?? '';

    if ($assessment_id) {
        // Update existing assessment
        $stmt = $pdo->prepare(
            "UPDATE assessment_tbl 
            SET name = ?, type = ?, time_limit = ?, assessment_mode = ?, instructions = ?, status = ? 
            WHERE assessment_id = ?"
        );
        $stmt->execute([$name, $type, $time_limit, $assessment_mode, $instructions, $status, $assessment_id]);
        $message = "Assessment updated successfully!";
    } else {
        $total_points = 0;

        // Insert new assessment
        $stmt = $pdo->prepare(
            "INSERT INTO assessment_tbl 
            (class_id, teacher_id, name, type, status, time_limit, assessment_mode, total_points, instructions) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([ 
            $class_id,
            $_SESSION['teacher_id'],
            $name,
            $type,
            $status,
            $time_limit,
            $assessment_mode,
            $total_points,
            $instructions
        ]);

        $assessment_id = $pdo->lastInsertId();
        $message = "Assessment created successfully!";
    }

    // Redirect to the appropriate page based on the type of assessment
    switch ($type) {
        case 'Essay':
            header("Location: assessment_essay.php?assessment_id=$assessment_id&message=$message");
            break;
        case 'Multiple Choice - Individual':
            header("Location: assessment_multiple_choice.php?assessment_id=$assessment_id&message=$message");
            break;
        case 'Multiple Choice - Collaborative':
            header("Location: assessment_multiple_choice_collab.php?assessment_id=$assessment_id&message=$message");
            break;
        case 'Recitation':
            header("Location: assessment_recitation.php?assessment_id=$assessment_id&message=$message");
            break;
        case 'True or False':
            header("Location: assessment_true_false.php?assessment_id=$assessment_id&message=$message");
            break;
        default:
            die("Invalid assessment type.");
    }
    exit();
}

// Fetch class details
$stmt = $pdo->prepare("SELECT class_subject, class_section FROM class_tbl WHERE class_id = ?");
$stmt->execute([$class_id]);
$class = $stmt->fetch();
if (!$class) {
    echo "Class not found.";
    exit();
}

// Fetch assessments for the class
$stmt = $pdo->prepare("SELECT * FROM assessment_tbl WHERE class_id = ?");
$stmt->execute([$class_id]);
$assessments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Separate assessments into saved and published categories
$saved_assessments = array_filter($assessments, fn($a) => $a['status'] === 'Saved');
$published_assessments = array_filter($assessments, fn($a) => $a['status'] === 'Published');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assessments for <?php echo htmlspecialchars($class['class_section'] . ' - ' . $class['class_subject']); ?></title>
    <link rel="stylesheet" href="css/teacher_assessment.css">
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
            <h1 class="dashboard-title">Assessments for <?php echo htmlspecialchars($class['class_section'] . ' - ' . $class['class_subject']); ?></h1>
            <p class="current-date" id="currentDate"></p>
</div>


<div class="container">
    <div class="create-assessment">
        <h3>Create New Assessment</h3>
        <form method="post">
            <input type="hidden" name="class_id" value="<?php echo htmlspecialchars($class_id); ?>">
            <label for="name">Assessment Name:</label>
            <input type="text" name="name" id="name" placeholder="Enter assessment name" required>
            
            <div class="form-group">
                <label for="type">Assessment Type:</label>
                <div class="custom-dropdown">
                    <select name="type" id="type" required onchange="toggleDropdownIcon(this)">
                        <option value="Essay">Essay</option>
                        <option value="Recitation">Recitation</option>
                        <option value="True or False">True or False</option>
                        <option value="Multiple Choice - Individual">Multiple Choice - Individual</option>
                        <option value="Multiple Choice - Collaborative">Multiple Choice - Collaborative</option>
                    </select>
                    <span class="dropdown-icon">▼</span>
                </div>
            </div>

            <button type="submit" class="btn-create">Create Assessment</button>
        </form>
    </div>
</div>


        <h3>Saved Assessments</h3>
<ul class="assessment-list">
    <?php if (!empty($saved_assessments)): ?>
        <?php foreach ($saved_assessments as $assessment): ?>
            <li class="assessment-item">
                <span><?php echo htmlspecialchars($assessment['name']); ?> (<?php echo htmlspecialchars($assessment['type']); ?>)</span>
                <div class="actions">
                    <a href="<?php 
                        switch ($assessment['type']) {
                            case 'Essay':
                                echo "assessment_essay.php?assessment_id=" . $assessment['assessment_id'];
                                break;
                            case 'True or False':
                                echo "assessment_true_false.php?assessment_id=" . $assessment['assessment_id'];
                                break;
                            case 'Multiple Choice - Individual':
                                echo "assessment_multiple_choice.php?assessment_id=" . $assessment['assessment_id'];
                                break;
                            case 'Multiple Choice - Collaborative':
                                echo "assessment_multiple_choice_collab.php?assessment_id=" . $assessment['assessment_id'];
                                break;
                            case 'Recitation':
                                echo "assessment_recitation.php?assessment_id=" . $assessment['assessment_id'];
                                break;
                            default:
                                echo "#";
                        }
                    ?>" class="btn-action edit">✏️ Edit</a>
                    <a href="assessment_status.php?assessment_id=<?php echo $assessment['assessment_id']; ?>&action=publish" class="btn-action publish">📤 Publish</a>
                    <a href="assessment_status.php?assessment_id=<?php echo $assessment['assessment_id']; ?>&action=delete" class="btn-action delete" onclick="return confirm('Are you sure?');">🗑 Delete</a>
                </div>
            </li>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="no-assessments">No saved assessments available.</p>
    <?php endif; ?>
</ul>

<h3>Published Assessments</h3>
<ul class="assessment-list">
    <?php if (!empty($published_assessments)): ?>
        <?php foreach ($published_assessments as $assessment): ?>
            <li class="assessment-item">
                <span><?php echo htmlspecialchars($assessment['name']); ?> (<?php echo htmlspecialchars($assessment['type']); ?>)</span>
                <div class="actions">
                    <a href="<?php 
                        switch ($assessment['type']) {
                            case 'Essay':
                                echo "assessment_essay.php?assessment_id=" . $assessment['assessment_id'];
                                break;
                            case 'True or False':
                                echo "assessment_true_false.php?assessment_id=" . $assessment['assessment_id'];
                                break;
                            case 'Multiple Choice - Individual':
                                echo "assessment_multiple_choice.php?assessment_id=" . $assessment['assessment_id'];
                                break;
                            case 'Multiple Choice - Collaborative':
                                echo "assessment_multiple_choice_collab.php?assessment_id=" . $assessment['assessment_id'];
                                break;
                            case 'Recitation':
                                echo "assessment_recitation.php?assessment_id=" . $assessment['assessment_id'];
                                break;
                            default:
                                echo "error";
                        }
                    ?>" class="btn-action edit">✏️ Edit</a>
                     <!-- View Groups Button (Only for Collaborative Multiple Choice) -->
                     <?php if ($assessment['type'] === 'Multiple Choice - Collaborative'): ?>
                        <a href="groups.php?assessment_id=<?php echo $assessment['assessment_id']; ?>" class="btn-action groups">👥 View Groups</a>
                    <?php endif; ?>
                    
                    <!-- Unpublish Button -->
                    <a href="assessment_status.php?assessment_id=<?php echo $assessment['assessment_id']; ?>&action=unpublish" class="btn-action unpublish">📥 Unpublish</a>
                    
                    <!-- Delete Button -->
                    <a href="assessment_status.php?assessment_id=<?php echo $assessment['assessment_id']; ?>&action=delete" class="btn-action delete" onclick="return confirm('Are you sure?');">🗑 Delete</a>
                </div>
            </li>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="no-assessments">No published assessments available.</p>
    <?php endif; ?>
</ul>

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

        function toggleDropdownIcon(selectElement) {
            let dropdownIcon = selectElement.nextElementSibling;
            if (selectElement.value) {
                dropdownIcon.style.transform = "rotate(180deg)";
            } else {
                dropdownIcon.style.transform = "rotate(0deg)";
            }
        }

    </script>
</body>
</html>
