<?php
session_start();

// Redirect to login page if not authenticated or not an admin
if (!isset($_SESSION['is_admin'])) {
    header("Location: login.php");
    exit();
}

include 'includes/db.php'; 
include 'adminnavbar.php';

// Initialize variables for error and success messages
$error_message = '';
$success_message = '';

// Function to log password change attempts
function logPasswordChangeAttempt($adminId, $adminEmail, $status, $errorMessage = '') {
    $logFile = 'logs/password_change.log';
    $timezone = 'Asia/Manila';
    date_default_timezone_set($timezone);
    $date = date('Y-m-d H:i:s');
    $statusText = $status ? 'Successful' : 'Failed';
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $errorText = $errorMessage ? " - Error: $errorMessage" : '';
    $logMessage = "[$date] Admin ID: $adminId - Email: $adminEmail - IP: $ipAddress - User-Agent: $userAgent: Password Change $statusText$errorText\n";
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle password change request
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Retrieve admin ID from session
        $admin_id = $_SESSION['user_id'];

        // Retrieve admin email and stored password
        $sql = "SELECT email, password FROM admins WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $admin_id);
        $stmt->execute();
        $stmt->bind_result($admin_email, $stored_password);
        $stmt->fetch();
        $stmt->close();

        // Verify current password
        if (password_verify($current_password, $stored_password)) {
            // Validate new password length
            if (strlen($new_password) >= 8) {
                // Validate new password match
                if ($new_password === $confirm_password) {
                    // Hash the new password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                    // Update password in the database
                    $update_sql = "UPDATE admins SET password = ? WHERE id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param('si', $hashed_password, $admin_id);
                    if ($update_stmt->execute()) {
                        $success_message = "Password updated successfully.";
                        logPasswordChangeAttempt($admin_id, $admin_email, true); // Log successful password change
                    } else {
                        $error_message = "Error updating password. Please try again later.";
                        logPasswordChangeAttempt($admin_id, $admin_email, false, "Database update error."); // Log failed password change
                    }
                    $update_stmt->close();
                } else {
                    $error_message = "New password and confirm password do not match.";
                    logPasswordChangeAttempt($admin_id, $admin_email, false, "Password mismatch."); // Log failed password change
                }
            } else {
                $error_message = "New password must be at least 8 characters long.";
                logPasswordChangeAttempt($admin_id, $admin_email, false, "Password too short."); // Log failed password change
            }
        } else {
            $error_message = "Current password is incorrect.";
            logPasswordChangeAttempt($admin_id, $admin_email, false, "Incorrect current password."); // Log failed password change
        }
    }
}

$conn->close();
?>


<!-- Display HTML content with appropriate messages -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings - Kape Kita Coffee Shop</title>
    <link rel="stylesheet" href="css/adminstyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <!-- Additional CSS -->
    <style>
        body {
            background-image: url('image/coffeebg.jpg');
        }
    </style>
</head>
<body>
    <div class="adminsettings">
        <div class="container">
            <h2>Admin Settings</h2>
            <form action="adminsettings.php" method="POST">
                <div class="mb-3">
                    <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Current Password" required>
                </div>
                <div class="mb-3">
                    <input type="password" class="form-control" id="new_password" name="new_password" placeholder="New Password (minimum 8 characters)" required>
                </div>
                <div class="mb-3">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm New Password" required>
                </div>
                <?php if (!empty($error_message)) : ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                <?php if (!empty($success_message)) : ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary" name="change_password">Change Password</button>
            </form>
        </div>
    </div>
</body>
</html>
