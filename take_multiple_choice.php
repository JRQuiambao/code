<?php
require 'auth.php';
require 'config.php';

// Helper function for error handling
function showErrorAndExit($message) {
    echo htmlspecialchars($message);
    exit();
}

// Get assessment ID
$assessment_id = $_GET['assessment_id'] ?? null;
if (!$assessment_id) {
    showErrorAndExit("No assessment selected.");
}

// Check if the student has already attempted this assessment
$stmt = $pdo->prepare("SELECT COUNT(*) AS attempt_count FROM answers_mcq_tbl WHERE student_id = ? AND assessment_id = ?");
$stmt->execute([$_SESSION['student_id'], $assessment_id]);
$user_attempt = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user_attempt && $user_attempt['attempt_count'] > 0) {
    showErrorAndExit("You have already attempted this assessment.");
}

// Fetch assessment details with time_limit
$stmt = $pdo->prepare("SELECT * FROM assessment_tbl WHERE assessment_id = ?");
$stmt->execute([$assessment_id]);
$assessment = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$assessment) {
    showErrorAndExit("Assessment not found.");
}

$time_limit = $assessment['time_limit'] * 60; // Convert minutes to seconds

// Fetch questions for the assessment
$stmt = $pdo->prepare("SELECT * FROM questions_mcq_tbl WHERE assessment_id = ?");
$stmt->execute([$assessment_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!$questions) {
    showErrorAndExit("No questions found for this assessment.");
}

$totalQuestions = count($questions); // Calculate total questions
$healthPerQuestion = 100 / $totalQuestions; // Calculate health decrement per question

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answers = $_POST['answers'] ?? [];

    if (empty($answers)) {
        showErrorAndExit("Please answer all questions.");
    }

    try {
        $pdo->beginTransaction();

        foreach ($answers as $question_id => $selected_option) {
            // Fetch correct option and points
            $stmt = $pdo->prepare("SELECT correct_option, points FROM questions_mcq_tbl WHERE question_id = ?");
            $stmt->execute([$question_id]);
            $question = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$question) {
                throw new Exception("Question not found: $question_id");
            }

            $is_correct = ($selected_option === $question['correct_option']) ? $question['points'] : 0;

            // Insert the answer into answers_mcq_tbl
            $stmt = $pdo->prepare("INSERT INTO answers_mcq_tbl 
                (assessment_id, question_id, student_id, selected_option, correct_answer, attempt) 
                VALUES (?, ?, ?, ?, ?, 1)");
            $stmt->execute([$assessment_id, $question_id, $_SESSION['student_id'], $selected_option, $is_correct]);
        }

        $pdo->commit();

        // Fetch total score
        $stmt = $pdo->prepare("SELECT SUM(correct_answer) AS total_score 
            FROM answers_mcq_tbl 
            WHERE assessment_id = ? AND student_id = ?");
        $stmt->execute([$assessment_id, $_SESSION['student_id']]);
        $total_score = $stmt->fetch(PDO::FETCH_ASSOC)['total_score'];

        // Redirect to accomplished_mcq.php after successful submission
        $redirect_url = "accomplished_mcq.php?assessment_id=" . urlencode($assessment_id) .
                        "&class_id=" . urlencode($_SESSION['class_id']) .
                        "&totalScore=" . urlencode($total_score) .
                        "&maxScore=" . urlencode(array_sum(array_column($questions, 'points')));

        header("Location: $redirect_url");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        showErrorAndExit("An error occurred: " . $e->getMessage());
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Assessment: <?php echo htmlspecialchars($assessment['name']); ?></title>
    <style>
        .health-bar-container {
            margin-bottom: 20px;
            text-align: center;
        }

        .health-bar {
            height: 30px;
            background-color: #ff4c4c; /* Background color */
            border: 1px solid #000;
            width: 100%;
            position: relative;
        }

        .health-progress {
            height: 100%;
            background-color: #4CAF50; /* Progress color */
            width: 100%;
        }

        .question-container {
            display: none;
        }

        .question-container.active {
            display: block;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const totalQuestions = <?php echo $totalQuestions; ?>;
            const healthPerQuestion = 100 / (totalQuestions - 1);
            const storageKeyIndex = `current_question_<?php echo $assessment_id; ?>`; // Unique key for current question index
            const questions = document.querySelectorAll(".question-container");
            const bossHealthProgress = document.getElementById("boss-health-progress");
            const currentQuestionDisplay = document.getElementById("current-question");
            const nextButton = document.getElementById("next-button");
            const submitButton = document.getElementById("submit-button"); // Reference to Submit button
            const timerElement = document.getElementById("timer");
            const formElement = document.querySelector("form");

            // Timer Logic
            const timeLimit = <?php echo $time_limit; ?>;
            const storageKeyTimer = `assessment_timer_<?php echo $assessment_id; ?>`;
            let remainingTime = localStorage.getItem(storageKeyTimer)
                ? parseInt(localStorage.getItem(storageKeyTimer))
                : timeLimit;

            let currentQuestionIndex = localStorage.getItem(storageKeyIndex)
                ? parseInt(localStorage.getItem(storageKeyIndex))
                : 0;

            const updateTimerDisplay = () => {
                const minutes = Math.floor(remainingTime / 60);
                const seconds = remainingTime % 60;
                timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, "0")}`;
            };

            const timerInterval = setInterval(() => {
                if (remainingTime > 0) {
                    remainingTime--;
                    localStorage.setItem(storageKeyTimer, remainingTime);
                    updateTimerDisplay();
                } else {
                    clearInterval(timerInterval);
                    alert("Time is up! Submitting your answers.");
                    formElement.submit();
                }
            }, 1000);

            updateTimerDisplay();

            // Health Bar Logic
            function updateHealthBars() {
                const bossHealth = Math.max(0, 100 - (currentQuestionIndex * healthPerQuestion));
                bossHealthProgress.style.width = `${bossHealth}%`;
                currentQuestionDisplay.textContent = currentQuestionIndex + 1;
            }

            // Toggle Button Visibility
            function toggleButtons() {
                if (currentQuestionIndex === totalQuestions - 1) {
                    nextButton.style.display = "none"; // Hide Next button
                    submitButton.style.display = "inline-block"; // Show Submit button
                } else {
                    nextButton.style.display = "inline-block"; // Show Next button
                    submitButton.style.display = "none"; // Hide Submit button
                }
            }

            // Show Current Question
            questions[currentQuestionIndex].classList.add("active");
            updateHealthBars();
            toggleButtons(); // Ensure correct button visibility

            // Next Button Logic
            nextButton.addEventListener("click", function () {
                if (currentQuestionIndex < totalQuestions - 1) {
                    questions[currentQuestionIndex].classList.remove("active");
                    currentQuestionIndex++;
                    questions[currentQuestionIndex].classList.add("active");

                    // Save the current question index to localStorage
                    localStorage.setItem(storageKeyIndex, currentQuestionIndex);

                    updateHealthBars();
                    toggleButtons(); // Update button visibility after clicking
                }
            });

            // Save the current question index when the page is closed or refreshed
            window.addEventListener("beforeunload", () => {
                localStorage.setItem(storageKeyIndex, currentQuestionIndex);
            });
        });
    </script>
</head>
<body>
    <h2>Take Assessment: <?php echo htmlspecialchars($assessment['name']); ?></h2>
    <h3>Pick the best answer for each question to save yourself from the boss. Show off your skills—you’ve got this!</h3>
    <p>Time Remaining: <span id="timer"></span></p>
    <div>
        <div class="health-bar-container">
            <h3>Boss Health</h3>
            <div class="health-bar">
                <div id="boss-health-progress" class="health-progress"></div>
            </div>
        </div>

        <div class="health-bar-container">

        </div>
    </div>
    <p>Question <span id="current-question">1</span> of <?php echo $totalQuestions; ?></p>
    <form method="post">
        <?php foreach ($questions as $index => $question): ?>
            <div class="question-container">
                <p><?php echo htmlspecialchars($question['question_text']); ?></p>
                <?php
                $options = json_decode($question['options'], true);
                foreach ($options as $key => $value): ?>
                    <label>
                        <input type="radio" name="answers[<?php echo htmlspecialchars($question['question_id']); ?>]" value="<?php echo $key; ?>" required>
                        <?php echo "$key: $value"; ?>
                    </label><br>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        <button type="button" id="next-button">Next</button>
        <button type="submit" id="submit-button" style="display: none;">Submit Assessment</button>
    </form>
</body>
</html>
