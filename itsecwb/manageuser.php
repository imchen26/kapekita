<?php
session_start();
ob_start(); // Start output buffering

// Redirect to login page if not authenticated
if (!isset($_SESSION['is_admin'])) {
    header("Location: login.php");
    exit();
}

include 'adminnavbar.php';
include 'includes/db.php';

// Logging function
function logAction($message) {
    $logFile = 'logs/manageuser.log';
    $timezone = 'Asia/Manila';
    date_default_timezone_set($timezone);
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Retrieve email of the admin
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT email FROM admins WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $adminEmail = $result->fetch_assoc()['email'];
}

// Error messages
$add_user_error_message = "";
$edit_user_error_message = "";
$delete_user_error_message = "";

// Handle Add User
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user'])) {
    // Validate email format and domain
    $email = $_POST['email'];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $add_user_error_message = "Invalid email format.";
        logAction("Add User Error: Invalid email format for email '$email'.");
    } else {
        $email_parts = explode('@', $email);
        if (strtolower($email_parts[1]) !== 'gmail.com') {
            $add_user_error_message = "Email must be from gmail.com domain.";
            logAction("Add User Error: Email domain not gmail.com for email '$email'.");
        }
    }

    // Validate phone number format based on prefix
    $phone_prefix = $_POST['phone_prefix'];
    $phone_number = $_POST['phone_number'];
    if (($phone_prefix == "+63" && !preg_match("/^\+63[0-9]{10}$/", $phone_number)) || 
        ($phone_prefix == "09" && !preg_match("/^09[0-9]{9}$/", $phone_number))) {
        $add_user_error_message = "Invalid phone number format.";
        logAction("Add User Error: Invalid phone number format for number '$phone_number'.");
    }

    // Validate profile photo type
    $profile_photo = $_FILES['profile_photo']['name'];
    $imageFileType = strtolower(pathinfo($profile_photo, PATHINFO_EXTENSION));
    if (!in_array($imageFileType, array('jpg', 'jpeg', 'png'))) {
        $add_user_error_message = "Only JPG, JPEG & PNG files are allowed.";
        logAction("Add User Error: Invalid profile photo type '$imageFileType' for file '$profile_photo'.");
    }

    // If no validation errors, proceed to add user
    if (empty($add_user_error_message)) {
        $full_name = $_POST['full_name'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hashing the password

        // Move uploaded photo to images directory
        $target_dir = "images/";
        $target_file = $target_dir . basename($_FILES["profile_photo"]["name"]);
        move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_file);

        // Insert user into database
        $sql = "INSERT INTO users (full_name, email, password, phone_prefix, phone_number, profile_photo) 
                VALUES ('$full_name', '$email', '$password', '$phone_prefix', '$phone_number', '$profile_photo')";
        
        if ($conn->query($sql) === TRUE) {
            logAction("User added successfully by Admin ($adminEmail): $full_name, $email.");
            // Redirect to refresh the page after adding
            header("Location: manageuser.php");
            exit();
        } else {
            $add_user_error_message = "Error: " . $sql . "<br>" . $conn->error;
            logAction("Add User Error: Database error: " . $conn->error);
        }
    }
}

