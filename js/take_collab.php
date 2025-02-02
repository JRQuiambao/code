<?php


// Retrieve assessment_id and room_id
$assessment_id = $_GET['assessment_id'] ?? null;
$roomId = $_SESSION['room_id'] ?? null;

if (!$assessment_id || !$roomId) {
    exit("Invalid session or assessment ID. Please return to the lobby.");
}

// Fetch class_id from room_ready_tbl
try {
    $stmt = $pdo->prepare("SELECT class_id FROM room_ready_tbl WHERE room_id = ? AND assessment_id = ?");
    $stmt->execute([$roomId, $assessment_id]);
    $roomData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$roomData || empty($roomData['class_id'])) {
        exit("Error: Class ID not found for the specified room and assessment.");
    }

    // Store class_id in the session
    $_SESSION['class_id'] = $roomData['class_id'];
} catch (PDOException $e) {
    exit("Error fetching class ID: " . $e->getMessage());
}

// Fetch assessment details
try {
    $stmt = $pdo->prepare("SELECT * FROM assessment_tbl WHERE assessment_id = ?");
    $stmt->execute([$assessment_id]);
    $assessment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$assessment) {
        exit("Error: Assessment not found.");
    }
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

<script>
const socket = io('http://localhost:3000');
const roomId = "<?php echo $roomId; ?>";
const username = "<?php echo htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?>";
const totalQuestions = <?php echo count($questions); ?>;
const healthPerQuestion = 100 / totalQuestions;
let bossHealth = 100;
let currentQuestionIndex = 0;

const questions = document.querySelectorAll('.question');
const nextButton = document.getElementById('next-button');
const submitButton = document.getElementById('submit-button');
const answerCountDisplay = document.getElementById('answer-count');
const bossHealthProgress = document.getElementById('boss-health-progress');
const currentQuestionDisplay = document.getElementById('current-question');

// Show the first question
questions[currentQuestionIndex].classList.add('active');
updateBossHealth();

// Join the room
socket.emit('joinRoom', { roomId, username });

// Real-time chat functionality
const chatMessages = document.getElementById('chat-messages');
const chatInput = document.getElementById('chat-message-input');
const sendButton = document.getElementById('send-message');

sendButton.addEventListener('click', () => {
    const message = chatInput.value.trim();
    if (message) {
        socket.emit('chatMessage', { roomId, username, message });
        chatInput.value = '';
    }
});

socket.on('message', (data) => {
    const messageDiv = document.createElement('div');
    messageDiv.textContent = `${data.username}: ${data.message}`;
    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
});

// Update UI based on server data
socket.on('roomUpdate', (data) => {
    const answeredCount = data.answeredCount;
    const totalUsers = data.totalUsers;

    // Update answer count display
    updateAnswerCount(answeredCount, totalUsers);

    // Navigate to the correct question if index has changed
    if (data.currentQuestionIndex !== currentQuestionIndex) {
        navigateToQuestion(data.currentQuestionIndex);
    }
});

// Handle Submit Button Click
submitButton.addEventListener('click', () => {
    const maxScore = <?php echo array_sum(array_column($questions, 'points')); ?>;
    const totalScore = Math.floor(Math.random() * maxScore); // Simulated score

    console.log('Emitting submitAssessment with:', { roomId, totalScore, maxScore });
    socket.emit('submitAssessment', { roomId, totalScore, maxScore });
});

// Listen for assessmentSubmitted
socket.on('assessmentSubmitted', ({ totalScore, maxScore }) => {
    console.log('Received assessmentSubmitted:', { totalScore, maxScore });

    // Retrieve assessment_id and class_id from PHP session and pass them to the URL
    const assessmentId = "<?php echo htmlspecialchars($assessment_id); ?>";
    const classId = "<?php echo htmlspecialchars($_SESSION['class_id'] ?? ''); ?>";

    // Redirect to accomplished_mcq.php with relevant data
    window.location.href = `accomplished_mcq.php?assessment_id=${assessmentId}&class_id=${classId}&totalScore=${totalScore}&maxScore=${maxScore}`;
});

function updateAnswerCount(answeredCount, totalUsers) {
    const answerCountDisplay = document.getElementById('answer-count');
    answerCountDisplay.textContent = `${answeredCount}/${totalUsers} users answered`;

    if (currentQuestionIndex === totalQuestions - 1) {
        submitButton.style.display = 'inline-block';
        submitButton.disabled = answeredCount < totalUsers;
    } else {
        nextButton.disabled = answeredCount < totalUsers;
    }
}

function navigateToQuestion(index) {
    const currentQuestionDisplay = document.getElementById('current-question');

    questions[currentQuestionIndex].classList.remove('active');
    currentQuestionIndex = index;
    questions[currentQuestionIndex].classList.add('active');

    currentQuestionDisplay.textContent = currentQuestionIndex + 1;

    // Update boss health here
    updateBossHealth();

    if (currentQuestionIndex === totalQuestions - 1) {
        nextButton.style.display = 'none';
        submitButton.style.display = 'inline-block';
    } else {
        nextButton.style.display = 'inline-block';
        submitButton.style.display = 'none';
    }

    nextButton.disabled = true;
}

function updateBossHealth() {
    bossHealth = 100 - (currentQuestionIndex * healthPerQuestion);
    bossHealthProgress.style.width = `${bossHealth}%`;
}

nextButton.addEventListener('click', () => {
    socket.emit('nextQuestion', { roomId });
});

// Submit an answer
function submitAnswer(questionId, selectedOption) {
    fetch('submit_answer.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `room_id=${roomId}&assessment_id=<?php echo $assessment_id; ?>&question_id=${questionId}&selected_option=${selectedOption}`
    }).then(() => {
        socket.emit('submitAnswer', { roomId });
    });
}

// Fetch answers dynamically
function fetchAnswers() {
    fetch('fetch_answers.php?room_id=<?php echo $roomId; ?>')
        .then(response => response.json())
        .then(data => {
            document.querySelectorAll('.question label').forEach(label => {
                label.classList.remove('highlighted');
                const checkmark = label.querySelector('.checkmark');
                if (checkmark) checkmark.remove();
            });

            for (let questionId in data) {
                const answers = data[questionId];
                for (let option in answers) {
                    const count = answers[option];
                    document.querySelectorAll(`[data-question="${questionId}"][value="${option}"]`).forEach(input => {
                        const label = input.parentElement;

                        if (count > 0) {
                            label.classList.add('highlighted');
                            let checkmark = label.querySelector('.checkmark');
                            if (!checkmark) {
                                checkmark = document.createElement('span');
                                checkmark.classList.add('checkmark');
                                label.appendChild(checkmark);
                            }
                            checkmark.textContent = ` ✔ (${count} users)`;
                        }
                    });
                }
            }
        })
        .catch(err => console.error('Error:', err));
}

setInterval(fetchAnswers, 2000);

function preventBack() {
    window.history.forward();
}
setTimeout("preventBack()", 0);
window.onunload = function () { null };

// Automatically trigger submission when the timer runs out
submitButton.addEventListener('click', () => {
    fetch('submit_assessment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `room_id=${roomId}&assessment_id=<?php echo $assessment_id; ?>`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = `accomplished_mcq.php?assessment_id=<?php echo $assessment_id; ?>&class_id=<?php echo $_SESSION['class_id']; ?>`;
        } else {
            alert('Failed to submit the assessment. Please try again.');
        }
    })
    .catch(err => console.error('Error submitting assessment:', err));
});

</script>
