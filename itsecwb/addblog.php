<?php
session_start();

// Check if user is authenticated
if (!isset($_SESSION['is_admin'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit();
}

include 'includes/db.php'; // Adjust the path as per your file structure

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $author_id = mysqli_real_escape_string($conn, $_POST['author_id']);
    
    // Fetch author name from users table (optional)
    $sql_author = "SELECT full_name FROM users WHERE id='$author_id'";
    $result_author = mysqli_query($conn, $sql_author);
    if ($row_author = mysqli_fetch_assoc($result_author)) {
        $author_name = $row_author['full_name'];
    } else {
        // Default author name if not found
        $author_name = "Unknown Author";
    }
    
    // Handle image upload
    $target_dir = "blog/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $uploadOk = 1;
    $error_message = "";

    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if($check !== false) {
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
    if(!in_array($imageFileType, array('jpg', 'jpeg', 'png'))) {
        $error_message = "Only JPG, JPEG & PNG files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo $error_message;
    } else {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Insert blog into database
            $sql = "INSERT INTO blogs (title, content, author_id, author_name, created_at, image) 
                    VALUES ('$title', '$content', '$author_id', '$author_name', NOW(), '$target_file')";
            if (mysqli_query($conn, $sql)) {
                echo "Blog added successfully.";
                header("Location: manageblog.php"); 
                exit();
            } else {
                echo "Error: " . $sql . "<br>" . mysqli_error($conn);
            }
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }

    mysqli_close($conn);
}
?>
