<?php
require 'auth.php';
require 'config.php';

$assessment_id = $_GET['assessment_id'] ?? null;

// Fetch assessment data
$assessment = null;
if ($assessment_id) {
    $stmt = $pdo->prepare("SELECT name, time_limit, status, total_points FROM assessment_tbl WHERE assessment_id = ?");
    $stmt->execute([$assessment_id]);
    $assessment = $stmt->fetch();
    if (!$assessment) {
        echo "Assessment not found.";
        exit();
    }

    // Update the 'type' to 'Multiple Choice - Collaborative' when the page loads
    $stmt = $pdo->prepare("UPDATE assessment_tbl SET type = 'Multiple Choice - Collaborative' WHERE assessment_id = ?");
    $stmt->execute([$assessment_id]);
} else {
    echo "No assessment selected.";
    exit();
}

// Fetch existing questions
$stmt = $pdo->prepare("SELECT * FROM questions_mcq_tbl WHERE assessment_id = ?");
$stmt->execute([$assessment_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize messages
$save_message = '';
$publish_message = '';
$unpublish_message = '';
$update_message = '';
$error_message = '';

// Handle Save/Publish/Unpublish/Update actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['save_assessment']) || isset($_POST['publish_assessment']) || isset($_POST['unpublish_assessment']) || isset($_POST['update_assessment']))) {
    if (count($questions) === 0) {
        $error_message = "Please ensure at least one question is set before saving or publishing.";
    } else {
        if (isset($_POST['publish_assessment'])) {
            $status = 'Published';
        } elseif (isset($_POST['unpublish_assessment'])) {
            $status = 'Saved';
        } elseif (isset($_POST['update_assessment'])) {
            $status = $assessment['status'];
        } else {
            $status = 'Saved';
        }

        $stmt = $pdo->prepare("UPDATE assessment_tbl SET status = ?, time_limit = ? WHERE assessment_id = ?");
        $stmt->execute([$status, $_POST['timer'] ?? $assessment['time_limit'], $assessment_id]);

        if ($stmt->rowCount() === 0) {
            $error_message = "";
        } else {
            if ($status === 'Published') {
                $publish_message = "Assessment published successfully!";
                header("Location: assessment_multiple_choice_collab.php?assessment_id=$assessment_id");
                exit();
            } elseif ($status === 'Saved' && isset($_POST['unpublish_assessment'])) {
                $unpublish_message = "Assessment unpublished successfully!";
                header("Location: assessment_multiple_choice_collab.php?assessment_id=$assessment_id");
                exit();
            } elseif (isset($_POST['update_assessment'])) {
                $update_message = "Assessment updated successfully!";
                header("Location: assessment_multiple_choice_collab.php?assessment_id=$assessment_id");
                exit();
            } else {
                $save_message = "Assessment saved successfully!";
                header("Location: assessment_multiple_choice_collab.php?assessment_id=$assessment_id");
                exit();
            }
        }
    }
}

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
    $timer = $_POST['timer'] ?? $assessment['time_limit'];

    $stmt = $pdo->prepare("INSERT INTO questions_mcq_tbl (assessment_id, question_text, options, correct_option, points) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$assessment_id, $question_text, $options, $correct_option, $points]);

    // Recalculate and update total points
    recalculateTotalPoints($pdo, $assessment_id);

    header("Location: assessment_multiple_choice_collab.php?assessment_id=$assessment_id");
    exit();
}



if (isset($_POST['remove_question'])) {
    $question_id = $_POST['question_id'] ?? null;
    if ($question_id) {
        $stmt = $pdo->prepare("DELETE FROM questions_mcq_tbl WHERE question_id = ?");
        $stmt->execute([$question_id]);

        // Recalculate and update total points
        recalculateTotalPoints($pdo, $assessment_id);
    }

    header("Location: assessment_multiple_choice_collab.php?assessment_id=$assessment_id");
    exit();
}


if (isset($_POST['edit_question'])) {
    $question_id = $_POST['question_id'] ?? null;
    $question_text = $_POST['edit_question_text'] ?? '';
    $options = json_encode([
        'A' => $_POST['edit_option_a'] ?? '',
        'B' => $_POST['edit_option_b'] ?? '',
        'C' => $_POST['edit_option_c'] ?? '',
        'D' => $_POST['edit_option_d'] ?? ''
    ]);
    $correct_option = $_POST['edit_correct_option'] ?? '';
    $points = !empty($_POST['edit_points']) ? $_POST['edit_points'] : 1;

    if ($question_id) {
        $stmt = $pdo->prepare("UPDATE questions_mcq_tbl SET question_text = ?, options = ?, correct_option = ?, points = ? WHERE question_id = ?");
        $stmt->execute([$question_text, $options, $correct_option, $points, $question_id]);

        // Recalculate and update total points
        recalculateTotalPoints($pdo, $assessment_id);
    }

    header("Location: assessment_multiple_choice_collab.php?assessment_id=$assessment_id");
    exit();
}



function recalculateTotalPoints($pdo, $assessment_id) {
    $stmt = $pdo->prepare("SELECT SUM(points) as total_points FROM questions_mcq_tbl WHERE assessment_id = ?");
    $stmt->execute([$assessment_id]);
    $total_points = $stmt->fetchColumn() ?? 0;

    $stmt = $pdo->prepare("UPDATE assessment_tbl SET total_points = ? WHERE assessment_id = ?");
    $stmt->execute([$total_points, $assessment_id]);

    return $total_points;
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Collaborative Assessment: <?php echo htmlspecialchars($assessment['name']); ?></title>
</head>
<body>
    <h2>Edit Collaborative Assessment: <?php echo htmlspecialchars($assessment['name']); ?></h2>

    <button onclick="history.back()" class="btn btn-secondary">Back</button>
    <a href="teacher_dashboard.php" class="btn btn-primary">Home</a>

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

    <form method="post">
        <label>Timer (minutes):</label>
        <input type="number" name="timer" min="0" value="<?php echo htmlspecialchars($assessment['time_limit'] ?? 0); ?>">
        <?php if ($assessment['status'] === 'Published'): ?>
            <button type="submit" name="update_assessment">Update Assessment</button>
        <?php else: ?>
            <button type="submit" name="save_assessment">Save Assessment</button>
            <button type="submit" name="publish_assessment">Publish Assessment</button>
        <?php endif; ?>
        <?php if ($assessment['status'] === 'Published'): ?>
            <button type="submit" name="unpublish_assessment">Unpublish Assessment</button>
        <?php endif; ?>
    </form>

    <h3>Add Questions</h3>
    <p><strong>Total Points:</strong> <?php echo htmlspecialchars($assessment['total_points'] ?? 0); ?></p>
    <form method="post">
        <label>Question:</label>
        <input type="text" name="question">

        <label>Option A:</label>
        <input type="text" name="option_a">

        <label>Option B:</label>
        <input type="text" name="option_b">

        <label>Option C:</label>
        <input type="text" name="option_c">

        <label>Option D:</label>
        <input type="text" name="option_d">

        <label>Correct Option:</label>
        <select name="correct_option" required>
            <option value="A">A</option>
            <option value="B">B</option>
            <option value="C">C</option>
            <option value="D">D</option>
        </select>

        <label>Points:</label>
        <input type="number" name="points" min="1">

        <button type="submit" name="add_question">Add Question</button>
    </form>

     <!-- Display Current Questions -->
     <h3>Current Questions</h3>
    <ol>
        <?php foreach ($questions as $question): 
            $options = json_decode($question['options'], true);
        ?>
            <li>
                <p><?php echo htmlspecialchars($question['question_text']); ?></p>
                <ul>
                    <?php foreach ($options as $key => $value): ?>
                        <li><?php echo "$key: $value"; ?></li>
                    <?php endforeach; ?>
                </ul>
                <p>Correct Answer: <?php echo htmlspecialchars($question['correct_option']); ?></p>
                <p>Points: <?php echo htmlspecialchars($question['points']); ?></p>

                <!-- Edit Form -->
                <form method="post">
                    <input type="hidden" name="question_id" value="<?php echo $question['question_id']; ?>">
                    <label>Edit Question:</label>
                    <input type="text" name="edit_question_text" value="<?php echo htmlspecialchars($question['question_text']); ?>" d>

                    <label>Edit Option A:</label>
                    <input type="text" name="edit_option_a" value="<?php echo htmlspecialchars($options['A']); ?>" required>

                    <label>Edit Option B:</label>
                    <input type="text" name="edit_option_b" value="<?php echo htmlspecialchars($options['B']); ?>" required>

                    <label>Edit Option C:</label>
                    <input type="text" name="edit_option_c" value="<?php echo htmlspecialchars($options['C']); ?>" required>

                    <label>Edit Option D:</label>
                    <input type="text" name="edit_option_d" value="<?php echo htmlspecialchars($options['D']); ?>" required>

                    <label>Edit Correct Option:</label>
                    <select name="edit_correct_option" required>
                        <option value="A" <?php echo $question['correct_option'] === 'A' ? 'selected' : ''; ?>>A</option>
                        <option value="B" <?php echo $question['correct_option'] === 'B' ? 'selected' : ''; ?>>B</option>
                        <option value="C" <?php echo $question['correct_option'] === 'C' ? 'selected' : ''; ?>>C</option>
                        <option value="D" <?php echo $question['correct_option'] === 'D' ? 'selected' : ''; ?>>D</option>
                    </select>

                    <label>Edit Points:</label>
                    <input type="number" name="edit_points" min="1" value="<?php echo htmlspecialchars($question['points'] ?? 1); ?>" required>

                    <button type="submit" name="edit_question">Save Changes</button>
                </form>

               <!-- Remove Form -->
<form method="post" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this question?');">
    <input type="hidden" name="question_id" value="<?php echo $question['question_id']; ?>">
    <button type="submit" name="remove_question">Remove</button>
</form>

            </li>           
        <?php endforeach; ?>
    </ol>
</body>
</html>
