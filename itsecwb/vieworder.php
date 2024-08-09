<?php
session_start();

// Redirect to login page if not authenticated
if (!isset($_SESSION['is_admin'])) {
    header("Location: login.php");
    exit();
}

include 'includes/db.php'; // Adjust to your database connection file

// Fetch order details from the database based on order ID
if (isset($_GET['id'])) {
    $order_id = intval($_GET['id']); // Sanitize input
    $user_id = $_SESSION['user_id'];

    // Fetch order details
    $sql_order = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql_order);
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result_order = $stmt->get_result();
    $order = $result_order->fetch_assoc();

    if (!$order) {
        echo "Order not found.";
        exit();
    }

    // Fetch order items
    $sql_items = "SELECT oi.*, i.name FROM order_items oi JOIN items i ON oi.item_id = i.id WHERE oi.order_id = ?";
    $stmt = $conn->prepare($sql_items);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result_items = $stmt->get_result();
} else {
    echo "Order ID not provided.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Order - Kape Kita Coffee Shop</title>
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
<div class="container mt-5" style="margin-bottom: 50px;">
    <h2>View Order</h2>
    <div class="card">
        <div class="card-header">
            Order Details
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <tr>
                    <th>Order ID</th>
                    <td><?php echo htmlspecialchars($order['id']); ?></td>
                </tr>
                <tr>
                    <th>User ID</th>
                    <td><?php echo htmlspecialchars($order['user_id']); ?></td>
                </tr>
                <tr>
                    <th>Total Amount</th>
                    <td><?php echo htmlspecialchars($order['total_amount']); ?></td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td><?php echo htmlspecialchars($order['status']); ?></td>
                </tr>
                <tr>
                    <th>Created At</th>
                    <td><?php echo date('M j, Y - g:i A', strtotime($order['created_at'])); ?></td>
                </tr>
            </table>

            <div class="mt-3">
                <h4>Order Items</h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $result_items->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                <td><?php echo htmlspecialchars($item['price']); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer text-end">
            <a href="manageorder.php" class="btn btn-primary">Back to Orders</a>
        </div>
    </div>
</div>
</body>
</html>
