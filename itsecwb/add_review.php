<?php
session_start(); // Start the session
include 'includes/db.php';

// Function to log review actions
function logReviewAction($userId, $userEmail, $action, $title = null) {
    $logFile = 'logs/review_actions.log';
    $timezone = 'Asia/Manila';
    date_default_timezone_set($timezone);
    $date = date('Y-m-d H:i:s');
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $logMessage = "[$date] User ID: $userId - Email: $userEmail - IP: $ipAddress - User-Agent: $userAgent - Action: $action";
    if ($title) {
        $logMessage .= " - Title: " . htmlspecialchars($title);
    }
    $logMessage .= "\n";
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        // Redirect or handle the case where user is not logged in
        header("Location: login.php"); 
        exit();
    }

    $author_name = mysqli_real_escape_string($conn, $_POST['author_name']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $rating = (int)$_POST['rating'];
    $user_id = $_SESSION['user_id']; // Get user ID from session

    // Get user's email for logging
    $userQuery = "SELECT email FROM users WHERE id = '$user_id'";
    $userResult = mysqli_query($conn, $userQuery);
    $user = mysqli_fetch_assoc($userResult);
    $userEmail = $user['email'];

    $sql = "INSERT INTO reviews (author_name, user_id, title, content, rating, created_at) VALUES ('$author_name', $user_id, '$title', '$content', $rating, NOW())";

    if (mysqli_query($conn, $sql)) {
        // Log the review creation action
        logReviewAction($user_id, $userEmail, 'Review Created', $title);

        // Redirect to reviews.php after successful insertion
        header("Location: reviews.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }

    mysqli_close($conn);
}
?>
