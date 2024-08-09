<?php
session_start();

include 'includes/db.php';

$add_review_error_message = "";

// Function to log review actions with email
function logReviewAction($adminId, $adminEmail, $action, $reviewId = null) {
    $logFile = 'logs/admin_review_actions.log';
    $timezone = 'Asia/Manila';
    date_default_timezone_set($timezone);
    $date = date('Y-m-d H:i:s');
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $logMessage = "[$date] Admin ID: $adminId - Email: $adminEmail - IP: $ipAddress - User-Agent: $userAgent - Action: $action";
    if ($reviewId) {
        $logMessage .= " - Review ID: $reviewId";
    }
    $logMessage .= "\n";
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Handle Add Review
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $adminId = $_SESSION['user_id']; // Ensure you have a session for user_id
    $title = $_POST['title'];
    $content = $_POST['content'];
    $rating = $_POST['rating'];
    $user_id = $_POST['user_id'];

    // Fetch user's full name from users table
    $sql_author = "SELECT full_name FROM users WHERE id=?";
    $stmt_author = mysqli_prepare($conn, $sql_author);
    mysqli_stmt_bind_param($stmt_author, 'i', $user_id);
    mysqli_stmt_execute($stmt_author);
    $result_author = mysqli_stmt_get_result($stmt_author);
    if ($row_author = mysqli_fetch_assoc($result_author)) {
        $author_name = $row_author['full_name'];
    } else {
        // Default author name if not found
        $author_name = "Unknown Author";
    }
    mysqli_stmt_close($stmt_author);

    // Retrieve admin email
    $sql_admin = "SELECT email FROM admins WHERE id=?";
    $stmt_admin = mysqli_prepare($conn, $sql_admin);
    mysqli_stmt_bind_param($stmt_admin, 'i', $adminId);
    mysqli_stmt_execute($stmt_admin);
    mysqli_stmt_bind_result($stmt_admin, $adminEmail);
    mysqli_stmt_fetch($stmt_admin);
    mysqli_stmt_close($stmt_admin);

    // Insert review into database
    $sql = "INSERT INTO reviews (user_id, title, content, rating, author_name) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'issis', $user_id, $title, $content, $rating, $author_name);

    if (mysqli_stmt_execute($stmt)) {
        // Log the add action
        $reviewId = mysqli_insert_id($conn); // Get the ID of the newly inserted review
        logReviewAction($adminId, $adminEmail, 'Add', $reviewId);

        // Redirect to refresh the page after adding
        header("Location: managereview.php");
        exit();
    } else {
        $add_review_error_message = "Error: " . $conn->error;
    }
    mysqli_stmt_close($stmt);
}
?>
