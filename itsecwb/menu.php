<?php
session_start();
ob_start();

include 'navbar.php';
include 'includes/db.php';

// Set session timeout in seconds
$sessionTimeout = 10; // Timeout after 10 seconds

// Check if session variable for last activity exists
if (isset($_SESSION['last_activity'])) {
    $elapsedTime = time() - $_SESSION['last_activity'];
    if ($elapsedTime > $sessionTimeout) {
        // Session has expired, redirect to logout.php
        header('Location: logout.php');
        exit;
    }
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Fetch user email from the session or database
function getUserEmail($userId, $conn) {
    if (isset($_SESSION['user_email'])) {
        return $_SESSION['user_email'];
    }
    
    $sql = "SELECT email FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        return $user['email'];
    }

    return '';
}

// Function to log cart actions
function logCartAction($userId, $userEmail, $action, $itemId = null, $quantity = null) {
    $logFile = 'logs/cart_actions.log';
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

// Handle add to cart requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    $userId = $_SESSION['user_id'];
    $userEmail = getUserEmail($userId, $conn);
    $itemId = intval($_POST['item_id']);
    $quantity = intval($_POST['quantity']);
    $price = floatval($_POST['item_price']);

    if ($quantity <= 0) {
        logCartAction($userId, $userEmail, 'Failed to add item to cart - Invalid quantity', $itemId, $quantity);
        echo "<script>alert('Invalid quantity.'); window.history.back();</script>";
        exit;
    }

    // Check if there's an existing cart for the user
    $sql = "SELECT id FROM carts WHERE user_id = ? AND order_id IS NULL ORDER BY created_at DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $cart = $result->fetch_assoc();
        $cartId = $cart['id'];
    } else {
        // Create a new cart if none exists
        $sql = "INSERT INTO carts (user_id) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $userId);
        if (!$stmt->execute()) {
            logCartAction($userId, $userEmail, 'Failed to create cart');
            echo "<script>alert('Failed to create cart.'); window.history.back();</script>";
            $stmt->close();
            $conn->close();
            exit;
        }
        $cartId = $stmt->insert_id;
    }

    // Check if item is already in the cart
    $sql = "SELECT id FROM cart_items WHERE cart_id = ? AND item_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $cartId, $itemId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update existing item quantity and price
        $sql = "UPDATE cart_items SET quantity = quantity + ?, price = ? WHERE cart_id = ? AND item_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('idii', $quantity, $price, $cartId, $itemId);
        $action = 'Updated item quantity in cart';
    } else {
        // Add new item to cart
        $sql = "INSERT INTO cart_items (cart_id, item_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iiid', $cartId, $itemId, $quantity, $price);
        $action = 'Added new item to cart';
    }

    if ($stmt->execute()) {
        logCartAction($userId, $userEmail, $action, $itemId, $quantity);
        echo "<script>alert('Item added to cart successfully!'); window.location.href = 'menu.php';</script>";
    } else {
        logCartAction($userId, $userEmail, 'Failed to add item to cart', $itemId, $quantity);
        echo "<script>alert('Failed to add item to cart.'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
    exit;
}

