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
    <link rel="stylesheet" href="css/take_mcq_collab.css">
</head>
<body>

<div class="assessment-container">
    <h2 class="assessment-title">MCQ - COLLAB: <?php echo htmlspecialchars($assessment['name']); ?> (Room ID: <?php echo $roomId; ?>)</h2>
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
        <p class = "question-text"> <span id="answer-count">0/0 users answered</span></p>
        <form id="collab-form">
            <?php if (!empty($questions)): ?>
                <?php foreach ($questions as $index => $question): ?>
                    <div class="question-container <?php echo $index === 0 ? 'active' : ''; ?>">
                        <p class="question-number">QUESTION #<?php echo ($index + 1); ?> <span class="points">(<?php echo $question['points']; ?> points)</span></p>
                        <p class="question-text"><?php echo htmlspecialchars($question['question_text']); ?></p>
                        <div class="option-container">
                            <?php $options = json_decode($question['options'], true); ?>
                            <?php foreach ($options as $key => $value): ?>
                                <div class="option-btn" onclick="selectOption(this, '<?php echo $question['question_id']; ?>', '<?php echo $key; ?>')">
                                    <span class="choice-circle"><?php echo strtoupper($key); ?></span>
                                    <?php echo htmlspecialchars($value); ?>
                                    <input type="hidden" name="answers[<?php echo $question['question_id']; ?>]" value="<?php echo htmlspecialchars($key); ?>">
                                    <span class="users-answered" id="users-answered-<?php echo $question['question_id']; ?>-<?php echo $key; ?>">0 Users</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="error-message">No questions available for this assessment.</p>
            <?php endif; ?>
        
            <div class="next-button-container">
                <button id="next-button" type="button" disabled>Next</button>
                <button id="submit-button" type="submit" style="display: none;" disabled>Submit</button>
                
            </div>
        </form>
    </div>

<div id="chat-container">
    <div id="chat-header">Room Chat <span id="toggle-chat">▼</span></div>
    <div class="chat-panel">
        <div class="chat-messages" id="chat-messages"></div>
        <div class="chat-input">
            <input type="text" id="chat-message-input" placeholder="Type a message">
            <button id="send-message">Send</button>
        </div>
    </div>
</div>


<script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>
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

    document.addEventListener("DOMContentLoaded", function () {
    const chatPanel = document.querySelector(".chat-panel");
    const chatHeader = document.getElementById("chat-header");
    const chatMessages = document.getElementById("chat-messages");
    const chatInput = document.getElementById("chat-message-input");
    const sendMessage = document.getElementById("send-message");
    const toggleChatIcon = document.getElementById("toggle-chat");

    // Get assessment_id from PHP
    const assessmentId = <?php echo json_encode($_GET['assessment_id']); ?>;

    // Unique key per assessment to prevent mixing chats
    const chatStorageKey = `chatMessages_${assessmentId}`;

    // Clear old chat when a new assessment starts
    if (!localStorage.getItem(chatStorageKey)) {
        localStorage.setItem(chatStorageKey, JSON.stringify([])); // Reset chat
    }

    // Load messages on page load
    loadMessages();

    // Toggle chat panel visibility
    chatHeader.addEventListener("click", function () {
        chatPanel.style.display = chatPanel.style.display === "none" ? "flex" : "none";
        toggleChatIcon.textContent = chatPanel.style.display === "none" ? "▼" : "▲";
    });

    // Send message on button click
    sendMessage.addEventListener("click", sendChatMessage);

    // Send message on "Enter" key
    chatInput.addEventListener("keypress", function (event) {
        if (event.key === "Enter") {
            sendChatMessage();
        }
    });

    function sendChatMessage() {
        const messageText = chatInput.value.trim();
        if (messageText === "") return;

        // Get username from PHP session
        const username = <?php echo json_encode($_SESSION['username']); ?>;

        // Store message object
        const messageData = {
            username: username,
            text: messageText
        };

        // Save to localStorage
        let messages = JSON.parse(localStorage.getItem(chatStorageKey)) || [];
        messages.push(messageData);
        localStorage.setItem(chatStorageKey, JSON.stringify(messages));

        // Display message instantly
        appendMessage(messageData);

        // Clear input and auto-scroll
        chatInput.value = "";
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function appendMessage(messageData) {
        const message = document.createElement("div");
        message.textContent = `${messageData.username}: ${messageData.text}`;
        chatMessages.appendChild(message);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function loadMessages() {
        let messages = JSON.parse(localStorage.getItem(chatStorageKey)) || [];
        chatMessages.innerHTML = ""; // Clear messages on reload
        messages.forEach(appendMessage);
    }
});

</script>

<?php 

require 'js/take_collab.php';

?>
</body>
</html>
