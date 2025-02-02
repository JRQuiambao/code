<?php
require 'config.php';

$roomId = $_POST['room_id'] ?? null;

if (!$roomId) {
    echo json_encode(['error' => 'Room ID is missing']);
    exit;
}

try {
    // Increment the current question index
    $stmt = $pdo->prepare("UPDATE room_status_tbl SET current_question_index = current_question_index + 1 WHERE room_id = ?");
    $stmt->execute([$roomId]);

    if ($stmt->rowCount()) {
        echo json_encode(['success' => true, 'message' => 'Question index updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update question index']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
