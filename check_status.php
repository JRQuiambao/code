<?php
require 'config.php';

session_start(); // Ensure session is started
$roomId = $_SESSION['room_id'] ?? null;
$assessment_id = $_SESSION['assessment_id'] ?? null;

if (!$roomId || !$assessment_id) {
    echo json_encode(['error' => 'Invalid room or assessment ID.']);
    exit();
}

// Fetch room status and assessment type
$stmt = $pdo->prepare("
    SELECT rr.status, at.type 
    FROM room_ready_tbl rr 
    JOIN assessment_tbl at ON rr.assessment_id = at.assessment_id 
    WHERE rr.room_id = ?
    LIMIT 1
");
$stmt->execute([$roomId]);
$roomData = $stmt->fetch(PDO::FETCH_ASSOC);

if ($roomData) {
    echo json_encode([
        'status' => $roomData['status'],
        'type' => $roomData['type'],
        'assessment_id' => $assessment_id // Send assessment ID for redirection
    ]);
} else {
    echo json_encode(['error' => 'Room not found.']);
}
?>
