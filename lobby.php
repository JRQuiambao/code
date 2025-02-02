<?php
require 'auth.php';
require 'config.php';

$roomId = $_SESSION['room_id'] ?? null;
$assessment_id = $_SESSION['assessment_id'] ?? null;
$student_id = $_SESSION['student_id'] ?? null;

if (!$roomId || !$assessment_id || !$student_id) {
    echo "Invalid room, assessment, or student ID.";
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ready'])) {
        $stmt = $pdo->prepare("UPDATE room_ready_tbl SET is_ready = 1 WHERE room_id = ? AND student_id = ?");
        $stmt->execute([$roomId, $student_id]);
        header("Refresh:0");
        exit();
    }

    if (isset($_POST['cancel'])) {
        $stmt = $pdo->prepare("UPDATE room_ready_tbl SET is_ready = 0 WHERE room_id = ? AND student_id = ?");
        $stmt->execute([$roomId, $student_id]);
        header("Refresh:0");
        exit();
    }

   if (isset($_POST['startAssessment'])) {
    // Fetch the assessment type
    $stmt = $pdo->prepare("SELECT type FROM assessment_tbl WHERE assessment_id = ?");
    $stmt->execute([$assessment_id]);
    $assessment = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($assessment) {
        $assessmentType = $assessment['type'];

        // Update room status to started
        $stmt = $pdo->prepare("UPDATE room_ready_tbl SET status = 'started' WHERE room_id = ?");
        $stmt->execute([$roomId]);

        // Redirect based on assessment type
        if ($assessmentType === 'Multiple Choice - Collaborative') {
            header("Location: take_multiple_choice_collaboration.php?assessment_id=" . urlencode($assessment_id));
        } elseif ($assessmentType === 'Essay - Collaborative') {
            header("Location: take_essay_collab.php?assessment_id=" . urlencode($assessment_id));
        } else {
            echo "Invalid assessment type.";
            exit();
        }
        exit();
    } else {
        echo "Assessment not found.";
        exit();
    }
}


    if (isset($_POST['leaveRoom'])) {
        // Check if the user leaving is the host
        $stmt = $pdo->prepare("SELECT is_host FROM room_ready_tbl WHERE room_id = ? AND student_id = ?");
        $stmt->execute([$roomId, $student_id]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        // Remove the leaving user from the room
        $stmt = $pdo->prepare("DELETE FROM room_ready_tbl WHERE room_id = ? AND student_id = ?");
        $stmt->execute([$roomId, $student_id]);

        // If the leaving user was the host, assign a new host
        if ($userData['is_host']) {
            $stmt = $pdo->prepare("SELECT student_id FROM room_ready_tbl WHERE room_id = ? ORDER BY student_id DESC LIMIT 1");
            $stmt->execute([$roomId]);
            $newHost = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($newHost) {
                $stmt = $pdo->prepare("UPDATE room_ready_tbl SET is_host = 1 WHERE student_id = ?");
                $stmt->execute([$newHost['student_id']]);
            }
        }

        // Redirect to student dashboard
        header("Location: student_dashboard.php");
        exit();
    }
}

