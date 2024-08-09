<?php
session_start();
ob_start();

// Redirect to login page if not authenticated
if (!isset($_SESSION['is_admin'])) {
    header("Location: login.php");
    exit();
}

include 'adminnavbar.php';
include 'includes/db.php';

// Function to log item actions
function logItemAction($adminEmail, $action, $itemId = null) {
    $logFile = 'logs/item_actions.log';
    $timezone = 'Asia/Manila';
    date_default_timezone_set($timezone);
    $date = date('Y-m-d H:i:s');
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $logMessage = "[$date] Admin Email: $adminEmail - IP: $ipAddress - User-Agent: $userAgent - Action: $action";
    if ($itemId) {
        $logMessage .= " - Item ID: $itemId";
    }
    $logMessage .= "\n";
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

$add_item_error_message = "";
$edit_item_error_message = "";
$delete_item_error_message = "";

// Get admin email
$adminEmail = '';
if (isset($_SESSION['user_id'])) {
    $adminId = $_SESSION['user_id'];
    $sql = "SELECT email FROM admins WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $adminId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $adminEmail = $row['email'];
    } else {
        $adminEmail = 'unknown'; // In case of an error
    }
}

// Handle Add Item
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_item'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];

    // Handle image upload
    $image_path = '';
    if ($_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "menu/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        } else {
            $add_item_error_message = "Error uploading image.";
        }
    }

    // If no validation errors, proceed to add item
    if (empty($add_item_error_message)) {
        $sql = "INSERT INTO items (name, description, price, image_path, quantity) 
                VALUES ('$name', '$description', $price, '$image_path', $quantity)";
        
        if ($conn->query($sql) === TRUE) {
            // Log the add action
            logItemAction($adminEmail, 'Add', $conn->insert_id);

            // Redirect to refresh the page after adding
            header("Location: manageitem.php");
            exit();
        } else {
            $add_item_error_message = "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

// Handle Edit Item
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_item'])) {
    $item_id = $_POST['item_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];

    // Handle image update
    $image_path = $_POST['current_image'];
    if ($_FILES['edit_image']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "menu/";
        $target_file = $target_dir . basename($_FILES["edit_image"]["name"]);
        if (move_uploaded_file($_FILES["edit_image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        } else {
            $edit_item_error_message = "Error uploading image.";
        }
    }

    // Proceed if no validation errors
    if (empty($edit_item_error_message)) {
        $sql = "UPDATE items SET name='$name', description='$description', price=$price, quantity=$quantity";
        
        // Only update image_path if a new image was uploaded
        if (!empty($image_path)) {
            $sql .= ", image_path='$image_path'";
        }
        
        $sql .= " WHERE id=$item_id";

        if ($conn->query($sql) === TRUE) {
            // Log the edit action
            logItemAction($adminEmail, 'Edit', $item_id);

            // Redirect to refresh the page after editing
            header("Location: manageitem.php");
            exit();
        } else {
            $edit_item_error_message = "Error updating record: " . $conn->error;
        }
    }
}

// Handle Delete Item
if (isset($_POST['delete_item'])) {
    $item_id = $_POST['item_id'];

    // Delete item from database
    $sql = "DELETE FROM items WHERE id=$item_id";
    if ($conn->query($sql) === TRUE) {
        // Log the delete action
        logItemAction($adminEmail, 'Delete', $item_id);

        // Redirect to refresh the page after deletion
        header("Location: manageitem.php");
        exit();
    } else {
        $delete_item_error_message = "Error deleting record: " . $conn->error;
    }
}

// Fetch items from db
$sql = "SELECT * FROM items";
$result = $conn->query($sql);

$conn->close();
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Items - Kape Kita Coffee Shop</title>
    <link rel="stylesheet" href="css/adminstyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <style>
        body {
            background-image: url('image/coffeebg.jpg');
        }
    </style>
</head>
<body>
    <div class="manageitems">
    <div class="content-container">
        <h1 class="admin-page-heading">Manage Items</h1>
        <div class="admin-edit-delete-buttons">
            <div class="btn-add-container">
                <button class="btn btn-add" id="btn-show-add-form" style="font-size: 1.2em; font-weight: bold;">Add Item</button>
            </div>
        </div>
            <div class="admin-profile-grid">
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<div class='admin-profile-card'>";
                        echo "<img src='" . $row["image_path"] . "' alt='" . $row["name"] . "' class='admin-profile-photo'>";
                        echo "<div class='admin-profile-info'>";
                        echo "<p><strong>ID:</strong> " . $row["id"]. "</p>";
                        echo "<p><strong>Name:</strong> " . $row["name"]. "</p>";
                        echo "<p><strong>Description:</strong> " . $row["description"]. "</p>";
                        echo "<p><strong>Price:</strong> ₱" . $row["price"]. "</p>";
                        echo "<p><strong>Quantity:</strong> " . $row["quantity"]. "</p>";
                        echo "</div>";
                        echo "<div class='admin-edit-delete-buttons'>";
                        echo "<button class='btn btn-edit' onclick='showEditForm(" . $row['id'] . ")'>Edit</button>";
                        echo "<button class='btn btn-delete' onclick='showDeleteForm(" . $row['id'] . ")'>Delete</button>";
                        echo "</div>"; 
                        echo "</div>"; 
                    }
                } else {
                    echo "<p class='admin-no-results'>No items found.</p>";
                }
                ?>
            </div>
        </div>
    </div>
        <!-- Add Item Form -->
        <div class="popup-form-overlay" id="add-item-overlay">
            <div class="popup-form-container">
                <span class="popup-form-close" onclick="closeForm()">&times;</span>
                <h2>Add Item</h2>
                <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="4"></textarea>
                    <label for="price">Price:</label>
                    <input type="number" id="price" name="price" step="0.01" required>
                    <label for="quantity">Quantity:</label>
                    <input type="number" id="quantity" name="quantity" value="0" required>
                    <label for="image" style="margin-top: 5px; margin-bottom: 5px;">Image:</label>
                    <input type="file" id="image" name="image" accept="image/*" required>
                    <?php if (!empty($add_item_error_message)) : ?>
                        <div class="alert alert-danger" style="margin-top: 5px;"><?php echo $add_item_error_message; ?></div>
                    <?php endif; ?>
                    <button type="submit" name="add_item" class="btn btn-success">Add Item</button>
                </form>
            </div>
        </div>

        <!-- Edit Item Form -->
        <div class="popup-form-overlay" id="edit-item-overlay">
            <div class="popup-form-container">
                <span class="popup-form-close" onclick="closeForm()">&times;</span>
                <h2>Edit Item</h2>
                <form id="edit-item-form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
                    <input type="hidden" id="edit_item_id" name="item_id">
                    <input type="hidden" id="current_image" name="current_image">
                    <label for="edit_name">Name:</label>
                    <input type="text" id="edit_name" name="name" required>
                    <label for="edit_description">Description:</label>
                    <textarea id="edit_description" name="description" rows="4" required></textarea>
                    <label for="edit_price">Price:</label>
                    <input type="number" id="edit_price" name="price" step="0.01" required>
                    <label for="edit_quantity">Quantity:</label>
                    <input type="number" id="edit_quantity" name="quantity" value="0" required>
                    <label for="edit_image" style="margin-top: 5px; margin-bottom: 5px;">Image (optional):</label>
                    <input type="file" id="edit_image" name="edit_image" accept="image/*">
                    <?php if (!empty($edit_item_error_message)) : ?>
                        <div class="alert alert-danger" style="margin-top: 5px;"><?php echo $edit_item_error_message; ?></div>
                    <?php endif; ?>           
                    <button type="submit" name="edit_item" class="btn btn-success">Save Changes</button>
                </form>
            </div>
        </div>

        <!-- Delete Item Form -->
        <div class="popup-form-overlay" id="delete-item-overlay">
            <div class="popup-form-container">
                <span class="popup-form-close" onclick="closeForm()">&times;</span>
                <h2>Delete Item</h2>
                <form id="delete-item-form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <input type="hidden" id="delete_item_id" name="item_id">
                    <p>Are you sure you want to delete this item?</p>
                    <?php if (!empty($delete_item_error_message)) : ?>
                        <div class="alert alert-danger" style="margin-top: 5px;"><?php echo $delete_item_error_message; ?></div>
                    <?php endif; ?>
                    <button type="submit" name="delete_item" class="btn btn-delete">Delete Item</button>
                </form>
            </div>
        </div>

    </div> 

    <script>
        // Function to show Add Item form
        document.getElementById('btn-show-add-form').addEventListener('click', function() {
            document.getElementById('add-item-overlay').style.display = 'flex';
        });

        // Function to show Edit Item form
        function showEditForm(itemId) {
            document.getElementById('edit-item-overlay').style.display = 'flex';
            // Fetch item details and populate form (optional)
            // Example: document.getElementById('edit_name').value = 'Coffee';
            document.getElementById('edit_item_id').value = itemId;
        }

        // Function to show Delete Item form
        function showDeleteForm(itemId) {
            document.getElementById('delete-item-overlay').style.display = 'flex';
            document.getElementById('delete_item_id').value = itemId;
        }

        // Function to close any form overlay
        function closeForm() {
            document.querySelectorAll('.popup-form-overlay').forEach(function(el) {
                el.style.display = 'none';
            });
        }
    </script>
</body>
</html>
