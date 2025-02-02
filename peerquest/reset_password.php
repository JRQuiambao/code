<?php
session_start();
require 'config.php';

$message = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST['token'];
    $hashedToken = hash("sha256", $token); // Hash token to compare with DB
    $newPassword = trim($_POST['password']);
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    try {
        // Validate the token
        $stmt = $pdo->prepare("SELECT * FROM password_reset_tbl WHERE token = ? AND expires_at > NOW()");
        $stmt->execute([$hashedToken]);
        $resetRequest = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($resetRequest) {
            $email = $resetRequest['email'];
            $user_type = $resetRequest['user_type'];
            $table = $user_type === 'student' ? 'student_tbl' : 'teacher_tbl';
            $emailColumn = $user_type === 'student' ? 'student_email' : 'teacher_email';
            $passwordColumn = $user_type === 'student' ? 'student_password' : 'teacher_password';

            // Update password
            $updateStmt = $pdo->prepare("UPDATE $table SET $passwordColumn = ? WHERE $emailColumn = ?");
            $updateStmt->execute([$hashedPassword, $email]);

            // Delete token
            $deleteStmt = $pdo->prepare("DELETE FROM password_reset_tbl WHERE token = ?");
            $deleteStmt->execute([$hashedToken]);

            $message = "Password successfully updated. You can now log in.";
        } else {
            $message = "Invalid or expired token.";
        }
    } catch (PDOException $e) {
        $message = "ERROR: Could not connect. " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Water.css -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
    
    <!-- Bootstrap CSS for modal -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="forgot_password.css" rel="stylesheet">

    <style>
        /* Full-page centering */
        html, body {
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            background-color: #f8f9fa; /* Light gray background */
        }

        /* Styling for the form container */
        .reset-password-box {
            width: 100%;
            max-width: 400px;
            padding: 20px;
            border-radius: 10px;
            background: white;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        /* Ensure buttons are full width */
        .btn {
            width: 100%;
        }
    </style>
</head>
<body>

    <div class="reset-password-box">
        <h1>Reset Password</h1>

        <form method="post">
            <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">
            <label for="password">New Password</label>
            <input type="password" class="form-control mb-3" name="password" id="password" required>
            <button type="submit" class="btn btn-primary">Reset Password</button>
        </form>

        <a href="login.php" class="btn btn-secondary mt-2">Back to Login</a>
... (33 lines left)