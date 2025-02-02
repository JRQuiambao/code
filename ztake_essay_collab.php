<?php
require 'auth.php';
require 'config.php';
require 'js/count_users.php';

// Retrieve assessment_id and session details
$assessment_id = $_GET['assessment_id'] ?? null;
$roomId = $_SESSION['room_id'] ?? null;
$studentId = $_SESSION['student_id'] ?? null;

if (!$assessment_id || !$roomId || !$studentId) {
    exit("Invalid session or assessment ID. Please return to the lobby.");
}



// Fetch the number of users in the room
$userCount = countUsersInRoom($pdo, $roomId);

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
            $stmt = $pdo->prepare("INSERT INTO chat_history (assessment_id, room_id, student_id, content, time_and_date) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$assessment_id, $roomId, $studentId, $content]);

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
                       WHERE chat_history.assessment_id = ? AND chat_history.room_id = ? 
                       ORDER BY chat_history.time_and_date ASC");
        $stmt->execute([$assessment_id, $roomId]);

        $chatMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        exit(json_encode($chatMessages));
    } catch (PDOException $e) {
        exit(json_encode(['error' => $e->getMessage()]));
    }
}

// Fetch user count via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ajax']) && $_GET['ajax'] === 'fetchUserCount') {
    $roomId = $_GET['room_id'] ?? null;
    if ($roomId) {
        try {
            $userCount = countUsersInRoom($pdo, $roomId);
            exit(json_encode(['user_count' => $userCount]));
        } catch (PDOException $e) {
            exit(json_encode(['error' => $e->getMessage()]));
        }
    }
    exit(json_encode(['error' => 'Invalid room ID']));
}

