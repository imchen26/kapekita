<?php
session_start();

// Redirect to login page if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'includes/db.php'; // Adjust to your database connection file

// Function to log review actions
function logReviewAction($userId, $userEmail, $action, $reviewId = null) {
    $logFile = 'logs/review_actions.log';
    $timezone = 'Asia/Manila';
    date_default_timezone_set($timezone);
    $date = date('Y-m-d H:i:s');
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $logMessage = "[$date] User ID: $userId - Email: $userEmail - IP: $ipAddress - User-Agent: $userAgent - Action: $action";
    if ($reviewId) {
        $logMessage .= " - Review ID: " . htmlspecialchars($reviewId);
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
    $review_id = mysqli_real_escape_string($conn, $_POST['review_id']);
    $userId = $_SESSION['user_id']; // Assuming you have the user's ID stored in the session

    // Delete review from the database
    $query = "DELETE FROM reviews WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $review_id);

    if ($stmt->execute()) {
        // Retrieve the user's email for logging
        $userEmail = getUserEmail($conn, $userId);

        // Log the review deletion action
        logReviewAction($userId, $userEmail, 'Review Deleted', $review_id);

        // Redirect to profile page after deletion
        header("Location: profile.php?success=Review deleted successfully");
        exit();
    } else {
        // Handle error
        echo "Error deleting review: " . $stmt->error;
    }
    
    $stmt->close();
}

mysqli_close($conn);
?>
