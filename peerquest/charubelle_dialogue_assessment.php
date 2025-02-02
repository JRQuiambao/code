<?php
session_start();

// Get assessment ID from URL
if (isset($_GET['assessment_id'])) {
    $assessment_id = htmlspecialchars($_GET['assessment_id']);
} else {
    echo "Invalid assessment!";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Charubelle's Dialogue</title>
    <link rel="stylesheet" href="css/c_dialogue.css">
</head>
<body>
    <div class="dialogue-container">
        <img src="images/c_blinking.gif" alt="Charubelle Speaking" class="charubelle-gif">
        <div class="dialogue-box">
            <p>Hello there! I'm Charaubelle, your study companion. Are you ready to take your assessment?</p>
            <p>Make sure to focus and give your best effort!</p>
        </div>
        <form action="take_assessment.php" method="GET">
            <input type="hidden" name="assessment_id" value="<?php echo $assessment_id; ?>">
            <button type="submit" class="btn-start">Start Assessment</button>
        </form>
    </div>
</body>
</html>
