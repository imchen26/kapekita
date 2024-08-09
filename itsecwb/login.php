<?php
session_start();
include 'includes/db.php';

function logLoginAttempt($email, $isAdmin, $status, $errorMessage = '') {
    $logFile = 'logs/login.log';
    $timezone = 'Asia/Manila';
    date_default_timezone_set($timezone);
    $date = date('Y-m-d H:i:s');
    $statusText = $status ? 'Successful' : 'Failed';
    $adminText = $isAdmin ? 'Admin' : 'User';
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $errorText = $errorMessage ? " - Error: $errorMessage" : '';
    $logMessage = "[$date] $adminText ($email) - IP: $ipAddress - User-Agent: $userAgent: Login $statusText$errorText\n";
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}


// Redirect if user is already logged in
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
        header("Location: admin.php");
    } else {
        header("Location: user.php");
    }
    exit();
}

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Anti-brute-force protection
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
    }

    $max_attempts = 5; // Maximum allowed login attempts
    $ban_duration = 300; // Ban duration in seconds (5 minutes)

    // Check if the user is banned
    if (isset($_SESSION['ban_time']) && $_SESSION['ban_time'] > time()) {
        $remaining_time = $_SESSION['ban_time'] - time();
        $_SESSION['error_message'] = "You are temporarily banned. Please try again in $remaining_time seconds.";
        logLoginAttempt($email, isset($_SESSION['is_admin']) && $_SESSION['is_admin'], false, $_SESSION['error_message']);
    } else {
        // Check in the admins database
        $sql = "SELECT * FROM admins WHERE email=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['is_admin'] = true;
                $_SESSION['login_attempts'] = 0; // Reset login attempts on successful login
                logLoginAttempt($email, true, true); // Log successful admin login
                header("Location: admin.php");
                exit();
            } else {
                $_SESSION['error_message'] = "Invalid password.";
                $_SESSION['login_attempts']++;
                logLoginAttempt($email, true, false, $_SESSION['error_message']); // Log failed admin login
            }
        } else {
            // Check in the users database if not found in admins
            $sql = "SELECT * FROM users WHERE email=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();

                // Verify password
                if (password_verify($password, $row['password'])) {
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['is_admin'] = false;
                    $_SESSION['login_attempts'] = 0; // Reset login attempts on successful login
                    logLoginAttempt($email, false, true); // Log successful user login
                    header("Location: user.php");
                    exit();
                } else {
                    $_SESSION['error_message'] = "Invalid password.";
                    $_SESSION['login_attempts']++;
                    logLoginAttempt($email, false, false, $_SESSION['error_message']); // Log failed user login
                }
            } else {
                $_SESSION['error_message'] = "No user found with that email.";
                $_SESSION['login_attempts']++;
                logLoginAttempt($email, false, false, $_SESSION['error_message']); // Log failed login
            }
        }

        if ($_SESSION['login_attempts'] >= $max_attempts) {
            // User exceeded maximum login attempts, ban temporarily
            $_SESSION['ban_time'] = time() + $ban_duration;
            $_SESSION['error_message'] = "Too many login attempts. Please try again later.";
            logLoginAttempt($email, isset($_SESSION['is_admin']) && $_SESSION['is_admin'], false, $_SESSION['error_message']);
        }
    }
}

$conn->close();
?>

<?php include 'navbar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kape Kita Coffee Shop</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <form action="login.php" method="post">
            <h1>Log in</h1>
            <input type="email" id="email" name="email" placeholder="Email" required>
            <input type="password" id="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">LOG IN</button>
            <div class="error-message">
                <!-- Error messages will be displayed here -->
                <?php
                if (isset($_SESSION['error_message'])) {
                    echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']); // Clear the message after displaying
                }
                ?>
            </div>
        </form>
    </div>
</body>
</html>