// Get the current question index
$current_question_index = $_POST['current_question_index'] ?? 0;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $question = $questions[$current_question_index];
    $question_id = $question['question_id'];
    $answer = trim($_POST["answer_$question_id"]);

    // Insert answer with room_id and set grade to 0
    $stmt = $pdo->prepare("INSERT INTO answers_esy_collab_tbl (assessment_id, room_id, student_id, question_id, answer, grades, submitted_at)
                            VALUES (?, ?, ?, ?, ?, 0, NOW())
                            ON DUPLICATE KEY UPDATE answer = ?, grades = 0, submitted_at = NOW()");
    $stmt->execute([$assessment_id, $roomId, $studentId, $question_id, $answer, $answer]);

    if ($current_question_index < count($questions) - 1) {
        $current_question_index++;
    } else {
        header("Location: results.php?assessment_id=$assessment_id");
        exit();
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
    <h1><?php echo htmlspecialchars($assessment['name']); ?> (Room ID: <?php echo $roomId; ?>, Users: <span id="user-count"><?php echo $userCount; ?></span>)</h1>
    <h3>Write your answers and collaborate with your team!</h3>

    <!-- Timer -->
    <div id="timer-container">
        <h3>Time Remaining: <span id="timer">--:--</span></h3>
    </div>

    <form method="POST">
    <?php $question = $questions[$current_question_index]; ?>
    <div>
        <p><strong>Q<?php echo $current_question_index + 1; ?>:</strong> <?php echo htmlspecialchars($question['question_text']); ?></p>
        <textarea name="answer_<?php echo $question['question_id']; ?>" rows="4" cols="50"></textarea>
        <input type="hidden" name="current_question_index" value="<?php echo $current_question_index; ?>" />
    </div>
    <button type="submit" id="next-button">
        <?php echo ($current_question_index == count($questions) - 1) ? "Submit" : "Next"; ?>
    </button>
</form>

</div>

<!-- Chat Section -->
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
const roomId = "<?php echo $roomId; ?>";
const studentId = "<?php echo $studentId; ?>";
const chatMessages = document.getElementById('chat-messages');
const chatInput = document.getElementById('chat-message-input');
const sendButton = document.getElementById('send-message');
const endTime = <?php echo $endTime * 1000; ?>;

// Timer Function
function startTimer() {
    function updateTimer() {
        const now = Date.now();
        const remainingTime = Math.max(0, endTime - now);
        const minutes = Math.floor((remainingTime / 1000) / 60);
        const seconds = Math.floor((remainingTime / 1000) % 60);
        document.getElementById('timer').innerText = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
        if (remainingTime <= 0) {
            clearInterval(timerInterval);
            document.getElementById('timer').innerText = "Time's up!";
            document.querySelector("form").submit();
        }
    }
    updateTimer();
    const timerInterval = setInterval(updateTimer, 1000);
}
startTimer();

// Fetch chat messages dynamically
function fetchChatMessages() {
    fetch(`?ajax=fetchMessages&assessment_id=${assessmentId}&room_id=${roomId}`)
        .then(response => response.json())
        .then(data => {
            chatMessages.innerHTML = '';
            data.forEach(msg => {
                const messageDiv = document.createElement('div');
                messageDiv.innerHTML = `<strong>${msg.username}:</strong> ${msg.content}`;
                chatMessages.appendChild(messageDiv);
            });
        })
        .catch(error => console.error("Chat Fetch Error:", error));
}
setInterval(fetchChatMessages, 2000);

// Fetch and update user count dynamically
function fetchUserCount() {
    fetch(`?ajax=fetchUserCount&room_id=${roomId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('user-count').innerText = data.user_count;
        })
        .catch(error => console.error("User Count Fetch Error:", error));
}
setInterval(fetchUserCount, 5000);
fetchUserCount(); // Initial fetch

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

// Prevent form resubmission on refresh
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}
</script>

</body>
</html>




<?php
require 'auth.php';
require 'config.php';
require 'js/count_users.php';

// Retrieve assessment_id and session details
$assessment_id = isset($_GET['assessment_id']) ? $_GET['assessment_id'] : null;
$roomId = isset($_SESSION['room_id']) ? $_SESSION['room_id'] : null;
$studentId = isset($_SESSION['student_id']) ? $_SESSION['student_id'] : null;

if (!$assessment_id || !$roomId || !$studentId) {
    exit("Invalid session or assessment ID. Please return to the lobby.");
}

// Fetch the number of users in the room
$userCount = countUsersInRoom($pdo, $roomId);

// Fetch assessment details
try {
    $stmt = $pdo->prepare("SELECT * FROM assessment_tbl WHERE assessment_id = ?");
    $stmt->execute([$assessment_id]);
    $assessment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$assessment) {
        exit("Error: Assessment not found.");
    }

    // Get time limit (in minutes) and convert to seconds
    $timeLimit = isset($assessment['time_limit']) ? $assessment['time_limit'] * 60 : 0;
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
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    if (!empty($content)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO chat_history (assessment_id, room_id, student_id, content, time_and_date) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$assessment_id, $roomId, $studentId, $content]);
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
                               WHERE chat_history.assessment_id = ? AND chat_history.room_id = ? 
                               ORDER BY chat_history.time_and_date ASC");
        $stmt->execute([$assessment_id, $roomId]);
        $chatMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        exit(json_encode($chatMessages));
    } catch (PDOException $e) {
        exit(json_encode(['error' => $e->getMessage()]));
    }
}

// Fetch user count via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ajax']) && $_GET['ajax'] === 'fetchUserCount') {
    $roomId = isset($_GET['room_id']) ? $_GET['room_id'] : null;
    if ($roomId) {
        try {
            $userCount = countUsersInRoom($pdo, $roomId);
            exit(json_encode(['user_count' => $userCount]));
        } catch (PDOException $e) {
            exit(json_encode(['error' => $e->getMessage()]));
        }
    }
    exit(json_encode(['error' => 'Invalid room ID']));
}

// Get the current question index
$current_question_index = isset($_POST['current_question_index']) ? (int)$_POST['current_question_index'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($questions[$current_question_index])) {
    $question = $questions[$current_question_index];
    $question_id = $question['question_id'];
    $answer = isset($_POST["answer_$question_id"]) ? trim($_POST["answer_$question_id"]) : '';

    // Insert answer with room_id and set grade to 0
    try {
        $stmt = $pdo->prepare("INSERT INTO answers_esy_collab_tbl (assessment_id, room_id, student_id, question_id, answer, grades, submitted_at)
                               VALUES (?, ?, ?, ?, ?, 0, NOW())
                               ON DUPLICATE KEY UPDATE answer = ?, grades = 0, submitted_at = NOW()");
        $stmt->execute([$assessment_id, $roomId, $studentId, $question_id, $answer, $answer]);

        if ($current_question_index < count($questions) - 1) {
            $current_question_index++;
        } else {
            header("Location: results.php?assessment_id=$assessment_id");
            exit();
        }
    } catch (PDOException $e) {
        exit("Error saving answer: " . $e->getMessage());
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
    <h1><?php echo htmlspecialchars($assessment['name']); ?> (Room ID: <?php echo $roomId; ?>, Users: <span id="user-count"><?php echo $userCount; ?></span>)</h1>
    <h3>Write your answers and collaborate with your team!</h3>

    <!-- Timer -->
    <div id="timer-container">
        <h3>Time Remaining: <span id="timer">--:--</span></h3>
    </div>

    <form method="POST">
    <?php $question = $questions[$current_question_index]; ?>
    <div>
        <p><strong>Q<?php echo $current_question_index + 1; ?>:</strong> <?php echo htmlspecialchars($question['question_text']); ?></p>
        <textarea name="answer_<?php echo $question['question_id']; ?>" rows="4" cols="50"></textarea>
        <input type="hidden" name="current_question_index" value="<?php echo $current_question_index; ?>" />
    </div>
    <button type="submit" id="next-button">
        <?php echo ($current_question_index == count($questions) - 1) ? "Submit" : "Next"; ?>
    </button>
</form>

</div>

<!-- Chat Section -->
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
const roomId = "<?php echo $roomId; ?>";
const studentId = "<?php echo $studentId; ?>";
const chatMessages = document.getElementById('chat-messages');
const chatInput = document.getElementById('chat-message-input');
const sendButton = document.getElementById('send-message');
const endTime = <?php echo $endTime * 1000; ?>;

// Timer Function
function startTimer() {
    function updateTimer() {
        const now = Date.now();
        const remainingTime = Math.max(0, endTime - now);
        const minutes = Math.floor((remainingTime / 1000) / 60);
        const seconds = Math.floor((remainingTime / 1000) % 60);
        document.getElementById('timer').innerText = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
        if (remainingTime <= 0) {
            clearInterval(timerInterval);
            document.getElementById('timer').innerText = "Time's up!";
            document.querySelector("form").submit();
        }
    }
    updateTimer();
    const timerInterval = setInterval(updateTimer, 1000);
}
startTimer();

// Fetch chat messages dynamically
function fetchChatMessages() {
    fetch(`?ajax=fetchMessages&assessment_id=${assessmentId}&room_id=${roomId}`)
        .then(response => response.json())
        .then(data => {
            chatMessages.innerHTML = '';
            data.forEach(msg => {
                const messageDiv = document.createElement('div');
                messageDiv.innerHTML = `<strong>${msg.username}:</strong> ${msg.content}`;
                chatMessages.appendChild(messageDiv);
            });
        })
        .catch(error => console.error("Chat Fetch Error:", error));
}
setInterval(fetchChatMessages, 2000);

// Fetch and update user count dynamically
function fetchUserCount() {
    fetch(`?ajax=fetchUserCount&room_id=${roomId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('user-count').innerText = data.user_count;
        })
        .catch(error => console.error("User Count Fetch Error:", error));
}
setInterval(fetchUserCount, 5000);
fetchUserCount(); // Initial fetch

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

// Prevent form resubmission on refresh
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}
</script>

</body>
</html>