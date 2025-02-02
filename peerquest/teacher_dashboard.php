
<?php 
require 'auth.php';
require 'config.php';

// Check if the user is a teacher (role 1)
if ($_SESSION['role'] != 1) {
    echo "Access denied: Teachers only.";
    exit();
}

$teacher_id = $_SESSION['teacher_id']; // Use the teacher_id from the session
$message = '';

// Handle Create Request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create'])) {
    $school_id = $_SESSION['school_id'];
    $subject = $_POST['subject'];
    $class_section = $_POST['class_section'];
    $class_code = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

    // Insert class with the correct teacher_id
    $stmt = $pdo->prepare("INSERT INTO class_tbl (class_section, class_subject, class_code, teacher_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$class_section, $subject, $class_code, $teacher_id]);
    $message = "Class created successfully with code: $class_code.";

    // Redirect to prevent form resubmission
    header("Location: teacher_dashboard.php");
    exit();
}

// Handle Edit Request
if (isset($_GET['edit'])) {
    $class_id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM class_tbl WHERE class_id = ? AND teacher_id = ?");
    $stmt->execute([$class_id, $teacher_id]);
    $editClass = $stmt->fetch();
}

// Handle Update Request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $class_id = $_POST['class_id'];
    $subject = $_POST['subject'];
    $class_section = $_POST['class_section'];

    $stmt = $pdo->prepare("UPDATE class_tbl SET class_section = ?, class_subject = ? WHERE class_id = ? AND teacher_id = ?");
    $stmt->execute([$class_section, $subject, $class_id, $teacher_id]);
    $message = "Class updated successfully.";

    // Redirect to prevent form resubmission
    header("Location: teacher_dashboard.php");
    exit();
}

// Handle Delete Request
if (isset($_GET['delete'])) {
    $class_id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM class_tbl WHERE class_id = ? AND teacher_id = ?");
    $stmt->execute([$class_id, $teacher_id]);
    $message = "Class deleted successfully.";

    // Redirect to prevent URL parameter resubmission
    header("Location: teacher_dashboard.php");
    exit();
}

