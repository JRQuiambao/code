<?php
require 'auth.php';
require 'config.php';

// Function to extract content from .docx using ZipArchive
if (!function_exists('extractDocxContent')) {
    function extractDocxContent($file_path) {
        $content = '';
        if (file_exists($file_path)) {
            $zip = new ZipArchive;
            if ($zip->open($file_path) === TRUE) {
                // Extract word/document.xml
                $xml = $zip->getFromName('word/document.xml');
                $dom = new DOMDocument;
                @$dom->loadXML($xml);
                foreach ($dom->getElementsByTagName('p') as $paragraph) {
                    $content .= $paragraph->textContent . "\n";
                }
                $zip->close();
            } else {
                $content = "Failed to open the .docx file.";
            }
        } else {
            $content = "File not found.";
        }
        return $content;
    }
}

if (!function_exists('extractDocContent')) {
    function extractDocContent($file_path) {
        return file_exists($file_path) ? shell_exec("antiword " . escapeshellarg($file_path)) : '';
    }
}

if ($_SESSION['role'] !== 1) {
    header("Location: login.php");
    exit();
}

// Check if class_id is set in the URL
if (!isset($_GET['class_id']) || !is_numeric($_GET['class_id'])) {
    die("Invalid class ID.");
}

$class_id = $_GET['class_id'];

// Verify the teacher owns the class
$stmt = $pdo->prepare("SELECT * FROM class_tbl WHERE class_id = ? AND teacher_id = ?");
$stmt->execute([$class_id, $_SESSION['teacher_id']]);
$class = $stmt->fetch();

if (!$class) {
    die("Class not found or access denied.");
}

// Fetch modules for the selected class
$stmt = $pdo->prepare("SELECT * FROM modules_tbl WHERE teacher_id = ? AND class_id = ?");
$stmt->execute([$_SESSION['teacher_id'], $class_id]);
$modules = $stmt->fetchAll(PDO::FETCH_ASSOC);

$message = '';

