<?php
session_start();

// Check if user is authenticated as admin
if (!isset($_SESSION['is_admin'])) {
    header("Location: login.php");
    exit();
}

include 'includes/db.php'; // Adjust this to your database connection

// Function to clean data for Excel export
function cleanData($str) {
    $str = preg_replace("/\t/", "\\t", $str);
    $str = preg_replace("/\r?\n/", "\\n", $str);
    if (strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
    return $str;
}

// Function to log order actions with email
function logOrderAction($adminId, $adminEmail, $action, $orderId = null) {
    $logFile = 'logs/order_actions.log';
    $timezone = 'Asia/Manila';
    date_default_timezone_set($timezone);
    $date = date('Y-m-d H:i:s');
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $logMessage = "[$date] Admin ID: $adminId - Email: $adminEmail - IP: $ipAddress - User-Agent: $userAgent - Action: $action";
    if ($orderId) {
        $logMessage .= " - Order ID: $orderId";
    }
    $logMessage .= "\n";
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Function to retrieve admin email
function getAdminEmail($conn, $adminId) {
    $sql = "SELECT email FROM admins WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $adminId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $adminEmail);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    return $adminEmail;
}

// Check if single order export is requested
if (isset($_GET['id'])) {
    $adminId = $_SESSION['user_id']; // Assuming you have a session for user_id
    $adminEmail = getAdminEmail($conn, $adminId); // Retrieve admin email
    $order_id = $_GET['id']; // Ensure to sanitize and validate this input
    $sql = "SELECT * FROM orders WHERE id = $order_id";
    $result = mysqli_query($conn, $sql);

    // Prepare CSV content
    $filename = "order_" . $order_id . ".xls";
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Content-Type: application/vnd.ms-excel");

    $flag = false;
    while ($row = mysqli_fetch_assoc($result)) {
        if (!$flag) {
            // Display column names as first row
            echo implode("\t", array_keys($row)) . "\n";
            $flag = true;
        }
        array_walk($row, 'cleanData');
        echo implode("\t", array_values($row)) . "\n";
    }

    // Log the export action
    logOrderAction($adminId, $adminEmail, 'Export Single', $order_id);

    exit();
}

// Check if mass export is requested
if (isset($_POST['export_excel'])) {
    $adminId = $_SESSION['user_id']; // Assuming you have a session for user_id
    $adminEmail = getAdminEmail($conn, $adminId); // Retrieve admin email
    // Fetch all orders from the database
    $sql = "SELECT * FROM orders";
    $result = mysqli_query($conn, $sql);

    // Prepare CSV content
    $filename = "orders_export_" . date('Y-m-d') . ".xls"; // Example filename with date
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Content-Type: application/vnd.ms-excel");

    $flag = false;
    while ($row = mysqli_fetch_assoc($result)) {
        if (!$flag) {
            // Display column names as first row
            echo implode("\t", array_keys($row)) . "\n";
            $flag = true;
        }
        array_walk($row, 'cleanData');
        echo implode("\t", array_values($row)) . "\n";
    }

    // Log the mass export action
    logOrderAction($adminId, $adminEmail, 'Export All');

    exit();
}

include 'adminnavbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Kape Kita Coffee Shop</title>
    <link rel="stylesheet" href="css/adminstyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('image/coffeebg.jpg');
        }
    </style>
</head>
<body>
<div class="content-container">
    <div class="container mt-5">
        <h2>Manage Orders</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>User ID</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch orders from the database
                $sql = "SELECT * FROM orders";
                $result = mysqli_query($conn, $sql);

                while ($row = mysqli_fetch_assoc($result)) {
                ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['user_id']; ?></td>
                        <td><?php echo $row['total_amount']; ?></td>
                        <td><?php echo $row['status']; ?></td>
                        <td><?php echo date('M j, Y - g:i A', strtotime($row['created_at'])); ?></td>
                        <td>
                            <a href="vieworder.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">View</a>
                            <a href="manageorder.php?id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm">Export to Excel</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- Mass View and Export Buttons -->
        <div class="mb-3">
            <form method="post">
                <button type="submit" name="export_excel" class="btn btn-success" style="margin-top: 3px;">Mass Export to Excel</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
