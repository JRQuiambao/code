<?php
require 'auth.php';
require 'config.php';

// Retrieve assessment_id and room_id
$assessment_id = $_GET['assessment_id'] ?? null;
$roomId = $_SESSION['room_id'] ?? null;

if (!$assessment_id || !$roomId) {
    exit("Invalid session or assessment ID. Please return to the lobby.");
}

// Fetch assessment details
try {
    $stmt = $pdo->prepare("SELECT * FROM assessment_tbl WHERE assessment_id = ?");
    $stmt->execute([$assessment_id]);
    $assessment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$assessment) {
        exit("Error: Assessment not found.");
    }

    // Get the time limit (in minutes) and convert to seconds
    $timeLimit = $assessment['time_limit'] * 60;
} catch (PDOException $e) {
    exit("Error fetching assessment: " . $e->getMessage());
}

// Fetch questions for the assessment
try {
    $stmt = $pdo->prepare("SELECT * FROM questions_mcq_tbl WHERE assessment_id = ?");
    $stmt->execute([$assessment_id]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($questions)) {
        exit("No questions are available for this assessment.");
    }
} catch (PDOException $e) {
    exit("Error fetching questions: " . $e->getMessage());
}

$totalQuestions = count($questions);
$healthPerQuestion = 100 / $totalQuestions;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Take Assessment: <?php echo htmlspecialchars($assessment['name']); ?> - Collaborative Assessment</title>
    <link rel="stylesheet" href="css/take_collab.css">
</head>
<body>
<div class="main-content">
    <h1><?php echo htmlspecialchars($assessment['name']); ?> (Room ID: <?php echo $roomId; ?>)</h1>
    <h3>Pick the best answer for each question and fight the monster, you’ve got this!</h3>

    <!-- Timer -->
    <div id="timer-container">
        <h3>Time Remaining: <span id="timer">--:--</span></h3>
    </div>

    <!-- Boss Health Bar -->
    <div id="boss-health-container">
        <h3>Boss Health</h3>
        <div id="boss-health-bar">
            <div id="boss-health-progress" style="width: 100%;"></div>
        </div>
        <p>Question <span id="current-question">1</span> of <?php echo $totalQuestions; ?></p>
    </div>

    <!-- Boss Monster Image -->
    <div style="text-align: center;">
        <img src="image/boss_monster.png" alt="Boss Monster" width="400" height="400">
    </div>

    <form id="collab-form">
        <?php foreach ($questions as $index => $question): ?>
            <div class="question">
                <p><?php echo htmlspecialchars($question['question_text']); ?></p>
                <?php
                $options = json_decode($question['options'], true);
                foreach ($options as $key => $value): ?>
                    <label>
                        <input type="radio" name="answers[<?php echo $question['question_id']; ?>]"
                               data-question="<?php echo $question['question_id']; ?>"
                               value="<?php echo htmlspecialchars($key); ?>"
                               onclick="submitAnswer(<?php echo $question['question_id']; ?>, '<?php echo $key; ?>')">
                        <?php echo htmlspecialchars($value); ?>
                    </label><br>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        <div class="next-button-container">
            <button id="next-button" type="button" disabled>Next</button>
            <button id="submit-button" type="button" style="display: none;" disabled>Submit</button>
            <span id="answer-count">0/0 users answered</span>
        </div>
    </form>
</div>

<div class="chat-panel">
    <h3>Room Chat</h3>
    <div class="chat-messages" id="chat-messages"></div>
    <div class="chat-input">
        <input type="text" id="chat-message-input" placeholder="Type a message">
        <button id="send-message">Send</button>
    </div>
</div>

<script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>
<script>
const assessmentId = "<?php echo $assessment_id; ?>"; // Unique identifier for this assessment
const timeLimit = <?php echo $timeLimit; ?>; // Time limit in seconds
let remainingTime;

// Load the saved remaining time from localStorage if available
if (localStorage.getItem(`remainingTime_${assessmentId}`)) {
    remainingTime = parseInt(localStorage.getItem(`remainingTime_${assessmentId}`), 10);
} else {
    remainingTime = timeLimit; // Default to the full time limit
}

// Timer functionality
function startTimer() {
    const timerDisplay = document.getElementById('timer');

    const timerInterval = setInterval(() => {
        if (remainingTime <= 0) {
            clearInterval(timerInterval);
            alert('Time is up! Submitting your assessment...');
            document.getElementById('submit-button').click();
        } else {
            remainingTime--;
            const minutes = Math.floor(remainingTime / 60);
            const seconds = remainingTime % 60;
            timerDisplay.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

            // Save the remaining time to localStorage
            localStorage.setItem(`remainingTime_${assessmentId}`, remainingTime);
        }
    }, 1000);
}

// Start the timer
startTimer();

// Save the remaining time when the user leaves the page
window.addEventListener('beforeunload', () => {
    localStorage.setItem(`remainingTime_${assessmentId}`, remainingTime);
});

// Clear the saved remaining time upon successful submission
document.getElementById('submit-button').addEventListener('click', () => {
    localStorage.removeItem(`remainingTime_${assessmentId}`);
});
</script>

<?php 

require 'js/take_collab.php';

?>
</body>
</html>
