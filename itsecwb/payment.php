<?php
session_start();

// Include database connection
include 'includes/db.php'; // Adjust to your database connection file

// Function to log payment actions
function logPaymentAction($userId, $userEmail, $action, $orderId = null, $amount = null) {
    $logFile = 'logs/payment_actions.log';
    $timezone = 'Asia/Manila';
    date_default_timezone_set($timezone);
    $date = date('Y-m-d H:i:s');
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $logMessage = "[$date] User ID: $userId - Email: $userEmail - IP: $ipAddress - User-Agent: $userAgent - Action: $action";
    if ($orderId) {
        $logMessage .= " - Order ID: " . htmlspecialchars($orderId);
    }
    if ($amount) {
        $logMessage .= " - Amount: " . htmlspecialchars($amount);
    }
    $logMessage .= "\n";
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Function to retrieve user's email
function getUserEmail($conn, $userId) {
    $userEmail = ''; 
    $sql = "SELECT email FROM users WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->bind_result($userEmail);
    $stmt->fetch();
    $stmt->close();
    return $userEmail;
}

// Validate if payment request is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['order_id'])) {
    $order_id = intval($_POST['order_id']);
    
    // Fetch order details
    $query = "SELECT * FROM orders WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
        
        // Ensure order status is 'pending' to proceed with payment
        if ($order['status'] === 'pending') {
            $user_id = $_SESSION['user_id'];
            $total_amount = $order['total_amount'];
            
            // Check if user has sufficient balance in wallet
            $query = "SELECT wallet FROM users WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $current_wallet_balance = $user['wallet'];
                
                if ($current_wallet_balance >= $total_amount) {
                    // Deduct amount from wallet
                    $new_wallet_balance = $current_wallet_balance - $total_amount;
                    
                    $query = "UPDATE users SET wallet = ? WHERE id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("di", $new_wallet_balance, $user_id);
                    $stmt->execute();
                    
                    if ($stmt->affected_rows > 0) {
                        // Update order status to 'completed'
                        $query = "UPDATE orders SET status = 'completed' WHERE id = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("i", $order_id);
                        $stmt->execute();
                        
                        if ($stmt->affected_rows > 0) {
                            // Payment successful
                            $userEmail = getUserEmail($conn, $user_id);
                            logPaymentAction($user_id, $userEmail, 'Payment Successful', $order_id, $total_amount);
                            echo json_encode(array('success' => true));
                            exit();
                        } else {
                            // Failed to update order status
                            logPaymentAction($user_id, getUserEmail($conn, $user_id), 'Failed to Update Order Status', $order_id, $total_amount);
                            echo json_encode(array('success' => false, 'error' => 'Failed to update order status.'));
                            exit();
                        }
                    } else {
                        // Failed to deduct from wallet
                        logPaymentAction($user_id, getUserEmail($conn, $user_id), 'Failed to Deduct from Wallet', $order_id, $total_amount);
                        echo json_encode(array('success' => false, 'error' => 'Failed to deduct from wallet.'));
                        exit();
                    }
                } else {
                    // Insufficient wallet balance
                    logPaymentAction($user_id, getUserEmail($conn, $user_id), 'Insufficient Wallet Balance', $order_id, $total_amount);
                    echo json_encode(array('success' => false, 'error' => 'Insufficient wallet balance.'));
                    exit();
                }
            } else {
                // User not found
                logPaymentAction($user_id, getUserEmail($conn, $user_id), 'User Not Found', $order_id, $total_amount);
                echo json_encode(array('success' => false, 'error' => 'User not found.'));
                exit();
            }
        } else {
            // Order status is not 'pending'
            logPaymentAction($user_id, getUserEmail($conn, $user_id), 'Order Status Not Pending', $order_id);
            echo json_encode(array('success' => false, 'error' => 'Order status is not pending.'));
            exit();
        }
    } else {
        // Order not found
        logPaymentAction($user_id, getUserEmail($conn, $user_id), 'Order Not Found', $order_id);
        echo json_encode(array('success' => false, 'error' => 'Order not found.'));
        exit();
    }
} else {
    // Invalid request
    http_response_code(400);
    echo json_encode(array('success' => false, 'error' => 'Invalid request.'));
    exit();
}
?>
