<?php
require 'auth.php';
require 'config.php';

$assessment_id = $_GET['assessment_id'] ?? null;

// Fetch assessment data
$assessment = null;
if ($assessment_id) {
    $stmt = $pdo->prepare("SELECT * FROM assessment_tbl WHERE assessment_id = ?");
    $stmt->execute([$assessment_id]);
    $assessment = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$assessment) {
        die("Assessment not found.");
    }
} else {
    die("No assessment selected.");
}

// Fetch existing questions
$stmt = $pdo->prepare("SELECT * FROM questions_mcq_tbl WHERE assessment_id = ?");
$stmt->execute([$assessment_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Dynamically calculate total points
$total_points = array_sum(array_column($questions, 'points'));

// Update total points in the assessment table
$stmt = $pdo->prepare("UPDATE assessment_tbl SET total_points = ? WHERE assessment_id = ?");
$stmt->execute([$total_points, $assessment_id]);

// Initialize messages
$save_message = '';
$publish_message = '';
$unpublish_message = '';
$update_message = '';
$error_message = '';

// Handle Save/Publish/Update/Unpublish actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $time_limit = $_POST['time_limit'] ?? $assessment['time_limit'];
    $instructions = $_POST['instructions'] ?? $assessment['instructions'];
    $assessment_title = trim($_POST['assessment_name'] ?? $assessment['name']);

    if (isset($_POST['save_assessment']) || isset($_POST['publish_assessment'])) {
        if (count($questions) === 0) {
            $error_message = "Please add at least one question before saving or publishing.";
        } else {
            $status = isset($_POST['publish_assessment']) ? 'Published' : 'Saved';
            $stmt = $pdo->prepare("UPDATE assessment_tbl SET name = ?, status = ?, time_limit = ?, total_points = ? WHERE assessment_id = ?");
            $stmt->execute([$assessment_title, $status, $time_limit, $total_points, $assessment_id]);

            $assessment['name'] = $assessment_title;
            $assessment['time_limit'] = $time_limit;
            $assessment['status'] = $status;

            $save_message = isset($_POST['save_assessment']) ? "Assessment saved successfully!" : "";
            $publish_message = isset($_POST['publish_assessment']) ? "Assessment published successfully!" : "";
        }
    }
}

     //updating 
     if (isset($_POST['update_assessment'])) {
        $stmt = $pdo->prepare("UPDATE assessment_tbl SET name = ?, instructions = ?, time_limit = ? WHERE assessment_id = ?");
        $stmt->execute([$assessment_title, $instructions, $time_limit, $assessment_id]);

        $assessment['name'] = $assessment_title;
        $assessment['time_limit'] = $time_limit;
        $assessment['instructions'] = $instructions;

        $update_message = "Assessment updated successfully!";
    }

    //unpublish
    if (isset($_POST['unpublish_assessment'])) {
        $stmt = $pdo->prepare("UPDATE assessment_tbl SET status = 'Saved' WHERE assessment_id = ?");
        $stmt->execute([$assessment_id]);

        $assessment['status'] = 'Saved';
        $unpublish_message = "Assessment unpublished successfully!";
    }

// Handle adding a new essay question
if (isset($_POST['add_question'])) {
    $question_text = $_POST['question'] ?? '';
    $options = json_encode([
        'A' => $_POST['option_a'] ?? '',
        'B' => $_POST['option_b'] ?? '',
        'C' => $_POST['option_c'] ?? '',
        'D' => $_POST['option_d'] ?? ''
    ]);
    $correct_option = $_POST['correct_option'] ?? '';
    $points = !empty($_POST['points']) ? $_POST['points'] : 1;

    if (empty($question_text)) {
        $error_message = "Question text cannot be empty.";
    } else {
        // Insert the new question into the table
        $stmt = $pdo->prepare("INSERT INTO questions_mcq_tbl (assessment_id, question_text, options, correct_option, points) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$assessment_id, $question_text, $options, $correct_option, $points]);

        // Update total points dynamically after insertion
        $stmt = $pdo->prepare("SELECT * FROM questions_mcq_tbl WHERE assessment_id = ?");
        $stmt->execute([$assessment_id]);
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total_points = array_sum(array_column($questions, 'points'));

        $stmt = $pdo->prepare("UPDATE assessment_tbl SET total_points = ? WHERE assessment_id = ?");
        $stmt->execute([$total_points, $assessment_id]);

        // Redirect to refresh the page after adding the question
        header("Location: assessment_multiple_choice.php?assessment_id=$assessment_id");
        exit();
    }
}


if (isset($_POST['remove_question'])) {
    $question_id = $_POST['question_id'] ?? null;
    if ($question_id) {
        $stmt = $pdo->prepare("DELETE FROM questions_mcq_tbl WHERE question_id = ?");
        $stmt->execute([$question_id]);

        // Recalculate and update total points
        recalculateTotalPoints($pdo, $assessment_id);
    }

    header("Location: assessment_multiple_choice.php?assessment_id=$assessment_id");
    exit();
}


if (isset($_POST['update_question'])) {
    $question_id = $_POST['question_id'] ?? null;
    $question_text = $_POST['question_text'] ?? '';
    $points = $_POST['points'] ?? 1;
    $correct_option = $_POST['correct_option'] ?? '';

    // Decode JSON options sent from JavaScript
    $options = json_encode(json_decode($_POST['options'], true));

    // Update the question with the correct data
    $stmt = $pdo->prepare("UPDATE questions_mcq_tbl SET question_text = ?, points = ?, options = ?, correct_option = ? WHERE question_id = ?");
    $stmt->execute([$question_text, $points, $options, $correct_option, $question_id]);

    // Re-fetch the updated questions
    $stmt = $pdo->prepare("SELECT * FROM questions_mcq_tbl WHERE assessment_id = ?");
    $stmt->execute([$assessment_id]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit MCQ - Individual Assessment: <?php echo htmlspecialchars($assessment['name'] ?? ''); ?></title>
    <link rel="stylesheet" href="css/assessment_tf.css">
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
            <h1 class="dashboard-title"> Edit Assessment - MCQ Individual</h1>
            <p class="current-date" id="currentDate"></p>
    </div> 

    <div class="welcome-section">
    <img src="images/charaubelle/C_teacher_eyesmile.webp" alt="Welcome Owl">
    <div class="welcome-box">
        <p class="welcome-text">Hi, <strong><?php echo htmlspecialchars($_SESSION['username']); ?>! Ready to create an essay assessment?</strong></p>
        <p class="welcome-subtext"> Start by setting instructions, adding questions, and helping your students unlock their writing potential! </p>
    </div>
</div>

    <!-- Display Messages -->
    <?php if ($save_message): ?>
        <p style="color: green;"><?php echo $save_message; ?></p>
    <?php endif; ?>
    <?php if ($publish_message): ?>
        <p style="color: green;"><?php echo $publish_message; ?></p>
    <?php endif; ?>
    <?php if ($unpublish_message): ?>
        <p style="color: green;"><?php echo $unpublish_message; ?></p>
    <?php endif; ?>
    <?php if ($update_message): ?>
        <p style="color: green;"><?php echo $update_message; ?></p>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <p style="color: red;"><?php echo $error_message; ?></p>
    <?php endif; ?>

    <!-- Assessment Form -->
    <form method="post">

    <!-- Save and Publish Buttons -->
    <div class="top-buttons">
        <?php if ($assessment['status'] === 'Published'): ?>
                <button type="submit" name="update_assessment" class="btn btn-update">Update</button>
                <button type="submit" name="unpublish_assessment" class="btn btn-unpublish">Unpublish</button>
        <?php else: ?>
                <button type="submit" name="save_assessment" class="btn btn-save">Save</button>
                <button type="submit" name="publish_assessment" class="btn btn-publish">Publish</button>
        
        <?php endif; ?>
    </div>

    <div class="container">

        <form method="post">
            <div class="header-section">

            <div class="card">
            <h3 class="editable-heading">Assessment Name
            <img src="images/icons/edit_icon.webp" alt="Edit Icon" class="edit-icon" />
            </h3>
                <input type="text" id="assessment-name" name="assessment_name" value="<?php echo htmlspecialchars($assessment['name']); ?>" required>
            </div>
        </div>
    </form>


    <div class="card settings-card">
    <div class="settings-row">
        <div class="form-group">
            <h3 class="editable-heading">Time Limit
                 <img src="images/icons/clock_icon.webp" alt="Edit Icon" class="edit-icon" />
            </h3>
             <label>(set the time in minutes) </label>
            <input type="number" id="time-limit" value="<?php echo $assessment['time_limit']; ?>" onchange="updateTimeLimit()" />
        </div>

        <div class="form-group">
            <h3 class="editable-heading">Total Points
                 <img src="images/icons/points_icon.webp" alt="Edit Icon" class="edit-icon" />
            </h3>
            <label>(Points automatically increase as questions are added.) </label>
            <div class="total-points-badge">
                <?php echo htmlspecialchars($total_points); ?>
            </div>
        </div>
    </div>

    <div class="form-group">
    <h3 class="editable-heading">Instructions
                 <img src="images/icons/instructions_icon.webp" alt="Edit Icon" class="edit-icon" />
            </h3>
                <label for="instructions">(Important Notes for Students)</label>
                <textarea id="instructions" onchange="updateInstructions()"><?php echo htmlspecialchars($assessment['instructions']); ?></textarea>
        </div>
    </div>

    <div class="card">
    <!-- Add Questions to Assessment -->
    <h3 class="editable-heading">Add Questions
                 <img src="images/icons/questions_icon.webp" alt="Edit Icon" class="edit-icon" />
    </h3>
    
    <form method="post">
        <label>Question:</label>
        <input type="text" name="question" >

        <label>Option A:</label>
        <input type="text" name="option_a">

        <label>Option B:</label>
        <input type="text" name="option_b">

        <label>Option C:</label>
        <input type="text" name="option_c">

        <label>Option D:</label>
        <input type="text" name="option_d">

        <label>Correct Option:</label>
        <select name="correct_option" id="correct_option" class="styled-select" required>
            <option value="A">A</option>
            <option value="B">B</option>
            <option value="C">C</option>
            <option value="D">D</option>
        </select>
        <br> </br>
        <button type="submit" name="add_question">Add Question</button>
    </form>
        </div>

    <div class="card">
    <!-- Display Current Questions -->
    <h3 class="editable-heading">Current Questions
        <img src="images/icons/questions_icon.webp" alt="Edit Icon" class="edit-icon" />
    </h3>
    <ol>
    <?php foreach ($questions as $question): ?>
    <?php $options = json_decode($question['options'], true); ?>
    <li>
        <label>Question:</label>
        <textarea id="question-text-<?php echo $question['question_id']; ?>" onchange="updateQuestion('<?php echo $question['question_id']; ?>')"><?php echo htmlspecialchars($question['question_text']); ?></textarea>

        <label>Points:</label>
        <input type="number" id="question-points-<?php echo $question['question_id']; ?>" value="<?php echo $question['points']; ?>" min="1" onchange="updateQuestion('<?php echo $question['question_id']; ?>')">

        <label>Edit Option A:</label>
        <input type="text" id="option-a-<?php echo $question['question_id']; ?>" value="<?php echo htmlspecialchars($options['A'] ?? ''); ?>" required>

        <label>Edit Option B:</label>
        <input type="text" id="option-b-<?php echo $question['question_id']; ?>" value="<?php echo htmlspecialchars($options['B'] ?? ''); ?>" required>

        <label>Edit Option C:</label>
        <input type="text" id="option-c-<?php echo $question['question_id']; ?>" value="<?php echo htmlspecialchars($options['C'] ?? ''); ?>" required>

        <label>Edit Option D:</label>
        <input type="text" id="option-d-<?php echo $question['question_id']; ?>" value="<?php echo htmlspecialchars($options['D'] ?? ''); ?>" required>

        <label>Correct Answer:</label>
        <select id="correct-answer-<?php echo $question['question_id']; ?>" class="styled-select" onchange="updateQuestion('<?php echo $question['question_id']; ?>')">
            <option value="A" <?php echo $question['correct_option'] === 'A' ? 'selected' : ''; ?>>A</option>
            <option value="B" <?php echo $question['correct_option'] === 'B' ? 'selected' : ''; ?>>B</option>
            <option value="C" <?php echo $question['correct_option'] === 'C' ? 'selected' : ''; ?>>C</option>
            <option value="D" <?php echo $question['correct_option'] === 'D' ? 'selected' : ''; ?>>D</option>
        </select>

        <br><br>
        <button type="button" onclick="removeQuestion('<?php echo $question['question_id']; ?>')">Remove</button>
    </li>
<?php endforeach; ?>
</ol>
</div>

<script>
        function updateName() {
            const name = document.getElementById('name').value;
            updateField('', { update_assessment: 1, name });
        }

        function updateInstructions() {
            const instructions = document.getElementById('instructions').value;
            updateField('', { update_assessment: 1, instructions });
        }

        function updateTimeLimit() {
            const timeLimit = document.getElementById('time-limit').value;
            updateField('', { update_assessment: 1, time_limit: timeLimit });
        }

        function updateQuestion(questionId) {
    const questionText = document.getElementById(`question-text-${questionId}`).value;
    const points = document.getElementById(`question-points-${questionId}`).value;

    // Get updated options from the input fields
    const updatedOptions = {
        A: document.getElementById(`option-a-${questionId}`).value,
        B: document.getElementById(`option-b-${questionId}`).value,
        C: document.getElementById(`option-c-${questionId}`).value,
        D: document.getElementById(`option-d-${questionId}`).value
    };

    const correctAnswer = document.getElementById(`correct-answer-${questionId}`).value;

    // Send updated data to the backend
    updateField('', {
        update_question: 1,
        question_id: questionId,
        question_text: questionText,
        points: points,
        options: JSON.stringify(updatedOptions),  // Convert options to JSON format
        correct_option: correctAnswer
    });
}

        async function removeQuestion(questionId) {
    if (confirm("Are you sure you want to remove this question?")) {
        const formData = new FormData();
        formData.append('remove_question', 1);
        formData.append('question_id', questionId);

        try {
            const response = await fetch('assessment_multiple_choice.php?assessment_id=<?php echo $assessment_id; ?>', {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                location.reload();  // Reload the page after successful deletion
            } else {
                alert("Failed to remove the question. Please try again.");
            }
        } catch (error) {
            alert("An error occurred while removing the question.");
        }
    }
}


async function updateField(url, data) {
    const formData = new FormData();
    for (const key in data) {
        formData.append(key, data[key]);
    }

    try {
        const response = await fetch('assessment_multiple_choice.php?assessment_id=<?php echo $assessment_id; ?>', {
            method: 'POST',
            body: formData,
        });

        if (!response.ok) {
            throw new Error("Network response was not ok");
        }
        console.log("Question updated successfully.");
    } catch (error) {
        console.error("Error updating question:", error);
    }
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
    </script>

</body>
</html>
