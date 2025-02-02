<?php
require 'auth.php';
require 'config.php';

// Retrieve assessment_id and session details
$assessment_id = $_GET['assessment_id'] ?? null;
$roomId = $_SESSION['room_id'] ?? null;
$studentId = $_SESSION['student_id'] ?? null;

if (!$assessment_id || !$roomId || !$studentId) {
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

    // Get time limit (in minutes) and convert to seconds
    $timeLimit = $assessment['time_limit'] * 60;
    $startTime = time();
    $endTime = $startTime + $timeLimit;
} catch (PDOException $e) {
    exit("Error fetching assessment: " . $e->getMessage());
}

// Fetch questions for the assessment
try {
    $stmt = $pdo->prepare("SELECT * FROM questions_esy_tbl WHERE assessment_id = ?");
    $stmt->execute([$assessment_id]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($questions)) {
        exit("No questions are available for this assessment.");
    }
} catch (PDOException $e) {
    exit("Error fetching questions: " . $e->getMessage());
}

// Handle chat submission via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax']) && $_POST['ajax'] === 'sendMessage') {
    $content = trim($_POST['content']);
    if (!empty($content)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO chat_history (assessment_id, student_id, content, time_and_date) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$assessment_id, $studentId, $content]);
            exit(json_encode(['success' => true]));
        } catch (PDOException $e) {
            exit(json_encode(['error' => $e->getMessage()]));
        }
    }
    exit(json_encode(['error' => 'Empty message']));
}

// Fetch chat messages via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ajax']) && $_GET['ajax'] === 'fetchMessages') {
    try {
        $stmt = $pdo->prepare("SELECT chat_history.*, student_tbl.username FROM chat_history 
                               JOIN student_tbl ON chat_history.student_id = student_tbl.student_id 
                               WHERE chat_history.assessment_id = ? ORDER BY chat_history.time_and_date ASC");
        $stmt->execute([$assessment_id]);
        $chatMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        exit(json_encode($chatMessages));
    } catch (PDOException $e) {
        exit(json_encode(['error' => $e->getMessage()]));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Take Essay Assessment: <?php echo htmlspecialchars($assessment['name']); ?> - Collaborative Assessment</title>
    <link rel="stylesheet" href="css/take_collab.css">
</head>
<body>

<div class="main-content">
    <h1><?php echo htmlspecialchars($assessment['name']); ?> (Room ID: <?php echo $roomId; ?>)</h1>
    <h3>Write your answers and collaborate with your team!</h3>

    <!-- Timer -->
    <div id="timer-container">
        <h3>Time Remaining: <span id="timer">--:--</span></h3>
    </div>

    <!-- Debugging: Show if questions are loaded -->
    <?php if (empty($questions)): ?>
        <p style="color: red;">No questions available for this assessment.</p>
    <?php endif; ?>

    <form id="collab-form" method="post" action="submit_essay_collab.php">
        <?php foreach ($questions as $index => $question): ?>
            <div class="question">
                <h4>Question <?php echo $index + 1; ?>:</h4>
                <p><?php echo htmlspecialchars($question['question_text'] ?? 'No question text available'); ?></p>
                <textarea name="answers[<?php echo $question['question_id'] ?? 'unknown'; ?>]" rows="5" cols="50" required></textarea>
            </div>
        <?php endforeach; ?>

        <input type="hidden" name="assessment_id" value="<?php echo $assessment_id; ?>">
        <input type="hidden" name="room_id" value="<?php echo $roomId; ?>">
        <input type="hidden" id="end-time" value="<?php echo $endTime; ?>">
        <button type="submit">Submit</button>
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

<script>
const assessmentId = "<?php echo $assessment_id; ?>";
const studentId = "<?php echo $studentId; ?>";
const chatMessages = document.getElementById('chat-messages');
const chatInput = document.getElementById('chat-message-input');
const sendButton = document.getElementById('send-message');
const endTime = parseInt(document.getElementById('end-time').value) * 1000;

// Timer Function
function startTimer() {
    function updateTimer() {
        const now = new Date().getTime();
        const remainingTime = Math.max(0, endTime - now);
        const minutes = Math.floor((remainingTime / 1000) / 60);
        const seconds = Math.floor((remainingTime / 1000) % 60);
        document.getElementById('timer').innerText = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
        if (remainingTime <= 0) {
            clearInterval(timerInterval);
            document.getElementById('timer').innerText = "Time's up!";
            document.getElementById('collab-form').submit();
        }
    }
    updateTimer();
    const timerInterval = setInterval(updateTimer, 1000);
}

startTimer();

// Fetch chat messages dynamically
function fetchChatMessages() {
    fetch(`?ajax=fetchMessages&assessment_id=${assessmentId}`)
        .then(response => response.json())
        .then(data => {
            chatMessages.innerHTML = '';
            data.forEach(msg => {
                const messageDiv = document.createElement('div');
                messageDiv.innerHTML = `<strong>${msg.username}:</strong> ${msg.content}`;
                chatMessages.appendChild(messageDiv);
            });
        });
}
setInterval(fetchChatMessages, 2000);

// Send messages dynamically
sendButton.addEventListener('click', () => {
    const content = chatInput.value.trim();
    if (content) {
        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `ajax=sendMessage&assessment_id=${assessmentId}&student_id=${studentId}&content=${encodeURIComponent(content)}`
        }).then(response => response.json())
          .then(() => {
              chatInput.value = '';
              fetchChatMessages();
          });
    }
});
</script>

</body>
</html>
