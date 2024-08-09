<?php 
session_start();
ob_start();
include 'includes/db.php';
include 'navbar.php'; 

// Function to log wallet actions
function logWalletAction($userId, $email, $action, $amount = null) {
    $logFile = 'logs/wallet_actions.log';
    $timezone = 'Asia/Manila';
    date_default_timezone_set($timezone);
    $date = date('Y-m-d H:i:s');
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $logMessage = "[$date] User ID: $userId - Email: $email - IP: $ipAddress - User-Agent: $userAgent - Action: $action";
    if ($amount) {
        $logMessage .= " - Amount: ₱" . number_format($amount, 2);
    }
    $logMessage .= "\n";
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Fetch the user's wallet balance from the database
if(isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id']; // Use user_id instead of email
    $walletQuery = "SELECT wallet, email FROM users WHERE id = '$userId'";
    $walletResult = mysqli_query($conn, $walletQuery);
    $user = mysqli_fetch_assoc($walletResult);
    $userWallet = $user['wallet'];
    $userEmail = $user['email']; // Fetch the user's email
} else {
    // Redirect if user is not logged in
    header("Location: login.php");
    exit();
}

// Handle wallet top-up
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['topup_button'])) {
    $topupAmount = 0; // Initialize the top-up amount

    // Check if custom amount is provided and use it
    if (!empty($_POST['custom_topup_amount'])) {
        $topupAmount = floatval($_POST['custom_topup_amount']);
    } elseif (!empty($_POST['topup_amount'])) {
        $topupAmount = floatval($_POST['topup_amount']);
    }

    // Perform top-up only if the amount is greater than zero
    if ($topupAmount > 0) {
        // Update wallet balance in the database
        $updateQuery = "UPDATE users SET wallet = wallet + $topupAmount WHERE id = '$userId'";
        mysqli_query($conn, $updateQuery);
        
        // Fetch the updated wallet balance from the database
        $walletQuery = "SELECT wallet FROM users WHERE id = '$userId'";
        $walletResult = mysqli_query($conn, $walletQuery);
        $userWallet = mysqli_fetch_assoc($walletResult)['wallet'];

        // Set success message for successful top-up
        $success_message = "Top-up successful! ₱" . number_format($topupAmount, 2) . " added to your wallet.";

        // Log the top-up action
        logWalletAction($userId, $userEmail, 'Top-up', $topupAmount);
        
        // Redirect to the same page to prevent form resubmission
        header("Location: wallet.php");
        exit();
    } else {
        // Set error message if top-up amount is not valid
        $error_message = "Please enter a valid top-up amount.";
    }
}

$conn->close();
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wallet - Kape-Kada Coffee Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.1/css/all.css">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Montserrat:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('css/bc3.png');
            background-size: cover;
            background-position: center;
            font-family: 'Nunito', sans-serif;
        }
        .wallet-container {
            background-color: rgba(255, 255, 255, 0.9); /* Semi-transparent white background */
            padding: 30px;
            border-radius: 10px;
            max-width: 600px;
            margin: 100px auto; /* Centering the container */
            box-shadow: 0 0 20px rgba(0,0,0,0.1); /* Box shadow for container */
        }
        .wallet-info {
            text-align: center;
            margin-bottom: 30px;
        }
        .wallet-balance {
            font-size: 24px;
            color: #D17A22;
        }
        .topup-form {
            max-width: 400px;
            margin: 0 auto;
        }
        .btn-primary {
            font-weight: bold;
            margin-top: 10px;
            padding: 10px;
            background: #b2744c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.5s ease;
            display: block;
            width: 100%; 
        }
        .btn-primary:hover {
            background: #8c5932;
        }
        .alert {
            margin-top: 20px;
            padding: 10px;
            border-radius: 5px;
        }
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
    </style>
</head>
<body>

<div class="container wallet-container">
    <div class="wallet-info">
        <h1>Your Wallet Balance</h1>
        <p class="wallet-balance">₱<?php echo number_format($userWallet, 2); ?></p>
    </div>
    <div class="topup-form">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="topup_amount">Select or Enter Top-up Amount (PHP)</label>
                <div class="input-group">
                    <select class="form-control" id="topup_amount" name="topup_amount">
                        <option value="20">₱20</option>
                        <option value="50">₱50</option>
                        <option value="100">₱100</option>
                        <option value="200">₱200</option>
                        <option value="500">₱500</option>
                        <option value="1000">₱1000</option>
                    </select>
                    <div class="input-group-append">
                        <span class="input-group-text">Or</span>
                    </div>
                    <input type="number" step="0.01" min="0" class="form-control" id="custom_topup_amount" name="custom_topup_amount" placeholder="Enter amount">
                </div>
                <small class="form-text text-muted">Enter your own amount or choose from the list</small>
            </div>
            <?php if (isset($error_message)) : ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <?php if (isset($success_message)) : ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary" name="topup_button">Top-up</button>
        </form>
    </div>
</div>

</body>
</html>
