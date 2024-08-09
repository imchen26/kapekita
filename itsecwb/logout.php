<?php
session_start();
include 'includes/db.php';

// Function to log logout actions
function logLogoutAction($email, $isAdmin) {
    $logFile = 'logs/logout.log';
    $timezone = 'Asia/Manila';
    date_default_timezone_set($timezone);
    $date = date('Y-m-d H:i:s');
    $adminText = $isAdmin ? 'Admin' : 'User';
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $logMessage = "[$date] $adminText ($email) - IP: $ipAddress - User-Agent: $userAgent: Logout\n";
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];

    // Retrieve email based on user type
    if ($isAdmin) {
        // Admin email
        $sql = "SELECT email FROM admins WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $email = $row['email'];
        } else {
            $email = 'unknown'; // In case of an error
        }
    } else {
        // Non-admin user email
        $sql = "SELECT email FROM users WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $email = $row['email'];
        } else {
            $email = 'unknown'; // In case of an error
        }
    }

    // Log the logout action
    logLogoutAction($email, $isAdmin);

    // Destroy session and redirect to login page
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
} else {
    // Redirect if user is not logged in
    header("Location: login.php");
    exit();
}

mysqli_close($conn);
?>
