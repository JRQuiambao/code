<?php
require 'config.php';

$roomId = $_GET['room_id'] ?? null;

if (!$roomId) {
    exit(json_encode(['error' => 'Room ID is required']));
}

try {
    // Fetch room status
    $stmt = $pdo->prepare("SELECT answered_count, total_users, current_question_index FROM room_status_tbl WHERE room_id = ?");
    $stmt->execute([$roomId]);
    $roomStatus = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($roomStatus) {
        echo json_encode([
            'answeredCount' => $roomStatus['answered_count'] ?? 0,
            'totalUsers' => $roomStatus['total_users'] ?? 0,
            'currentQuestionIndex' => $roomStatus['current_question_index'] ?? 0
        ]);
    } else {
        echo json_encode([
            'answeredCount' => 0,
            'totalUsers' => 0,
            'currentQuestionIndex' => 0,
            'error' => 'Room status not found'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
