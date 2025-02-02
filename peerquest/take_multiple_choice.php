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
    <link rel="stylesheet" href="css/take_mcq_indiv.css">
</head>
<body>
<div class="assessment-container">
    <h2 class="assessment-title">MCQ Individual: <?php echo htmlspecialchars($assessment['name']); ?></h2>
    <p><strong>Instructions:</strong> <?php echo htmlspecialchars($assessment['instructions'] ?? 'No instructions provided.'); ?></p>
    <p><strong>Total Points:</strong> <?php echo htmlspecialchars($assessment['total_points']); ?></p>

    <div class="timer-box">
    <progress id="progressBar" max="100" value="100"></progress>
    <div id="timer" class="timer-text"></div>
    </div>

    <div class="health-bar-wrapper">
    <h3 class="boss-health-text">Boss Health</h3>
    <div class="health-bar-container">
        <img src="images/boss_heart.png" alt="Health" class="health-icon">
        <div class="health-bar">
            <div id="boss-health-progress" class="health-progress"></div>
        </div>
    </div>
        <!-- Boss GIF added here -->
        <div class="boss-gif">
                <img src="images/indiv_monster.gif" alt="Boss GIF" id="boss-image">
            </div>
        </div>

       
        <p class = "question-text">Question <span id="current-question">1</span> of <?php echo $totalQuestions; ?></p>
        <form method="post">
            <?php foreach ($questions as $index => $question): ?>
                <div class="question-container">
                <p class="question-number">QUESTION #<?php echo ($index + 1); ?> <span class="points">(<?php echo $question['points']; ?> points)</span></p>
                <p class="question-text"><?php echo htmlspecialchars($question['question_text']); ?></p>
                <div class="option-container">
                    <?php
                    $options = json_decode($question['options'], true);
                    foreach ($options as $key => $value): ?>
                        <div class="option-btn" onclick="selectOption('<?php echo $key; ?>', '<?php echo $question['question_id']; ?>')">
                            <input type="hidden" name="answers[<?php echo htmlspecialchars($question['question_id']); ?>]" id="answer_<?php echo htmlspecialchars($question['question_id']); ?>" value="">
                            <span class="choice-circle"><?php echo strtoupper($key); ?></span>
                            <?php echo htmlspecialchars($value); ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                </div>
            <?php endforeach; ?>
            <div class="submit-container">
                <button type="button" id="next-button" class="submit-btn">Next</button>
                <button type="submit" id="submit-button" class="submit-btn" style="display: none;">Submit Assessment</button>
            </div>
        </form>
    </div>

    <script>
            function selectOption(option, questionId) {
                document.getElementById('answer_' + questionId).value = option;

                // Remove 'selected' class from all options of the same question
                const options = document.querySelectorAll(`[id^='answer_${questionId}']`).forEach(el => {
                    el.parentElement.classList.remove('selected');
                });

                // Add 'selected' class to the clicked option
                event.target.classList.add('selected');
            }

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
        document.addEventListener("DOMContentLoaded", function () {
            const totalQuestions = <?php echo $totalQuestions; ?>;
            const healthPerQuestion = 100 / totalQuestions;
            const storageKeyIndex = `current_question_<?php echo $assessment_id; ?>`;
            const questions = document.querySelectorAll(".question-container");
            const bossHealthProgress = document.getElementById("boss-health-progress");
            const currentQuestionDisplay = document.getElementById("current-question");
            const nextButton = document.getElementById("next-button");
            const submitButton = document.getElementById("submit-button");
            const timerElement = document.getElementById("timer");

            let currentQuestionIndex = localStorage.getItem(storageKeyIndex) 
                ? parseInt(localStorage.getItem(storageKeyIndex)) 
                : 0;

            function updateHealthBars() {
                const bossHealth = Math.max(0, 100 - (currentQuestionIndex * healthPerQuestion));
                bossHealthProgress.style.width = `${bossHealth}%`;
                currentQuestionDisplay.textContent = currentQuestionIndex + 1;
            }

            

            function toggleButtons() {
                if (currentQuestionIndex === totalQuestions - 1) {
                    nextButton.style.display = "none";
                    submitButton.style.display = "inline-block";
                } else {
                    nextButton.style.display = "inline-block";
                    submitButton.style.display = "none";
                }
            }

            questions[currentQuestionIndex].classList.add("active");
            updateHealthBars();
            toggleButtons();

            nextButton.addEventListener("click", function () {
                if (currentQuestionIndex < totalQuestions - 1) {
                    questions[currentQuestionIndex].classList.remove("active");
                    currentQuestionIndex++;
                    questions[currentQuestionIndex].classList.add("active");

                    localStorage.setItem(storageKeyIndex, currentQuestionIndex);
                    updateHealthBars();
                    toggleButtons();
                }
            });

            window.addEventListener("beforeunload", () => {
                localStorage.setItem(storageKeyIndex, currentQuestionIndex);
            });
        });

        document.addEventListener("DOMContentLoaded", function () {
    const assessmentId = <?php echo json_encode($assessment_id); ?>;
    const totalQuestions = <?php echo $totalQuestions; ?>;
    const healthPerQuestion = 100 / totalQuestions;
    const healthProgress = document.getElementById("boss-health-progress");

    // Initialize boss health when assessment starts
    if (!localStorage.getItem(`boss_health_${assessmentId}`)) {
        localStorage.setItem(`boss_health_${assessmentId}`, 100); // Set health to 100 at start
    }

    // Function to update health bar dynamically
    function updateHealthBar() {
        let bossHealth = localStorage.getItem(`boss_health_${assessmentId}`) || 100;
        bossHealth = parseFloat(bossHealth);
        healthProgress.style.width = `${bossHealth}%`;

        // Apply color changes based on health percentage
        if (bossHealth > 75) {
            healthProgress.style.background = "linear-gradient(to right, #4ac36e, #6aaf4c)"; // Green
        } else if (bossHealth > 50) {
            healthProgress.style.background = "linear-gradient(to right, #aaaf4c, #6aaf4c)"; // Yellow-Green
        } else if (bossHealth > 25) {
            healthProgress.style.background = "linear-gradient(to right, #af7f4c, #aaaf4c)"; // Orange
        } else {
            healthProgress.style.background = "linear-gradient(to right, #cb2320, #af7f4c)"; // Red
        }
    }

    // Call function to reflect health bar at start
    updateHealthBar();

    // Function to reduce health after answering a question
    function decreaseHealth() {
        let bossHealth = parseFloat(localStorage.getItem(`boss_health_${assessmentId}`)) || 100;
        bossHealth = Math.max(0, bossHealth - healthPerQuestion);
        localStorage.setItem(`boss_health_${assessmentId}`, bossHealth);
        updateHealthBar();
    }

    // Hook into form submission to reduce health dynamically
    document.querySelector('form').addEventListener('submit', function (event) {
        decreaseHealth();
    });

});

    </script>
</body>
</html>
