<?php
session_start();

include 'includes/db.php';

$edit_review_error_message = "";

// Function to log review actions with email
function logReviewAction($adminId, $adminEmail, $action, $reviewId) {
    $logFile = 'logs/admin_review_actions.log';
    $timezone = 'Asia/Manila';
    date_default_timezone_set($timezone);
    $date = date('Y-m-d H:i:s');
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $logMessage = "[$date] Admin ID: $adminId - Email: $adminEmail - IP: $ipAddress - User-Agent: $userAgent - Action: $action - Review ID: $reviewId\n";
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Handle Edit Review
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_review'])) {
    $review_id = $_POST['review_id'];
    $title = isset($_POST['title']) ? $_POST['title'] : '';
    $content = isset($_POST['content']) ? $_POST['content'] : '';
    $rating = isset($_POST['rating']) ? $_POST['rating'] : '';
    $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : '';

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
    $admin_id = $_SESSION['user_id'];
    $sql_admin = "SELECT email FROM admins WHERE id=?";
    $stmt_admin = mysqli_prepare($conn, $sql_admin);
    mysqli_stmt_bind_param($stmt_admin, 'i', $admin_id);
    mysqli_stmt_execute($stmt_admin);
    mysqli_stmt_bind_result($stmt_admin, $admin_email);
    mysqli_stmt_fetch($stmt_admin);
    mysqli_stmt_close($stmt_admin);

    // Update review in the database
    $sql = "UPDATE reviews SET title=?, content=?, rating=?, user_id=?, author_name=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ssiisi', $title, $content, $rating, $user_id, $author_name, $review_id);

    if (mysqli_stmt_execute($stmt)) {
        // Log the edit action
        logReviewAction($admin_id, $admin_email, 'Edit', $review_id);

        // Redirect to refresh the page after editing
        header("Location: managereview.php");
        exit();
    } else {
        $edit_review_error_message = "Error updating record: " . $conn->error;
    }
    mysqli_stmt_close($stmt);
}
?>