// Handle Edit User
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_user'])) {
    // Validate email format and domain
    $email = $_POST['email'];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $edit_user_error_message = "Invalid email format.";
        logAction("Edit User Error: Invalid email format for email '$email'.");
    } else {
        $email_parts = explode('@', $email);
        if (strtolower($email_parts[1]) !== 'gmail.com') {
            $edit_user_error_message = "Email must be from gmail.com domain.";
            logAction("Edit User Error: Email domain not gmail.com for email '$email'.");
        }
    }

    // Validate profile photo type if provided
    if (!empty($_FILES['edit_profile_photo']['name'])) {
        $profile_photo = $_FILES['edit_profile_photo']['name'];
        $imageFileType = strtolower(pathinfo($profile_photo, PATHINFO_EXTENSION));
        if (!in_array($imageFileType, array('jpg', 'jpeg', 'png'))) {
            $edit_user_error_message = "Only JPG, JPEG & PNG files are allowed.";
            logAction("Edit User Error: Invalid profile photo type '$imageFileType' for file '$profile_photo'.");
        }
    }

    // Proceed if no validation errors
    if (empty($edit_user_error_message)) {
        // Handle form submission for editing user
        $user_id = $_POST['user_id'];
        $full_name = $_POST['full_name'];
        $email = $_POST['email'];

        if (!empty($_FILES['edit_profile_photo']['name'])) {
            // Move uploaded photo to images directory
            $target_dir = "images/";
            $target_file = $target_dir . basename($_FILES["edit_profile_photo"]["name"]);
            move_uploaded_file($_FILES["edit_profile_photo"]["tmp_name"], $target_file);

            // Update user in database with profile photo
            $sql = "UPDATE users SET full_name='$full_name', email='$email', profile_photo='$profile_photo' WHERE id=$user_id";
        } else {
            // Update user in database without profile photo
            $sql = "UPDATE users SET full_name='$full_name', email='$email' WHERE id=$user_id";
        }

        if ($conn->query($sql) === TRUE) {
            logAction("User edited successfully by Admin ($adminEmail): ID $user_id, $full_name, $email.");
            // Redirect to refresh the page after editing
            header("Location: manageuser.php");
            exit();
        } else {
            $edit_user_error_message = "Error: " . $sql . "<br>" . $conn->error;
            logAction("Edit User Error: Database error: " . $conn->error);
        }
    }
}

// Handle Delete User
if (isset($_POST['delete_user'])) {
    // Handle deletion of user
    $user_id = $_POST['user_id'];

    // Delete user from database
    $sql = "DELETE FROM users WHERE id=$user_id";
    if ($conn->query($sql) === TRUE) {
        logAction("User deleted successfully by Admin ($adminEmail): ID $user_id.");
        // Redirect to refresh the page after deletion
        header("Location: manageuser.php");
        exit();
    } else {
        $delete_user_error_message = "Error deleting record: " . $conn->error;
        logAction("Delete User Error: Database error: " . $conn->error);
    }
}

// Fetch users from db
$sql = "SELECT * FROM users";
$result = $conn->query($sql);

$conn->close();
ob_end_flush(); // Flush the buffer and turn off output buffering
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage User - Kape Kita Coffee Shop</title>
    <link rel="stylesheet" href="css/adminstyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <!-- bootstrap links -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!-- bootstrap links -->
    <!-- fonts links -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <!-- fonts links -->
    <style>
        body {
            background-image: url('image/coffeebg.jpg');
        }
    </style>
</head>
<body>
<div class="content-container">
    <div class="manageuser">
        <div class="admin-container">
            <h1 class="admin-page-heading">Manage Users</h1>
            <div class="admin-edit-delete-buttons">
                <div class="btn-add-container">
                    <button class="btn btn-add" id="btn-show-add-form" style="font-size: 1.2em; font-weight: bold;">Add User</button>
                </div>
            </div>
            <div class="admin-profile-grid">
                <?php
                include 'includes/db.php';

                // Fetch users from db
                $sql = "SELECT * FROM users";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<div class='admin-profile-card'>";
                        echo "<img src='images/" . $row["profile_photo"] . "' alt='" . $row["full_name"] . "' class='admin-profile-photo'>";
                        echo "<div class='admin-profile-info'>";
                        echo "<p><strong>ID:</strong> " . $row["id"]. "</p>";
                        echo "<p><strong>Name:</strong> " . $row["full_name"]. "</p>";
                        echo "<p><strong>Email:</strong> " . $row["email"]. "</p>";
                        echo "</div>";
                        echo "<div class='admin-edit-delete-buttons'>";
                        echo "<button class='btn btn-edit' onclick='showEditForm(" . $row['id'] . ")'>Edit</button>";
                        echo "<button class='btn btn-delete' onclick='showDeleteForm(" . $row['id'] . ")'>Delete</button>";
                        echo "</div>"; 
                        echo "</div>"; 
                    }
                } else {
                    echo "<p class='admin-no-results'>No profiles found.</p>";
                }

                $conn->close();
                ?>
            </div>
        </div>
