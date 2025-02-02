<?php
require 'auth.php';
require 'config.php';

// Fetch assessment details
$assessment_id = $_GET['assessment_id'] ?? null;
if (!$assessment_id) {
    echo "Invalid assessment ID.";
    exit();
}

// Check if the student has already attempted this assessment
$stmt = $pdo->prepare("SELECT Attempt FROM answers_esy_tbl WHERE student_id = ? AND assessment_id = ?");
$stmt->execute([$_SESSION['student_id'], $assessment_id]);
$user_attempt = $stmt->fetch(PDO::FETCH_ASSOC);

// If student has already attempted
if ($user_attempt && $user_attempt['Attempt'] >= 1) {
    echo "You have already attempted this assessment.";
    exit();
}

// Fetch assessment details
$stmt = $pdo->prepare("SELECT * FROM assessment_tbl WHERE assessment_id = ?");
$stmt->execute([$assessment_id]);
$assessment = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$assessment) {
    echo "Assessment not found.";
    exit();
}

// Fetch questions for the assessment
$stmt = $pdo->prepare("SELECT * FROM questions_esy_tbl WHERE assessment_id = ?");
$stmt->execute([$assessment_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_assessment'])) {
    if (!isset($_POST['answers']) || empty($_POST['answers'])) {
        echo "Please provide answers to all questions.";
        exit();
    }

    // Record answers and increment attempt count
    try {
        $pdo->beginTransaction();

        foreach ($_POST['answers'] as $question_id => $answer_text) {
            $stmt = $pdo->prepare("INSERT INTO answers_esy_tbl (student_id, assessment_id, question_id, answer_text, Attempt) VALUES (?, ?, ?, ?, 1)");
            $stmt->execute([$_SESSION['student_id'], $assessment_id, $question_id, $answer_text]);
        }

        $stmt = $pdo->prepare("UPDATE answers_esy_tbl SET Attempt = Attempt + 1 WHERE student_id = ? AND assessment_id = ?");
        $stmt->execute([$_SESSION['student_id'], $assessment_id]);

        $pdo->commit();
        echo "<p>Assessment submitted successfully!</p>";
        echo '<a href="student_dashboard.php" class="btn btn-primary">Back to Assessments</a>';
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "An error occurred: " . $e->getMessage();
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeerQuest (ESSAY) <?php echo htmlspecialchars($assessment['name']); ?></title>
    <link rel="stylesheet" href="css/take_essay.css">
</head>
<body>

<div class="assessment-container">
    <h2 class="assessment-title">ESSAY: <?php echo htmlspecialchars($assessment['name']); ?></h2>
    <p><strong>Instructions:</strong> <?php echo htmlspecialchars($assessment['instructions'] ?? 'No instructions provided.'); ?></p>
    <p><strong>Total Points:</strong> <?php echo htmlspecialchars($assessment['total_points']); ?></p>

    <div class="timer-box">
    <progress id="progressBar" max="100" value="100"></progress>
    <div id="timer" class="timer-text"></div>
    </div>


    <form method="post" class="assessment-form">
        <?php foreach ($questions as $index => $question): ?>
            <div class="question-card">
            <p class="question-number">QUESTION #<?php echo ($index + 1); ?> <span class="points">(<?php echo $question['points']; ?> points)</span></p>
                </p>
                <p class="question-text"><?php echo htmlspecialchars($question['question_text']); ?></p>
                <textarea class="input-field" 
                    name="answers[<?php echo $question['question_id']; ?>]" 
                    required 
                    placeholder="Type your answer here..."></textarea>
            </div>
        <?php endforeach; ?>
        <div class="submit-container">
            <button class="submit-btn" type="submit" name="submit_assessment">Submit Assessment</button>
        </div>
    </form>
</div>


<script>
    const assessmentId = <?php echo json_encode($assessment_id); ?>;
    const totalTime = <?php echo (int) $assessment['time_limit']; ?> * 60;  // Total time in seconds

    // Get start time from localStorage or set a new one
    let startTime = localStorage.getItem(`startTime_${assessmentId}`);
    if (!startTime) {
        startTime = Date.now();
        localStorage.setItem(`startTime_${assessmentId}`, startTime);
    }

    function updateTimer() {
        const elapsedTime = Math.floor((Date.now() - startTime) / 1000); // Calculate elapsed time in seconds
        const remainingTime = totalTime - elapsedTime;

        if (remainingTime <= 0) {
            clearInterval(timerInterval);
            localStorage.removeItem(`startTime_${assessmentId}`); // Clear storage after submission
            alert("Time's up! Your assessment will be submitted.");
            document.querySelector('form').submit();
            return;
        }

        const minutes = Math.floor(remainingTime / 60);
        const seconds = remainingTime % 60;
        document.getElementById('timer').textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;

        const progressPercentage = (remainingTime / totalTime) * 100;
        document.getElementById('progressBar').value = progressPercentage;
    }

    // Run the timer function every second
    const timerInterval = setInterval(updateTimer, 1000);
    updateTimer();
</script>