// Fetch all sections created by this teacher using teacher_id
$sections = $pdo->prepare("SELECT * FROM class_tbl WHERE teacher_id = ?");
$sections->execute([$teacher_id]);
$sections = $sections->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/teacher_dashboard.css">
</head>
<body>
<div class="sidebar">
    <div class="logo-container">
        <img src="images/logo/pq_logo.webp" class="logo" alt="PeerQuest Logo">
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
                <h1 class="dashboard-title">Teacher’s Dashboard</h1>
                <p class="current-date" id="currentDate"></p>
        </div>

        <!-- Display success/error message -->
        <?php if ($message): ?>
            <div class="alert alert-success" role="alert">
                 <?php echo htmlspecialchars($message); ?>
             </div>
         <?php endif; ?>
            <div class="dashboard-header">
                <h3>My Classes</h3>
                <!-- Create Class Button -->
                <button class="create-class-btn" onclick="openCreateClassModal()">Create Class</button>
            </div>

                    <!-- Create Class Modal -->
            <div class="popup-overlay" id="createClassOverlay"></div>
            <div class="popup" id="createClassModal">
                <div class="popup-content">
                    <span class="close-popup" onclick="closeCreateClassModal()">&times;</span>
                    <h3>Create a New Class</h3>
                    <form method="post" action="teacher_dashboard.php">
                        <div class="input-group">
                            <label for="class_section">Class Section:</label>
                            <input type="text" id="create_class_section" name="class_section" required>
                        </div>

                        <div class="input-group">
                            <label for="subject">Subject:</label>
                            <input type="text" id="create_subject" name="subject" required>
                        </div>

                        <div class="popup-actions">
                            <button type="button" class="btn-cancel" onclick="closeCreateClassModal()">Cancel</button>
                            <button type="submit" name="create" class="btn-submit">Create Class</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Edit Class Modal -->
            <div class="popup-overlay" id="editClassOverlay"></div>
            <div class="popup" id="editClassModal">
                <div class="popup-content">
                    <span class="close-popup" onclick="closeEditClassModal()">&times;</span>
                    <h3>Edit Class</h3>
                    <form method="post" action="teacher_dashboard.php">
                        <input type="hidden" id="edit_class_id" name="class_id">

                        <div class="input-group">
                            <label for="edit_class_section">Class Section:</label>
                            <input type="text" id="edit_class_section" name="class_section" required>
                        </div>

                        <div class="input-group">
                            <label for="edit_subject">Subject:</label>
                            <input type="text" id="edit_subject" name="subject" required>
                        </div>

                        <div class="popup-actions">
                            <button type="button" class="btn-cancel" onclick="closeEditClassModal()">Cancel</button>
                            <button type="submit" name="update" class="btn-submit">Update Class</button>
                        </div>
                    </form>
                </div>
            </div>




            <?php 
                $index = 0; // Initialize index for colors
                $headerColors = ['#828BAC', '#EB6D64', '#5548A6', '#79D0CA', '#FFC6A2', '#F96A75','#D5BCFF']; 
                $colorCount = count($headerColors);
            ?>
            
            <?php 
                $index = 0; // Initialize index for colors
                $headerColors = ['#828BAC', '#EB6D64', '#5548A6', '#79D0CA', '#FFC6A2', '#F96A75', '#D5BCFF']; 
                $colorCount = count($headerColors);

                // Array of images
                $classImages = [
                    'images/class_img_1.webp',
                    'images/class_img_2.webp',
                    'images/class_img_3.webp',
                    'images/class_img_4.webp'
                ];
                $imageCount = count($classImages);
            ?>

            <div class="class-container">
            <?php foreach ($sections as $index => $class): ?>
                <div class="class-card">

                    <!-- Class Header with Dynamic Colors -->
                        <div class="class-header" style="background: <?php echo $headerColors[$index % count($headerColors)]; ?>;">
                            <span class="class-title"><?php echo htmlspecialchars($class['class_subject']); ?></span>

                            <!-- Icon Container -->
                            <div class="icon-actions">
                            <a href="javascript:void(0);" 
                                    class="icon-btn" 
                                    onclick="openEditClassModal('<?php echo $class['class_id']; ?>', '<?php echo htmlspecialchars($class['class_section']); ?>', '<?php echo htmlspecialchars($class['class_subject']); ?>')">
                                        <i class="fas fa-edit"></i> <!-- Edit Icon -->
                                    </a>
                                <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $class['class_id']; ?>)" class="icon-btn">
                                    <i class="fas fa-trash"></i> <!-- Delete Icon -->
                                </a>
                            </div>
                        </div>

                    
                    <!-- Class Body -->
                    <div class="class-body">
                        <!-- Dynamic Class Image -->
                             <img src="<?php echo $classImages[$index % $imageCount]; ?>" alt="Class Image" class="class-image">
                        <div class="class-content">
                            <div class="class-info">
                                <p><strong>Section:</strong> <?php echo htmlspecialchars($class['class_section']); ?></p>
                                <p><strong>Code:</strong> <?php echo htmlspecialchars($class['class_code']); ?></p>
                            </div>

                            <!-- Teacher Actions -->
                            <div class="class-actions">
                                <a href="view_classlist.php?class_id=<?php echo $class['class_id']; ?>" class="btn-action">View Class List</a>
                                <a href="view_assessment_teacher.php?class_id=<?php echo $class['class_id']; ?>" class="btn-action">View Assessments</a>
                                <a href="teacher_modules.php?class_id=<?php echo $class['class_id']; ?>" class="btn-action">View Modules</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
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
        updateDate();
        
       // Create Class Modal Logic
            function openCreateClassModal() {
                document.getElementById("createClassModal").style.display = "block";
                document.getElementById("createClassOverlay").style.display = "block";
            }

            function closeCreateClassModal() {
                document.getElementById("createClassModal").style.display = "none";
                document.getElementById("createClassOverlay").style.display = "none";
            }

            // Edit Class Modal Logic
            function openEditClassModal(classId, classSection, subject) {
                // Populate hidden and text inputs
                document.getElementById("edit_class_id").value = classId;
                document.getElementById("edit_class_section").value = classSection;
                document.getElementById("edit_subject").value = subject;

                // Show the edit modal and overlay
                document.getElementById("editClassModal").style.display = "block";
                document.getElementById("editClassOverlay").style.display = "block";
            }

            function closeEditClassModal() {
                // Hide the edit modal and overlay
                document.getElementById("editClassModal").style.display = "none";
                document.getElementById("editClassOverlay").style.display = "none";
            }



        </script>
    </div>
</body>
</html>
