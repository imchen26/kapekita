<?php
session_start();
require 'includes/db.php'; 

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = htmlspecialchars($_POST['full_name']);
    $email = htmlspecialchars($_POST['email']);
    $user_id = $_SESSION['user_id']; 

    // Check if a new profile photo was uploaded
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['profile_photo']['tmp_name'];
        $file_name = $_FILES['profile_photo']['name'];
        $file_name_cmps = explode(".", $file_name);
        $file_extension = strtolower(end($file_name_cmps));
        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg');

        if (in_array($file_extension, $allowedfileExtensions)) {
            // Directory where the uploaded file will be moved
            $uploadFileDir = 'images/';
            // Using the original file name
            $dest_path = $uploadFileDir . $file_name;

            if (move_uploaded_file($file_tmp_path, $dest_path)) {
                // Save only the file name in the database
                $profile_photo = $file_name;
            } else {
                $_SESSION['error'] = 'There was an error moving the uploaded file.';
                header('Location: profile.php');
                exit;
            }
        } else {
            $_SESSION['error'] = 'Upload failed. Allowed file types: ' . implode(',', $allowedfileExtensions);
            header('Location: profile.php');
            exit;
        }
    }

    // Update the database
    $sql = "UPDATE users SET full_name = ?, email = ?";
    if (isset($profile_photo)) {
        $sql .= ", profile_photo = ?";
    }
    $sql .= " WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        if (isset($profile_photo)) {
            $stmt->bind_param("sssi", $full_name, $email, $profile_photo, $user_id);
        } else {
            $stmt->bind_param("ssi", $full_name, $email, $user_id);
        }

        if ($stmt->execute()) {
            $_SESSION['success'] = 'Profile updated successfully!';
        } else {
            $_SESSION['error'] = 'Error: ' . $stmt->error;
        }

        $stmt->close();
    } else {
        $_SESSION['error'] = 'Error: ' . $conn->error;
    }

    $conn->close();

    header('Location: profile.php');
    exit;
}
?>
