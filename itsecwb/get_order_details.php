<?php
    session_start();

    // Include database connection
    include 'includes/db.php'; // Adjust to your database connection file

    if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
        http_response_code(403);
        exit();
    }

    $order_id = intval($_GET['order_id']);
    $user_id = $_SESSION['user_id'];

    // Verify the order belongs to the logged-in user
    $query = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(403);
        exit();
    }

    // Fetch order items
    function fetchOrderItems($order_id) {
        global $conn;

        $items = array();

        $query = "SELECT oi.*, i.name 
                FROM order_items oi 
                JOIN items i ON oi.item_id = i.id 
                WHERE oi.order_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }

        return $items;
    }

    $orderItems = fetchOrderItems($order_id);

    // Return order items as JSON
    header('Content-Type: application/json');
    echo json_encode(['items' => $orderItems]);
?>
