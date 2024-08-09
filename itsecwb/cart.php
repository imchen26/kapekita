<?php
session_start();
include 'includes/db.php'; // Include your database connection
include 'navbar.php';

// Set order_id to NULL for the current cart
$sql = "UPDATE carts SET order_id = NULL WHERE user_id = ? AND order_id IS NULL";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->close();

// Function to log cart actions
function logCartAction($userId, $userEmail, $action, $itemId = null, $quantity = null) {
    $logFile = 'logs/cart_actions.log';
    $timezone = 'Asia/Manila';
    date_default_timezone_set($timezone);
    $date = date('Y-m-d H:i:s');
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $logMessage = "[$date] User ID: $userId - Email: $userEmail - IP: $ipAddress - User-Agent: $userAgent - Action: $action";
    if ($itemId) {
        $logMessage .= " - Item ID: " . htmlspecialchars($itemId);
    }
    if ($quantity) {
        $logMessage .= " - Quantity: " . htmlspecialchars($quantity);
    }
    $logMessage .= "\n";
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Function to retrieve user's email
function getUserEmail($conn, $userId) {
    $sql = "SELECT email FROM users WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $userEmail);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    return $userEmail;
}

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in to proceed.'); window.location.href = 'login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle item addition to cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_item'])) {
    $item_id = intval($_POST['item_id']);
    $quantity = intval($_POST['quantity']);
    
    // Get cart ID
    $cart_id_query = "SELECT id FROM carts WHERE user_id = ? AND order_id IS NULL";
    $stmt = $conn->prepare($cart_id_query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $cart_result = $stmt->get_result();
    
    if ($cart_result->num_rows > 0) {
        $cart = $cart_result->fetch_assoc();
        $cart_id = $cart['id'];
        
        // Check if item already exists in the cart
        $item_exists_query = "SELECT id, quantity, price FROM cart_items WHERE cart_id = ? AND item_id = ?";
        $stmt = $conn->prepare($item_exists_query);
        $stmt->bind_param('ii', $cart_id, $item_id);
        $stmt->execute();
        $item_result = $stmt->get_result();
        
        if ($item_result->num_rows > 0) {
            // Update quantity and price if item already exists
            $item = $item_result->fetch_assoc();
            $new_quantity = $item['quantity'] + $quantity;
            $update_query = "UPDATE cart_items SET quantity = ?, price = ? WHERE cart_id = ? AND item_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param('idii', $new_quantity, $price, $cart_id, $item_id);
            $stmt->execute();
            
            // Log the update action
            $userEmail = getUserEmail($conn, $user_id);
            logCartAction($user_id, $userEmail, 'Item Updated in Cart', $item_id, $quantity);
        } else {
            // Add new item to cart
            $insert_query = "INSERT INTO cart_items (cart_id, item_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param('iiid', $cart_id, $item_id, $quantity, $price);
            $stmt->execute();
            
            // Log the addition action
            $userEmail = getUserEmail($conn, $user_id);
            logCartAction($user_id, $userEmail, 'Item Added to Cart', $item_id, $quantity);
        }
    } else {
        // Create a new cart if none exists
        $create_cart_query = "INSERT INTO carts (user_id) VALUES (?)";
        $stmt = $conn->prepare($create_cart_query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $cart_id = $stmt->insert_id;

        // Add the item to the newly created cart
        $insert_query = "INSERT INTO cart_items (cart_id, item_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param('iiid', $cart_id, $item_id, $quantity, $price);
        $stmt->execute();
        
        // Log the creation action
        $userEmail = getUserEmail($conn, $user_id);
        logCartAction($user_id, $userEmail, 'New Cart Created and Item Added', $item_id, $quantity);
    }

    echo "<script>window.location.href = 'cart.php';</script>";
    exit();
}

// Handle item removal from cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_item'])) {
    $item_id = intval($_POST['item_id']);
    
    // Get cart ID
    $cart_id_query = "SELECT id FROM carts WHERE user_id = ? AND order_id IS NULL";
    $stmt = $conn->prepare($cart_id_query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $cart_result = $stmt->get_result();
    
    if ($cart_result->num_rows > 0) {
        $cart = $cart_result->fetch_assoc();
        $cart_id = $cart['id'];
        
        // Remove item from cart
        $delete_item_query = "DELETE FROM cart_items WHERE cart_id = ? AND item_id = ?";
        $stmt = $conn->prepare($delete_item_query);
        $stmt->bind_param('ii', $cart_id, $item_id);
        $stmt->execute();
        
        // Log the removal action
        $userEmail = getUserEmail($conn, $user_id);
        logCartAction($user_id, $userEmail, 'Item Removed from Cart', $item_id);
    }
}

// Handle checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkout'])) {
    // Get cart ID
    $cart_id_query = "SELECT id FROM carts WHERE user_id = ? AND order_id IS NULL";
    $stmt = $conn->prepare($cart_id_query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $cart_result = $stmt->get_result();
    
    if ($cart_result->num_rows > 0) {
        $cart = $cart_result->fetch_assoc();
        $cart_id = $cart['id'];
        
        // Calculate total amount
        $total_amount = 0;
        $items_query = "SELECT ci.item_id, i.price, ci.quantity
                        FROM cart_items ci
                        JOIN items i ON ci.item_id = i.id
                        WHERE ci.cart_id = ?";
        $stmt = $conn->prepare($items_query);
        $stmt->bind_param('i', $cart_id);
        $stmt->execute();
        $items_result = $stmt->get_result();
        
        while ($item = $items_result->fetch_assoc()) {
            $total_amount += $item['price'] * $item['quantity'];
        }

        // Retrieve user's current wallet balance
        $wallet_query = "SELECT wallet FROM users WHERE id = ?";
        $stmt = $conn->prepare($wallet_query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->bind_result($wallet_balance);
        $stmt->fetch();
        $stmt->close();

        // Check if the user has sufficient funds
        if ($wallet_balance >= $total_amount) {
            // Deduct the total amount from the wallet
            $new_wallet_balance = $wallet_balance - $total_amount;
            $update_wallet_query = "UPDATE users SET wallet = ? WHERE id = ?";
            $stmt = $conn->prepare($update_wallet_query);
            $stmt->bind_param('di', $new_wallet_balance, $user_id);
            $stmt->execute();

            // Insert order
            $create_order_query = "INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'completed')";
            $stmt = $conn->prepare($create_order_query);
            $stmt->bind_param('id', $user_id, $total_amount);
            $stmt->execute();
            $order_id = $stmt->insert_id;

            // Insert order items
            $items_query = "SELECT item_id, quantity, price FROM cart_items WHERE cart_id = ?";
            $stmt = $conn->prepare($items_query);
            $stmt->bind_param('i', $cart_id);
            $stmt->execute();
            $items_result = $stmt->get_result();
            
            while ($item = $items_result->fetch_assoc()) {
                $insert_order_item_query = "INSERT INTO order_items (order_id, item_id, quantity, price) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_order_item_query);
                $stmt->bind_param('iiid', $order_id, $item['item_id'], $item['quantity'], $item['price']);
                $stmt->execute();
            }
            
            // Update cart with order_id
            $update_cart_query = "UPDATE carts SET order_id = ? WHERE id = ?";
            $stmt = $conn->prepare($update_cart_query);
            $stmt->bind_param('ii', $order_id, $cart_id);
            $stmt->execute();

            // Log the checkout action
            $userEmail = getUserEmail($conn, $user_id);
            logCartAction($user_id, $userEmail, 'Checkout Completed', null, null);
            
            echo "<script>alert('Checkout successful!'); window.location.href = 'cart.php';</script>";
        } else {
            echo "<script>alert('Insufficient funds in wallet.'); window.location.href = 'cart.php';</script>";
        }
    }
}

// Fetch cart items
$cart_items_query = "SELECT ci.item_id, i.name, i.price, ci.quantity
                     FROM carts c
                     JOIN cart_items ci ON c.id = ci.cart_id
                     JOIN items i ON ci.item_id = i.id
                     WHERE c.user_id = ? AND c.order_id IS NULL";
$stmt = $conn->prepare($cart_items_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$cart_items = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - Kape Kita Coffee Shop</title>
    <link rel="stylesheet" href="css/cart.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
</head>
<body>
    <section class="cart">
        <div class="container">
            <h2>Your Cart</h2>
            <?php if ($cart_items->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total = 0;
                        while ($item = $cart_items->fetch_assoc()):
                            $item_total = $item['price'] * $item['quantity'];
                            $total += $item_total;
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo number_format($item['price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                <td><?php echo number_format($item_total, 2); ?></td>
                                <td>
                                    <form method="post" action="cart.php" class="d-inline">
                                        <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item['item_id']); ?>">
                                        <button type="submit" name="remove_item" class="btn btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <tr>
                            <td colspan="3"><strong>Total</strong></td>
                            <td colspan="2"><?php echo number_format($total, 2); ?></td>
                        </tr>
                    </tbody>
                </table>
                <form method="post" action="cart.php" class="mt-4">
                    <button type="submit" name="checkout" class="btn btn-success">Proceed to Checkout</button>
                </form>
            <?php else: ?>
                <p>Your cart is empty.</p>
            <?php endif; ?>
        </div>
    </section>
</body>
</html>
