<?php
session_start();

// Check if user is authenticated
if (!isset($_SESSION['is_admin'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit();
}

include 'includes/db.php'; // Adjust the path as per your file structure

// Function to log blog actions
function logBlogAction($email, $isAdmin, $action, $blogId = null) {
    $logFile = 'logs/admin_blog_actions.log';
    $timezone = 'Asia/Manila';
    date_default_timezone_set($timezone);
    $date = date('Y-m-d H:i:s');
    $adminText = $isAdmin ? 'Admin' : 'User';
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $logMessage = "[$date] $adminText ($email) - IP: $ipAddress - User-Agent: $userAgent - Action: $action";
    if ($blogId) {
        $logMessage .= " - Blog ID: " . htmlspecialchars($blogId);
    }
    $logMessage .= "\n";
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Function to retrieve email based on user ID
function getUserEmail($conn, $userId) {
    $sql = "SELECT email FROM admins WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['email'];
    } else {
        return 'unknown'; // In case of an error
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $adminId = $_SESSION['user_id']; // Assuming you have a session for user_id
    $blog_id = mysqli_real_escape_string($conn, $_POST['blog_id']);

    // Retrieve the admin's email for logging
    $email = getUserEmail($conn, $adminId);
    $isAdmin = true; // Since we already checked that the user is an admin

    // Prepare and execute the delete query
    $sql = "DELETE FROM blogs WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $blog_id);

    if (mysqli_stmt_execute($stmt)) {
        // Log the delete action
        logBlogAction($email, $isAdmin, 'Delete', $blog_id);

        echo "Blog deleted successfully.";
    } else {
        echo "Error deleting blog: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>
