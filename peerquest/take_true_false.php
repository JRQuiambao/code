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
$stmt = $pdo->prepare("SELECT Attempt FROM answers_tf_tbl WHERE student_id = ? AND assessment_id = ?");
$stmt->execute([$_SESSION['student_id'], $assessment_id]);
$user_attempt = $stmt->fetch(PDO::FETCH_ASSOC);

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

$time_limit = $assessment['time_limit'] * 60; // Convert minutes to seconds

// Fetch questions
$stmt = $pdo->prepare("SELECT * FROM questions_tf_tbl WHERE assessment_id = ? ORDER BY question_id ASC");
$stmt->execute([$assessment_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$questions) {
    echo "No questions found for this assessment.";
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_assessment'])) {
    if (!isset($_POST['answers']) || empty($_POST['answers'])) {
        echo "Please answer all questions.";
        exit();
    }

    try {
        $pdo->beginTransaction();

        foreach ($_POST['answers'] as $question_id => $answer) {
            $answer_text = ($answer === "1") ? 'True' : 'False';

            // Fetch the correct answer and points for the question
            $stmt = $pdo->prepare("SELECT correct_answer, points FROM questions_tf_tbl WHERE question_id = ?");
            $stmt->execute([$question_id]);
            $question = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$question) {
                throw new Exception("Question not found: $question_id");
            }

            $is_correct = ($answer_text === $question['correct_answer']) ? $question['points'] : 0;

            // Insert student's answer
            $stmt = $pdo->prepare("
                INSERT INTO answers_tf_tbl (student_id, assessment_id, question_id, answer_text, correct_answer, Attempt)
                VALUES (?, ?, ?, ?, ?, 1)
            ");
            $stmt->execute([$_SESSION['student_id'], $assessment_id, $question_id, $answer_text, $is_correct]);
        }

        if ($user_attempt) {
            $stmt = $pdo->prepare("UPDATE answers_tf_tbl SET Attempt = Attempt + 1 WHERE student_id = ? AND assessment_id = ?");
            $stmt->execute([$_SESSION['student_id'], $assessment_id]);
        }

        $pdo->commit();
        echo "<p>Assessment submitted successfully!</p>";
        echo '<a href="student_dashboard.php" class="btn btn-primary">Back to Assessments</a>';
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "An error occurred: " . $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take True/False Assessment: <?php echo htmlspecialchars($assessment['name']); ?></title>
    <link rel="stylesheet" href="css/take_tf.css">
</head>

<body>
    <div class="assessment-container">
        <h2 class="assessment-title">True or False: <?php echo htmlspecialchars($assessment['name']); ?></h2>
        <p><strong>Instructions:</strong> <?php echo htmlspecialchars($assessment['instructions'] ?? 'No instructions provided.'); ?></p>
        <p><strong>Total Points:</strong> <?php echo htmlspecialchars($assessment['total_points']); ?></p>

        <div class="timer-box">
            <progress id="progressBar" max="100" value="100"></progress>
            <div id="timer" class="timer-text"></div>
        </div>

        <form method="post">
            <!-- Answer Cards (True/False) -->
            <div class="answer-cards">
                <div class="card true-card" id="true-drop">TRUE</div>
                <div class="card false-card" id="false-drop">FALSE</div>
            </div>

            <!-- Question Pile -->
            <div class="question-pile" id="question-stack">
                <?php $question_number = 1; ?>
                <?php foreach ($questions as $question): ?>
                    <div class="question-card draggable" data-id="<?php echo htmlspecialchars($question['question_id']); ?>">
                        <div class="card-back">PeerQuest</div>
                        <div class="card-front">
                            <div class="header">Question <?php echo $question_number++; ?></div>
                            <p><?php echo htmlspecialchars($question['question_text']); ?> (<?php echo htmlspecialchars($question['points']); ?> points)</p>
                        </div>
                        <input type="hidden" name="answers[<?php echo htmlspecialchars($question['question_id']); ?>]" value="">
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Skip Area -->
            <div class="drop-area" id="skip-drop">
                Drag and drop here to skip
            </div>

            <div class="submit-container">
                <button class="submit-btn" type="submit" name="submit_assessment">Submit Assessment</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const timeLimit = <?php echo $time_limit; ?>;
            const storageKey = `tf_assessment_timer_<?php echo $assessment_id; ?>`;

            let remainingTime = localStorage.getItem(storageKey)
                ? parseInt(localStorage.getItem(storageKey))
                : timeLimit;

            const timerElement = document.getElementById("timer");

            const updateTimerDisplay = () => {
                const minutes = Math.floor(remainingTime / 60);
                const seconds = remainingTime % 60;
                timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, "0")}`;
            };

            const timerInterval = setInterval(() => {
                if (remainingTime > 0) {
                    remainingTime--;
                    localStorage.setItem(storageKey, remainingTime);
                    updateTimerDisplay();
                } else {
                    clearInterval(timerInterval);
                    localStorage.removeItem(storageKey);
                    alert("Time is up! Submitting your answers.");
                    document.querySelector('form').submit();
                }
            }, 1000);

            updateTimerDisplay();
            window.addEventListener("beforeunload", () => {
                localStorage.setItem(storageKey, remainingTime);
            });

            // Drag and Drop Logic
            document.querySelectorAll('.question-card').forEach(card => {
                card.addEventListener('click', function () {
                    card.classList.add('active', 'flipped');
                });

                card.draggable = true;
                card.addEventListener('dragstart', (e) => {
                    e.dataTransfer.setData('text/plain', card.dataset.id);
                    setTimeout(() => {
                        card.style.display = 'none';
                    }, 0);
                });

                card.addEventListener('dragend', () => {
                    card.style.display = 'block';
                });
            });

            document.querySelectorAll('#true-drop, #false-drop, #skip-drop').forEach(dropArea => {
                dropArea.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    dropArea.classList.add('drag-over');
                });

                dropArea.addEventListener('dragleave', () => {
                    dropArea.classList.remove('drag-over');
                });

                dropArea.addEventListener('drop', (e) => {
                    e.preventDefault();
                    dropArea.classList.remove('drag-over');
                    const cardId = e.dataTransfer.getData('text/plain');
                    const card = document.querySelector(`.question-card[data-id="${cardId}"]`);
                    dropArea.appendChild(card);
                    card.classList.remove('flipped');

                    // Assign answer value based on drop area
                    const inputField = card.querySelector('input');
                    if (dropArea.id === 'true-drop') {
                        inputField.value = "1";
                    } else if (dropArea.id === 'false-drop') {
                        inputField.value = "0";
                    } else {
                        inputField.value = "";
                    }

                    card.style.display = 'block';
                });
            });
        });
    </script>
</body>
</html>