$sql = "SELECT * FROM items";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $items = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $items = []; 
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Kape Kita Coffee Shop</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <style>
        .menu .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: all 0.3s ease;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .menu .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.2);
        }
        .menu .card img {
            width: 100%;
            height: auto;
            border-radius: 10px 10px 0 0;
        }
        .menu .card-body {
            padding: 20px;
        }
        .menu .star {
            color: #f3d612;
            font-size: 18px;
            margin-bottom: 10px;
        }
        .menu .card-body h3 {
            font-size: 24px;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 15px;
        }
        .menu .card-body p {
            font-size: 18px;
            margin-bottom: 0;
        }
        .menu .add-to-cart {
            font-size: 24px;
            float: right;
            color: #6c757d;
            cursor: pointer;
        }
        .menu .add-to-cart:hover {
            color: #212529;
        }
        /* Popup Form Styling */
        .popup-form-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .popup-form-container {
            background: #fff;
            background-color: #b2744c; /* Ensure contrast with text color */
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            width: 90%;
            max-width: 500px; /* Adjusted for better responsiveness */
            color: white;
            position: relative; /* Added to position the close button */
            overflow-y: auto;
            max-height: 80%;
        }

        .popup-form-close {
            position: absolute;
            top: 10px;
            right: 15px; /* Moved the close button slightly to the left */
            font-size: 24px;
            color: white; /* Subtle color for the close button */
            cursor: pointer;
            padding: 5px; /* Added padding for better spacing */
        }

        .popup-form-container h2 {
            margin-top: 0;
            color: white; /* Updated heading color */
            font-size: 24px;
        }

        .popup-form-container .alert {
            display: none;
            margin-bottom: 15px; /* Added space below alerts */
        }

        .alert-success {
            color: green;
        }

        .alert-danger {
            color: red;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: white; /* Updated label color */
        }
        .form-group label[for="item_name"] {
            margin-top: 20px; /* Adjust the value as needed */
        }
        .form-group input {
            width: 100%;
            padding: 10px; /* Increased padding for better usability */
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px; /* Increased font size for readability */
        }

        button[type="submit"] {
            width: 100%;
            padding: 10px;
            background: #8c5932;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease; /* Shortened transition time */
            color: white; /* Ensure button text is readable */
            font-size: 16px;
        }

        button[type="submit"]:hover {
            background: #6e472b;
        }
    </style>
</head>
<body>

<section class="menu" id="menu">
    <div class="container">
        <div class="heading3">Menu</div>
    </div>
    <div class="container">
        <div class="row">
            <?php foreach ($items as $item) : ?>
                <div class="col-md-3 py-3 py-md-0">
                    <div class="card">
                        <img src="<?php echo $item['image_path']; ?>" alt="<?php echo $item['name']; ?>">
                        <div class="card-body">
                            <div class="star text-center">
                                <i class="fa-regular fa-star"></i>
                                <i class="fa-regular fa-star"></i>
                                <i class="fa-regular fa-star"></i>
                                <i class="fa-regular fa-star"></i>
                                <i class="fa-regular fa-star"></i>
                            </div>
                            <h3><?php echo $item['name']; ?></h3>
                            <p><?php echo $item['description']; ?></p>
                            <p>₱<?php echo number_format($item['price'], 2); ?> <strike>₱<?php echo number_format($item['price'] + 10, 2); ?></strike> 
                                <span class="add-to-cart" onclick="openPopup(<?php echo $item['id']; ?>, '<?php echo $item['name']; ?>', <?php echo number_format($item['price'], 2); ?>)">
                                    <i class="fa-solid fa-cart-shopping"></i>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Popup Form -->
<div id="popup-form" class="popup-form-overlay" style="display:none;">
    <div class="popup-form-container">
        <span class="popup-form-close" onclick="closePopup()">&times;</span>
        <h2>Add to Cart</h2>
        <div id="popup-alert" class="alert"></div>
        <form id="cart-form" action="menu.php" method="POST">
            <input type="hidden" id="item_id" name="item_id" value="">
            <input type="hidden" id="item_price" name="item_price" value="">
            <div class="form-group">
                <label for="item_name">Item Name</label>
                <input type="text" id="item_name" name="item_name" readonly>
            </div>
            <div class="form-group">
                <label for="item_price">Price</label>
                <input type="text" id="item_price_display" name="item_price_display" readonly>
            </div>
            <div class="form-group">
                <label for="quantity">Quantity</label>
                <input type="number" id="quantity" name="quantity" min="1" required>
            </div>
            <button type="submit" class="btn btn-primary">Add to Cart</button>
        </form>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
function openPopup(itemId, itemName, itemPrice) {
    document.getElementById('item_id').value = itemId;
    document.getElementById('item_name').value = itemName;
    document.getElementById('item_price').value = itemPrice;
    document.getElementById('item_price_display').value = itemPrice.toFixed(2);
    document.getElementById('popup-form').style.display = 'flex';
}

function closePopup() {
    document.getElementById('popup-form').style.display = 'none';
}
</script>

<script>
    setTimeout(function() {
        window.location.href = 'logout.php';
    }, 10000); // 10000 milliseconds = 10 seconds
</script>


</body>
</html>