// Fetch participants in the room with their usernames and is_ready status
$stmt = $pdo->prepare("SELECT rr.student_id, st.username, rr.is_ready, rr.is_host, rr.status 
                        FROM room_ready_tbl rr 
                        JOIN student_tbl st ON rr.student_id = st.student_id 
                        WHERE rr.room_id = ?");
$stmt->execute([$roomId]);
$participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

$allReady = true;
$isHost = false;
$isReady = false; // Track if the current user is ready

// Check if all participants are ready and determine if the user is the host
foreach ($participants as $participant) {
    if (!$participant['is_ready']) {
        $allReady = false;
    }
    if ($participant['student_id'] == $student_id) {
        if ($participant['is_host']) {
            $isHost = true;
        }
        $isReady = (int) $participant['is_ready']; // Ensure $isReady is properly interpreted as integer
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lobby</title>
    <style>
        .disabled {
            background-color: gray;
            cursor: not-allowed;
            opacity: 0.6;
        }
    </style>

    <script>
        (function() {
            history.pushState(null, null, document.URL);
            window.addEventListener('popstate', function () {
                history.pushState(null, null, document.URL);
            });
        })();
    </script>
</head>
<body>
<h1 onclick="copyToClipboard()" style="cursor: pointer;">
    Lobby for Room > <span id="roomId"><?php echo htmlspecialchars($roomId); ?></span> < (click the code to Copy)
</h1>

<ul>
    <?php foreach ($participants as $participant): ?>
        <li>
            <?php
            echo htmlspecialchars($participant['username']) . ' - ' .
                 ($participant['is_ready'] ? "✔ Ready" : "❌ Not Ready") . 
                 ($participant['is_host'] ? " (Host)" : "");
            ?>
        </li>
    <?php endforeach; ?>
</ul>

<!-- "I'm Ready" and "Cancel Ready" Button Logic -->
<form method="post">
    <?php if ($isReady === 0): ?>
        <button name="ready">I'm Ready</button>
    <?php else: ?>
        <button name="cancel">Cancel</button>
    <?php endif; ?>
</form>

<!-- Waiting for host message -->
<?php if ($allReady && !$isHost): ?>
    <p>Waiting for the host to start the assessment...</p>
<?php endif; ?>

<!-- Start Assessment Button for the Host -->
<?php if ($isHost): ?>
    <form method="post">
        <button type="submit" name="startAssessment" <?php echo !$allReady ? 'class="disabled" disabled' : ''; ?>>Start Assessment</button>
    </form>
<?php endif; ?>

<!-- Leave Room Option -->
<form method="post">
    <button type="submit" name="leaveRoom" style="background-color: red; color: white;">Leave Room</button>
</form>

<!-- AJAX Script for Participants -->
<script>
    function updateParticipants() {
        fetch('fetch_participants.php?t=' + new Date().getTime())  // Add timestamp to avoid caching
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error(data.error);
                    return;
                }

                let participantsList = document.querySelector('ul');
                participantsList.innerHTML = '';

                let allReady = true;

                data.forEach(participant => {
                    let listItem = document.createElement('li');
                    listItem.innerHTML = `${participant.username} - ` +
                        (participant.is_ready == 1 ? "✔ Ready" : "❌ Not Ready") +
                        (participant.is_host == 1 ? " (Host)" : "");
                    participantsList.appendChild(listItem);

                    if (participant.is_ready == 0) {
                        allReady = false;
                    }
                });

                // Enable or disable Start Assessment button
                let startButton = document.querySelector('[name="startAssessment"]');
                if (startButton) {
                    startButton.disabled = !allReady;
                    startButton.classList.toggle('disabled', !allReady);
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function checkRoomStatus() {
    fetch('check_status.php?t=' + new Date().getTime())  // Avoid caching issues
        .then(response => response.json())
        .then(data => {
            if (data.status === 'started') {
                // Determine the correct page to redirect
                let redirectUrl = '';
                if (data.type === 'Multiple Choice - Collaborative') {
                    redirectUrl = 'take_multiple_choice_collab.php?assessment_id=' + encodeURIComponent(data.assessment_id);
                } else if (data.type === 'Essay - Collaborative') {
                    redirectUrl = 'take_essay_collab.php?assessment_id=' + encodeURIComponent(data.assessment_id);
                }

                if (redirectUrl) {
                    window.location.href = redirectUrl; // Redirect all participants
                }
            }
        })
        .catch(error => console.error('Error:', error));
}

// Poll every 2 seconds for room status (ensuring real-time updates)
setInterval(checkRoomStatus, 2000);

// Initial check when the page loads
checkRoomStatus();


    function copyToClipboard() {
        let roomIdText = document.getElementById('roomId').innerText;
        navigator.clipboard.writeText(roomIdText).then(function() {
            alert('Room ID copied to clipboard: ' + roomIdText);
        }).catch(function(err) {
            alert('Failed to copy: ' + err);
        });
    }       
</script>
</body>
</html>
