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
    $blog_id = $_POST['blog_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $author_id = $_POST['author_id'];

    // Fetch author name from users table
    $sql_author = "SELECT full_name FROM users WHERE id=?";
    $stmt_author = mysqli_prepare($conn, $sql_author);
    mysqli_stmt_bind_param($stmt_author, 'i', $author_id);
    mysqli_stmt_execute($stmt_author);
    $result_author = mysqli_stmt_get_result($stmt_author);
    if ($row_author = mysqli_fetch_assoc($result_author)) {
        $author_name = $row_author['full_name'];
    } else {
        // Default author name if not found
        $author_name = "Unknown Author";
    }
    mysqli_stmt_close($stmt_author);

    $image_sql = "";

    // Check if a new image was uploaded
    if (!empty($_FILES["image"]["name"])) {
        // Handle image upload
        $target_dir = "blog/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $uploadOk = 1;
        $error_message = "";

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            $error_message = "File is not an image.";
            $uploadOk = 0;
        }

        // Check file size
        if ($_FILES["image"]["size"] > 500000) {
            $error_message = "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if (!in_array($imageFileType, array('jpg', 'jpeg', 'png'))) {
            $error_message = "Only JPG, JPEG & PNG files are allowed.";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            echo $error_message;
        } else {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_sql = ", image='$target_file'";
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }
    }

    // Update blog in database using prepared statements
    $sql = "UPDATE blogs SET title=?, content=?, author_id=?, author_name=? $image_sql WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ssisi', $title, $content, $author_id, $author_name, $blog_id);

    if (mysqli_stmt_execute($stmt)) {
        // Retrieve the admin's email for logging
        $email = getUserEmail($conn, $adminId);
        $isAdmin = true; // Since we already checked that the user is an admin

        // Log the update action
        logBlogAction($email, $isAdmin, 'Edit', $blog_id);

        echo "Blog updated successfully.";
    } else {
        echo "Error updating blog: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>
