<?php
session_start();

// Redirect to login page if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'includes/db.php'; // Adjust to your database connection file

// Function to log blog actions
function logBlogAction($userId, $userEmail, $action, $blogId = null) {
    $logFile = 'logs/blog_actions.log';
    $timezone = 'Asia/Manila';
    date_default_timezone_set($timezone);
    $date = date('Y-m-d H:i:s');
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $logMessage = "[$date] User ID: $userId - Email: $userEmail - IP: $ipAddress - User-Agent: $userAgent - Action: $action";
    if ($blogId) {
        $logMessage .= " - Blog ID: " . htmlspecialchars($blogId);
    }
    $logMessage .= "\n";
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Function to retrieve user's email
function getUserEmail($conn, $userId) {
    $sql = "SELECT email FROM users WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $userEmail);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    return $userEmail;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $blog_id = mysqli_real_escape_string($conn, $_POST['blog_id']);
    $userId = $_SESSION['user_id']; // Assuming you have the user's ID stored in the session

    // Delete blog from the database
    $query = "DELETE FROM blogs WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $blog_id);

    if ($stmt->execute()) {
        // Retrieve the user's email for logging
        $userEmail = getUserEmail($conn, $userId);

        // Log the blog deletion action
        logBlogAction($userId, $userEmail, 'Blog Deleted', $blog_id);
        
        // Redirect to profile page or wherever appropriate after deletion
        header("Location: profile.php");
        exit();
    } else {
        // Handle error
        echo "Error deleting blog: " . $stmt->error;
    }
    
    $stmt->close();
}

mysqli_close($conn);
?>
