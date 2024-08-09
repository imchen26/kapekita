<?php
session_start();
include 'includes/db.php';

$error_message = ""; // Initialize error message variable

// Redirect if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit();
}

if (isset($_POST['register'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone_prefix = $_POST['phone_prefix'];
    $phone_number = $phone_prefix . $_POST['phone_number'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $profile_photo = $_FILES['profile_photo']['name'];
    $target_dir = "images/";
    $target_file = $target_dir . basename($profile_photo);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $valid_domains = array("gmail.com", "yahoo.com");
    $domain = "";

    // Check if the user already exists
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $error_message = "A user with this email already exists.";
    } else {
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Invalid email format.";
        }

        // Split email into local part and domain
        $email_parts = explode("@", $email);
        if (count($email_parts) == 2) {
            list($local_part, $domain) = $email_parts;

            // Check length of local part
            if (strlen($local_part) > 64) {
                $error_message = "The local part of the email address is too long (max of 64 characters).";
            }

            // Check length of domain part
            if (strlen($domain) > 255) {
                $error_message = "The domain part of the email address is too long (max of 255 characters).";
            }

            // Check if domain is allowed
            if (!isset($error_messages['email']) && !in_array($domain, $valid_domains)) {
                $error_messages['email'] = "Invalid email format. Only Gmail and Yahoo email addresses are allowed.";
            }
            } else {
                $error_message = "Invalid email format.";
             }
        }
    // Check if domain is allowed
    if (empty($error_message) && !in_array($domain, $valid_domains)) {
        $error_message = "Invalid email format. Only Gmail and Yahoo email addresses are allowed.";
    }

    // Validate phone number
    if (empty($error_message) && 
        (($phone_prefix == "+63" && !preg_match("/^\+63[0-9]{10}$/", $phone_number)) || 
        ($phone_prefix == "09" && !preg_match("/^09[0-9]{9}$/", $phone_number)))) {
        $error_message = "Invalid phone number format.";
    }

    // Validate profile photo type
    if (empty($error_message) && !in_array($imageFileType, array('jpg', 'jpeg', 'png'))) {
        $error_message = "Only JPG, JPEG & PNG files are allowed.";
    }

    // Insert data into the database if no errors
    if (empty($error_message)) {
        $sql = "INSERT INTO users (full_name, email, phone_number, profile_photo, password) VALUES ('$full_name', '$email', '$phone_number', '$profile_photo', '$password')";
        if ($conn->query($sql) === TRUE) {
            $error_message = "Registration successful!";
            // Move uploaded file to target directory
            move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target_file);
        } else {
            $error_message = "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

$conn->close();
?>

<?php include 'navbar.php'; ?>
<!DOCTYPE html>
<lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kape Kita Coffee Shop</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <!-- bootstrap links -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!-- bootstrap links -->
    <!-- fonts links -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <!-- fonts links -->
</head>
<body>
    <div class="register-container">
        <form action="register.php" method="post" enctype="multipart/form-data">
            <h1>Register</h1>
            <input type="text" id="full_name" name="full_name" placeholder="Full Name" required>
            <input type="text" id="email" name="email" placeholder="Email" required>
            <label for="phone_number">Phone Number</label><br>
            <input type="radio" id="prefix_63" name="phone_prefix" value="+63" checked>
            <label for="prefix_63">+63</label>
            <input type="radio" id="prefix_09" name="phone_prefix" value="09">
            <label for="prefix_09">09</label><br>
            <input type="text" id="phone_number" name="phone_number" placeholder="Enter your phone number" required>
            <label for="profile_picture">Profile Picture</label><br>
            <input type="file" id="profile_photo" name="profile_photo" required>
            <input type="password" id="password" name="password" placeholder="Password" required>
            <button type="submit" name="register">REGISTER</button>
            <div class="error-message">
                <?php echo $error_message; ?>
            </div>
        </form>
    </div>
</body>
</html>
