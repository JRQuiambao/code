<?php
session_start();
$message = $_SESSION['message'] ?? null;
unset($_SESSION['message']); // Clear message after displaying
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Bootstrap CSS for modal -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="forgot_password.css" rel="stylesheet">

    <style>
        /* Ensure full-page centering */
        html, body {
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            background-color: #f8f9fa; /* Light gray background */
        }

        /* Styling for the form container */
        .forgot-password-box {
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

    <div class="forgot-password-box">
        <h1>Forgot Password</h1>

        <form method="post" action="send_password_reset.php">
            <label for="email">Email</label>
            <input type="email" class="form-control mb-3" name="email" id="email" required>
            
            <button type="submit" class="btn btn-primary">Send Reset Link</button>
            <a href="login.php" class="btn btn-secondary mt-2">Back to Login</a>
        </form>
    </div>

    <!-- Bootstrap Modal -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="messageModalLabel">Notification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?= htmlspecialchars($message ?? '') ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS for modal -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show modal if there is a message
        <?php if ($message): ?>
            var messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
            messageModal.show();
        <?php endif; ?>
    </script>

</body>
</html>