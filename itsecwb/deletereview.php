<?php
session_start();

include 'includes/db.php';

$delete_review_error_message = "";

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

// Handle Delete Review
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_review'])) {
    $review_id = $_POST['review_id'];

    // Retrieve admin email
    $admin_id = $_SESSION['user_id'];
    $sql = "SELECT email FROM admins WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $admin_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $admin_email);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    // Delete review from the database
    $sql = "DELETE FROM reviews WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $review_id);

    if (mysqli_stmt_execute($stmt)) {
        // Log the delete action
        logReviewAction($admin_id, $admin_email, 'Delete', $review_id);

        // Redirect to refresh the page after deletion
        header("Location: managereview.php");
        exit();
    } else {
        $delete_review_error_message = "Error deleting record: " . $conn->error;
    }
    mysqli_stmt_close($stmt);
}
?>
