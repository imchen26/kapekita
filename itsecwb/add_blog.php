<?php
include 'includes/db.php';
session_start();

// Function to log blog actions
function logBlogAction($userId, $userEmail, $action, $title = null) {
    $logFile = 'logs/blog_actions.log';
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $author_id = $_SESSION['user_id']; // Assuming you have the user's ID stored in the session
    $author_name = mysqli_real_escape_string($conn, $_POST['author_name']); // Getting the author name from the form input
    $image = '';

    // Handle file upload
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "blog/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image = $target_file;
        }
    }

    $sql = "INSERT INTO blogs (title, content, author_id, author_name, image) VALUES ('$title', '$content', '$author_id', '$author_name', '$image')";

    if (mysqli_query($conn, $sql)) {
        // Retrieve the user's email for logging
        $userEmail = getUserEmail($conn, $author_id);

        // Log the blog creation action
        logBlogAction($author_id, $userEmail, 'Blog Created', $title);

        // Redirect to the blog page
        header("Location: blog.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>
