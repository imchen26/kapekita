<?php
session_start();
include 'includes/db.php';
include 'adminnavbar.php';

$error_messages = []; // Initialize error messages array

// Logging function
function logAction($message, $type = 'INFO') {
    $logFile = 'logs/debug_register.log';
    $timezone = 'Asia/Manila';
    date_default_timezone_set($timezone);
    $logTime = date('Y-m-d H:i:s');
    $logMessage = "[$type] | $logTime | $message" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Stack trace function
function callTracer() {
    $backtrace = debug_backtrace();
    $formattedTrace = [];
    foreach ($backtrace as $index => $call) {
        $function = $call['function'] ?? 'unknown function';
        $file = $call['file'] ?? 'unknown file';
        $line = $call['line'] ?? 'unknown line';
        $argsString = '';
        if (isset($call['args'])) {
            $argsArray = array_map(function ($arg) {
                if (is_array($arg)) {
                    return 'Array';
                } else if (is_object($arg)) {
                    return 'Object('.get_class($arg).')';
                } else if (is_null($arg)) {
                    return 'NULL';
                } else if (is_bool($arg)) {
                    return $arg ? 'true' : 'false';
                } else if (is_string($arg)) {
                    return "'$arg'";
                } else {
                    return $arg;
                }
            }, $call['args']);
            $argsString = implode(', ', $argsArray);
        }
        $formattedTrace[] = "[$index] => #$index $function(<b>$argsString</b>) called at [$file:$line]";
    }
    return implode("<br>", $formattedTrace);
}

// Handle registration form submission
if (isset($_POST['register'])) {
    // Check and set debug mode
    if (isset($_POST['debugger'])) {
        $_SESSION['debug_mode'] = true;
    } else {
        // Reset debug mode if the checkbox is not checked
        $_SESSION['debug_mode'] = false;
    }
    $debug = isset($_SESSION['debug_mode']) ? $_SESSION['debug_mode'] : false;
    
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone_prefix = $_POST['phone_prefix'];
    $phone_number = $phone_prefix . $_POST['phone_number'];
    $password = $_POST['password'];
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
        $error_messages[] = "A user with this email already exists.";
        logAction(end($error_messages), 'ERROR');
        if ($debug) {
            $error_messages[] = "Stack trace: <br>" . callTracer();
            logAction(end($error_messages), 'ERROR');
        }
    } else {
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_messages[] = "Invalid email format.";
            logAction(end($error_messages), 'ERROR');
            if ($debug) {
                $error_messages[] = "Stack trace: <br>" . callTracer();
                logAction(end($error_messages), 'ERROR');
            }
        }

        // Split email into local part and domain
        $email_parts = explode("@", $email);
        if (count($email_parts) == 2) {
            list($local_part, $domain) = $email_parts;

            // Check length of local part
            if (strlen($local_part) > 64) {
                $error_messages[] = "The local part of the email address is too long (max of 64 characters).";
                logAction(end($error_messages), 'ERROR');
                if ($debug) {
                    $error_messages[] = "Stack trace: <br>" . callTracer();
                    logAction(end($error_messages), 'ERROR');
                }
            }

            // Check length of domain part
            if (strlen($domain) > 255) {
                $error_messages[] = "The domain part of the email address is too long (max of 255 characters).";
                logAction(end($error_messages), 'ERROR');
                if ($debug) {
                    $error_messages[] = "Stack trace: <br>" . callTracer();
                    logAction(end($error_messages), 'ERROR');
                }
            }

            // Check if domain is allowed
            if (!in_array($domain, $valid_domains)) {
                $error_messages[] = "Invalid email format. Only Gmail and Yahoo email addresses are allowed.";
                logAction(end($error_messages), 'ERROR');
                if ($debug) {
                    $error_messages[] = "Stack trace: <br>" . callTracer();
                    logAction(end($error_messages), 'ERROR');
                }
            }
        } else {
            $error_messages[] = "Invalid email format.";
            logAction(end($error_messages), 'ERROR');
            if ($debug) {
                $error_messages[] = "Stack trace: <br>" . callTracer();
                logAction(end($error_messages), 'ERROR');
            }
        }
    }

    // Validate phone number
    if (
        (($phone_prefix == "+63" && !preg_match("/^\+63[0-9]{10}$/", $phone_number)) || 
        ($phone_prefix == "09" && !preg_match("/^09[0-9]{9}$/", $phone_number)))
    ) {
        $error_messages[] = "Invalid phone number format.";
        logAction(end($error_messages), 'ERROR');
        if ($debug) {
            $error_messages[] = "Stack trace: <br>" . callTracer();
            logAction(end($error_messages), 'ERROR');
        }
    }

    // Validate profile photo type
    if (!in_array($imageFileType, array('jpg', 'jpeg', 'png'))) {
        $error_messages[] = "Only JPG, JPEG & PNG files are allowed.";
        logAction(end($error_messages), 'ERROR');
        if ($debug) {
            $error_messages[] = "Stack trace: <br>" . callTracer();
            logAction(end($error_messages), 'ERROR');
        }
    }

    // Simulate registration (do not actually insert into database)
    if (empty($error_messages)) {
        $error_messages[] = $debug ? "Registration simulated in debug mode." : "Registration would be successful.";
        logAction($debug ? "Registration simulated for $email" : "Registration would be successful for $email", $debug ? 'INFO' : 'SUCCESS');

        // Move uploaded file to target directory only if debug mode is on
        if ($debug) {
            move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target_file);
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Kape Kita Coffee Shop</title>
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
    <div class="register-container">
        <form action="debug_register.php" method="post" enctype="multipart/form-data">
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
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="debugger" name="debugger" <?php echo isset($_SESSION['debug_mode']) && $_SESSION['debug_mode'] ? 'checked' : ''; ?>>
                <label class="form-check-label" for="debugger">Enable Debug Mode</label>
            </div>
            <div class="error-message">
                <?php
                // Display all error messages
                if (!empty($error_messages)) {
                    foreach ($error_messages as $message) {
                        echo $message . "<br>";
                    }
                }
                ?>
            </div>
        </form>
    </div>
</body>
</html>