</div>
        <!-- Add User Form -->
        <div class="popup-form-overlay" id="add-user-overlay">
            <div class="popup-form-container">
                <span class="popup-form-close" onclick="closeForm()">&times;</span>
                <h2>Add User</h2>
                <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
                    <label for="full_name">Full Name:</label>
                    <input type="text" id="full_name" name="full_name" required>
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                    <label for="phone_prefix" style="margin-bottom: -13px;">Phone Number Prefix:</label><br>
                    <input type="radio" id="prefix_63" name="phone_prefix" value="+63">
                    <label for="prefix_63" style="margin-top: -5px;">+63</label>
                    <input type="radio" id="prefix_09" name="phone_prefix" value="09">
                    <label for="prefix_09" style="margin-top: -5px;">09</label><br>
                    <label for="phone_number" style="margin-top: -16px;">Phone Number:</label>
                    <input type="text" id="phone_number" name="phone_number" placeholder="Enter your phone number" required>
                    <label for="profile_photo">Profile Photo:</label>
                    <input type="file" id="profile_photo" name="profile_photo" accept="image/*" required>
                    <?php if (!empty($add_user_error_message)) : ?>
                        <div class="alert alert-danger" style="margin-top: 5px;"><?php echo $add_user_error_message; ?></div>
                    <?php endif; ?>
                    <button type="submit" name="add_user" class="btn btn-success">Add User</button>
                </form>
            </div>
        </div>

        <!-- Edit User Form -->
        <div class="popup-form-overlay" id="edit-user-overlay">
            <div class="popup-form-container">
                <span class="popup-form-close" onclick="closeForm()">&times;</span>
                <h2>Edit User</h2>
                <form id="edit-user-form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
                    <input type="hidden" id="edit_user_id" name="user_id">
                    <label for="edit_full_name">Full Name:</label>
                    <input type="text" id="edit_full_name" name="full_name" required>
                    <label for="edit_email">Email:</label>
                    <input type="email" id="edit_email" name="email" required>
                    <label for="edit_profile_photo" style="margin-top: 5px;">Profile Photo (optional):</label>
                    <input type="file" id="edit_profile_photo" name="edit_profile_photo" accept="image/*">
                    <?php if (!empty($edit_user_error_message)) : ?>
                        <div class="alert alert-danger" style="margin-top: 5px;"><?php echo $edit_user_error_message; ?></div>
                    <?php endif; ?>           
                    <button type="submit" name="edit_user" class="btn btn-success">Save Changes</button>
                </form>
            </div>
        </div>

        <!-- Delete User Form -->
        <div class="popup-form-overlay" id="delete-user-overlay">
            <div class="popup-form-container">
                <span class="popup-form-close" onclick="closeForm()">&times;</span>
                <h2>Delete User</h2>
                <form id="delete-user-form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <input type="hidden" id="delete_user_id" name="user_id">
                    <p>Are you sure you want to delete this user?</p>
                    <?php if (!empty($delete_user_error_message)) : ?>
                        <div class="alert alert-danger" style="margin-top: 5px;"><?php echo $delete_user_error_message; ?></div>
                    <?php endif; ?>
                    <button type="submit" name="delete_user" class="btn btn-delete">Delete User</button>
                </form>
            </div>
        </div>

    </div> <!-- End of manageuser -->

    <script>
        // Function to show Add User form
        document.getElementById('btn-show-add-form').addEventListener('click', function() {
            document.getElementById('add-user-overlay').style.display = 'flex';
        });

        // Function to show Edit User form
        function showEditForm(userId) {
            document.getElementById('edit-user-overlay').style.display = 'flex';
            document.getElementById('edit_user_id').value = userId;
        }

        // Function to show Delete User form
        function showDeleteForm(userId) {
            document.getElementById('delete-user-overlay').style.display = 'flex';
            document.getElementById('delete_user_id').value = userId;
        }

        // Function to close any form overlay
        function closeForm() {
            document.querySelectorAll('.popup-form-overlay').forEach(function(el) {
                el.style.display = 'none';
            });
        }
    </script>
</body>
</html>