// Handle POST requests for modules
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['upload'])) {
        // Ask for title when uploading a file
        if (empty($_POST['module_title'])) {
            $message = "Please provide a title for the file upload.";
        } else {
            // Handle file upload
            if (isset($_FILES['module_file']) && $_FILES['module_file']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['module_file']['tmp_name'];
                $fileName = $_FILES['module_file']['name'];
                $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

                if (!in_array($fileExtension, ['doc', 'docx'])) {
                    $message = "Invalid file type. Only .doc and .docx are allowed.";
                } else {
                    $uploadDir = 'uploads/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $filePath = $uploadDir . basename($fileName);
                    if (move_uploaded_file($fileTmpPath, $filePath)) {
                        // Extract content from the file
                        $fileContent = '';
                        if ($fileExtension === 'docx') {
                            $fileContent = extractDocxContent($filePath);
                        } else {
                            $fileContent = extractDocContent($filePath);
                        }

                        // Insert into database with title
                        $moduleTitle = $_POST['module_title'];
                        $stmt = $pdo->prepare("INSERT INTO modules_tbl (class_id, title, file_name, content, teacher_id, status) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$class_id, $moduleTitle, $fileName, $fileContent, $_SESSION['teacher_id'], 'Saved']);
                        $message = "File uploaded and content extracted successfully.";
                    } else {
                        $message = "Failed to upload the file.";
                    }
                }
            } else {
                $message = "Please select a file to upload.";
            }
        }
    } elseif (isset($_POST['create'])) {
        // Handle create from scratch
        $moduleTitle = $_POST['module_title'];
        $moduleContent = $_POST['module_content'];

        // Insert the new module into the database
        $stmt = $pdo->prepare("INSERT INTO modules_tbl (class_id, title, content, teacher_id, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$class_id, $moduleTitle, $moduleContent, $_SESSION['teacher_id'], 'Saved']);
        $message = "Module created successfully.";
    } elseif (isset($_POST['toggle_status'])) {
        // Handle toggle status
        $moduleId = $_POST['module_id'];
        $currentStatus = $_POST['current_status'];
        $newStatus = ($currentStatus === 'Saved') ? 'Published' : 'Saved';

        // Update the status in the database
        $stmt = $pdo->prepare("UPDATE modules_tbl SET status = ? WHERE module_id = ? AND teacher_id = ?");
        $stmt->execute([$newStatus, $moduleId, $_SESSION['teacher_id']]);
        $message = "Module status updated successfully.";
    } elseif (isset($_POST['delete'])) {
        // Handle delete module
        if (isset($_POST['module_id'])) {
            $moduleId = (int)$_POST['module_id']; // Ensure it's an integer
            $stmt = $pdo->prepare("DELETE FROM modules_tbl WHERE module_id = ? AND teacher_id = ?");
            $stmt->execute([$moduleId, $_SESSION['teacher_id']]);
            $message = "Module deleted successfully.";
        } else {
            $message = "Invalid module ID.";
        }
    }
}

// Fetch updated modules
$stmt = $pdo->prepare("SELECT * FROM modules_tbl WHERE teacher_id = ? AND class_id = ?");
$stmt->execute([$_SESSION['teacher_id'], $class_id]);
$modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modules - LMS Design</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/teacher_module.css">
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
                <h1 class="dashboard-title">Modules</h1>
                <p class="current-date" id="currentDate"></p>
        </div>

<div class="container">
    <h5>Class: <?php echo htmlspecialchars($class['class_section'] . ' - ' . $class['class_subject']); ?></h5>

    <!-- Upload and Create Module Box -->
    <div class="row">
        <div class="col-md-6">
            <div class="box">
                <ul class="nav nav-tabs" id="tabMenu">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#fileUpload">File Upload</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#textEntry">Text Entry</a>
                    </li>
                </ul>

                <div class="tab-content mt-3">
                    <!-- File Upload Tab -->
                    <div class="tab-pane fade show active" id="fileUpload">
                        <div class="drag-drop-box">
                        <img src="images/icons/upload_icon.webp" alt="Upload Icon" class="upload-icon">
                            <p><strong>Drag and Drop files here</strong></p>
                            <p>or</p>
                            <form method="post" enctype="multipart/form-data">
                                <input type="text" name="module_title" placeholder="Module Title" class="form-control input-box" required>
                                <input type="file" name="module_file" class="form-control input-box" required>
                                <button type="submit" name="upload" class="btn btn-primary w-100">Upload File</button>
                            </form>
                        </div>
                    </div>

                    <!-- Text Entry Tab -->
                    <div class="tab-pane fade" id="textEntry">
                        <form method="post">
                            <input type="text" name="module_title" placeholder="Module Title" class="form-control input-box" required>
                            <textarea name="module_content" placeholder="Enter Module Content" rows="5" class="form-control input-box" required></textarea>
                            <button type="submit" name="create" class="btn btn-success w-100">Submit Module</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Existing Modules Section -->
        <div class="col-md-6">
            <div class="box">
                <h4>Existing Modules</h4>
                <?php if (empty($modules)): ?>
                    <p class="text-center text-muted">No modules found for this class.</p>
                <?php else: ?>
                    <?php foreach ($modules as $module): ?>
                        <div class="lesson-row">
                            <span><?php echo htmlspecialchars($module['title']); ?></span>
                            <div class="actions">
                                <a href="edit_modules.php?module_id=<?php echo $module['module_id']; ?>" title="Edit">
                                    &#9998;
                                </a>
                                <form method="POST" action="teacher_modules.php?class_id=<?php echo $class_id; ?>" style="display:inline;" onsubmit="return confirmDelete();">
                                    <input type="hidden" name="module_id" value="<?php echo $module['module_id']; ?>">
                                    <button type="submit" name="delete" title="Delete">
                                        &#128465;
                                    </button>
                                </form>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="module_id" value="<?php echo $module['module_id']; ?>">
                                    <input type="hidden" name="current_status" value="<?php echo $module['status']; ?>">
                                    <button type="submit" name="toggle_status" class="status-indicator <?php echo $module['status'] === 'Published' ? 'published' : 'unpublished'; ?>">
                                        <?php echo $module['status'] === 'Published' ? 'Published' : 'Unpublish'; ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <a href="teacher_dashboard.php" class="back-button">Back to Dashboard</a>
</div>
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

function confirmDelete() {
    return confirm('Are you sure you want to delete this module?');
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
