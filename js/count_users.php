<?php 
// Function to count users in the room
function countUsersInRoom($pdo, $roomId) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as user_count FROM room_ready_tbl WHERE room_id = ?");
        $stmt->execute([$roomId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['user_count'];
    } catch (PDOException $e) {
        return 0; // Return 0 if there's an error
    }
}

?>