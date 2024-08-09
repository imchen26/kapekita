<?php
session_start();

// Redirect to login page if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection and functions
include 'navbar.php'; // Adjust to your navigation bar or database connection file
include 'includes/db.php'; // Adjust to your database connection file

// Initialize variables
$error_message = '';
$success_message = '';

// Fetch user data from database
$user_id = $_SESSION['user_id'];
$userData = fetchUserData($user_id);

// Function to fetch user data from the database
function fetchUserData($user_id) {
    global $conn;

    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        return null;
    }
}

// Fetch user blogs from the database
function fetchUserBlogs($user_id) {
    global $conn;

    $query = "SELECT * FROM blogs WHERE author_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}

// Fetch user reviews from the database
function fetchUserReviews($user_id) {
    global $conn;

    $query = "SELECT * FROM reviews WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to fetch user orders from the database
function fetchUserOrders($user_id) {
    global $conn;

    $orders = array();

    $query = "SELECT * FROM orders WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $order_id = $row['id'];
        $order_items = fetchOrderItems($order_id);
        $row['items'] = $order_items;
        $orders[] = $row;
    }

    return $orders;
}

// Function to fetch order items for a specific order
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

// Check if user data was successfully fetched
if ($userData) {
    $full_name = $userData['full_name'];
    $email = $userData['email'];
    $profile_photo = 'images/' . $userData['profile_photo']; 
    $wallet_balance = $userData['wallet']; 
} else {
    // Handle error if user data is not retrieved
    $full_name = null;
    $email = null;
    $profile_photo = null;
    $wallet_balance = 0; // Default to 0 or handle as necessary
}

// Fetch blogs and reviews
$userBlogs = fetchUserBlogs($user_id);
$userReviews = fetchUserReviews($user_id);
$userOrders = fetchUserOrders($user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@200;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            background-image: url('css/bc3.png');
            font-family: 'Nunito', sans-serif;
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .content-container {
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 50px auto;
        }

        .blank-container {
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 50px auto;
        }

        .order-container {
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .order-container.completed {
            background-color: #d4edda; /* Light green */
            border-color: #c3e6cb;
        }

        .order-container.pending {
            background-color: #f8d7da; /* Light red */
            border-color: #f5c6cb;
        }

        .order-details {
            margin-top: 10px;
        }

        .pay-now-btn {
            padding: 8px 16px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .pay-now-btn:hover {
            background-color: #0056b3;
        }

        .profile-info {
            text-align: center;
            margin-bottom: 20px;
        }

        .profile-info img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
            margin-bottom: 20px;
        }

        #editProfileBtn {
            display: block;
            width: 100%;
            max-width: 200px;
            margin: 15px auto;
            padding: 10px;
            background-color: #8c5932;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        #editProfileBtn:hover {
            background-color: #6e472b;
        }

        .wallet-section {
            margin-top: 20px;
            text-align: center;
        }

        .wallet-section p {
            margin-bottom: 10px;
        }

        button {
            padding: 8px 16px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            background-color: #007bff; /* Blue background color */
            color: #fff; /* White text color */
            margin-right: 5px;
        }

        /* Style specifically for the "Add Blog" and "Add Review" buttons */
        #addBlogBtn,
        #addReviewBtn {
            background-color: #28a745; /* Green background color */
        }

        /* Hover effect for buttons */
        button:hover {
            opacity: 0.8;
        }

        .popup-form-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .popup-form-container {
            background-color: #b2744c;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            max-width: 100%;
            width: 500px;
            color: white;
            overflow-y: auto;
            max-height: 80%;
            position: relative; /* Ensure the close button positioning */
        }

        .popup-form-container h2 {
            margin-top: 0;
        }

        .popup-form-container form {
            margin-top: 20px;
        }

        .popup-form-container form label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .popup-form-container form input[type="text"],
        .popup-form-container form input[type="email"],
        .popup-form-container form input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
        }

        .popup-form-container form input[type="file"] {
            padding-top: 5px;
        }

        .popup-form-container form button {
            width: 100%;
            padding: 10px;
            background: #8c5932;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.5s ease;
        }

        .popup-form-container form button:hover {
            background: #6e472b;
        }

        .popup-form-close {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
            color: white;
            font-size: 24px;
        }

        .adminsettings .alert {
            margin-top: 20px;
            padding: 10px;
            border-radius: 5px;
        }

        .adminsettings .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }

        .adminsettings .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 70%;
            position: relative;
        }

        .close {
            position: absolute; 
            top: 10px; 
            right: 25px; 
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer; 
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="content-container">
        <h2>User Profile</h2>
        <div class="profile-info">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($full_name); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
            <p><strong>Wallet Balance:</strong> ₱<?php echo number_format($wallet_balance, 2); ?></p>
            <?php if ($profile_photo): ?>
                <img src="<?php echo htmlspecialchars($profile_photo); ?>" alt="Profile Picture">
            <?php endif; ?>
        </div>

        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php elseif ($success_message): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <button id="editProfileBtn" onclick="window.location.href='wallet.php'">Top Up</button>
        <button id="editProfileBtn" onclick="togglePopup('editProfileFormOverlay')">Edit Profile</button>

        <h3 style="margin-top: 30px; margin-bottom: 15px;">My Blogs</h3>
        <ul class="list-group">
            <?php foreach ($userBlogs as $blog): ?>
                <li class="list-group-item">
                    <h5><?php echo htmlspecialchars($blog['title']); ?></h5>
                    <p><?php echo htmlspecialchars($blog['content']); ?></p>
                    <p><strong>Author:</strong> <?php echo htmlspecialchars($blog['author_name']); ?></p>
                    <button id="addBlogBtn" onclick="window.location.href='blog.php'">Add Blog</button>
                    <button onclick="togglePopup('editBlogFormOverlay', <?php echo $blog['id']; ?>)">Edit</button>
                    <button onclick="togglePopup('deleteBlogFormOverlay', <?php echo $blog['id']; ?>)">Delete</button>
                </li>
            <?php endforeach; ?>
        </ul>

        <h3 style="margin-top: 30px; margin-bottom: 15px;">My Reviews</h3>
        <ul class="list-group">
            <?php foreach ($userReviews as $review): ?>
                <li class="list-group-item">
                    <h5><?php echo htmlspecialchars($review['title']); ?></h5>
                    <p><?php echo htmlspecialchars($review['content']); ?></p>
                    <p><strong>Author:</strong> <?php echo htmlspecialchars($review['author_name']); ?></p>
                    <button id="addReviewBtn" onclick="window.location.href='reviews.php'">Add Review</button>
                    <button onclick="togglePopup('editReviewFormOverlay', <?php echo $review['id']; ?>)">Edit</button>
                    <button onclick="togglePopup('deleteReviewFormOverlay', <?php echo $review['id']; ?>)">Delete</button>
                </li>
            <?php endforeach; ?>
        </ul>

        <h3 style="margin-top: 30px; margin-bottom: -30px;">My Orders</h3>
        <div class="blank-container">
            <?php foreach ($userOrders as $order): ?>
                <div class="order-container <?php echo $order['status']; ?>">
                    <h3 style="font-size: 20px;">Order ID: <?php echo $order['id']; ?></h3>
                    <p><strong>Total Amount:</strong> ₱<?php echo number_format($order['total_amount'], 2); ?></p>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
                    <div class="order-details">
                    <button onclick="toggleOrderDetails(<?php echo $order['id']; ?>)">View Order Details</button>
                        <?php if ($order['status'] === 'pending'): ?>
                            <button class="pay-now-btn" onclick="togglePopup('payNowFormOverlay', <?php echo $order['id']; ?>)">Pay Now</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="orderDetailsModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeOrderDetails()">&times;</span>
            <h3>Order Details</h3>
            <ul id="orderItemsList"></ul>
        </div>
    </div>

    <!-- Popup form for payment -->
    <div id="payNowFormOverlay" class="popup-form-overlay">
        <div class="popup-form-container">
            <span class="popup-form-close" onclick="togglePopup('payNowFormOverlay')">&times;</span>
            <h2>Confirm Payment</h2>
            <form id="paymentForm" action="process_payment.php" method="POST">
                <input type="hidden" name="order_id" id="order_id" value="">
                <label for="amount">Amount to Deduct:</label>
                <input type="text" name="amount" id="amount" value="<?php echo $order['total_amount']; ?>" disabled>
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $success_message; ?>
                    </div>
                <?php elseif (!empty($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                <button type="button" onclick="payOrder()">Confirm Payment</button>
            </form>
        </div>
    </div>

    <!-- Popup forms for editing profile -->
    <div id="editProfileFormOverlay" class="popup-form-overlay">
        <div class="popup-form-container">
            <span class="popup-form-close" onclick="togglePopup('editProfileFormOverlay')">&times;</span>
            <h2>Edit Profile</h2>
            <form action="edit_profile.php" method="POST" enctype="multipart/form-data">
                <label for="full_name">Full Name:</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" required>
                <label for="email">Email:</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                <label for="profile_photo">Profile Photo:</label>
                <input type="file" name="profile_photo">
                <button type="submit">Update Profile</button>
            </form>
        </div>
    </div>

    <!-- Edit Blog Form -->
    <div id="editBlogFormOverlay" class="popup-form-overlay">
        <div class="popup-form-container">
            <span class="popup-form-close" onclick="togglePopup('editBlogFormOverlay')">&times;</span>
            <h2>Edit Blog</h2>
            <form action="profileeditblog.php" id="editBlogForm" method="POST">
                <input type="hidden" name="blog_id" id="editBlogId" value="">
                <label for="title">Title:</label>
                <input type="text" name="title" id="editBlogTitle" required>
                <label for="content">Content:</label>
                <input type="text" name="content" id="editBlogContent" required>
                <label for="author_name">Author Name:</label>
                <input type="text" name="author_name" id="editBlogAuthorName" required>
                <button type="submit">Update Blog</button>
            </form>
        </div>
    </div>
    
    <!-- Delete dit Blog Form -->
    <div id="deleteBlogFormOverlay" class="popup-form-overlay">
        <div class="popup-form-container">
            <span class="popup-form-close" onclick="togglePopup('deleteBlogFormOverlay')">&times;</span>
            <h2>Delete Blog</h2>
            <form action="profiledeleteblog.php" id="deleteBlogForm" method="POST">
                <input type="hidden" name="blog_id" id="deleteBlogId" value="">
                <p>Are you sure you want to delete this blog?</p>
                <button type="submit">Delete Blog</button>
            </form>
        </div>
    </div>
    
    <!-- Delete dit Blog Form -->
    <div id="deleteBlogFormOverlay" class="popup-form-overlay">
        <div class="popup-form-container">
            <span class="popup-form-close" onclick="togglePopup('deleteBlogFormOverlay')">&times;</span>
            <h2>Delete Blog</h2>
            <form action="profiledeleteblog.php" id="deleteBlogForm" method="POST">
                <input type="hidden" name="blog_id" id="deleteBlogId" value="">
                <p>Are you sure you want to delete this blog?</p>
                <button type="submit">Delete Blog</button>
            </form>
        </div>
    </div>
    
    <!-- Edit Review Form -->
    <div id="editReviewFormOverlay" class="popup-form-overlay">
        <div class="popup-form-container">
            <span class="popup-form-close" onclick="togglePopup('editReviewFormOverlay')">&times;</span>
            <h2>Edit Review</h2>
            <form action="profileeditreview.php" id="editReviewForm" method="POST">
                <input type="hidden" name="review_id" id="editReviewId" value="">
                <label for="title">Title:</label>
                <input type="text" name="title" id="editReviewTitle" required>
                <label for="content">Content:</label>
                <input type="text" name="content" id="editReviewContent" required>
                <label for="author_name">Author Name:</label>
                <input type="text" name="author_name" id="editReviewAuthorName" required>
                <button type="submit">Update Review</button>
            </form>
        </div>
    </div>

    <!-- Delete Review Form -->
    <div id="deleteReviewFormOverlay" class="popup-form-overlay">
        <div class="popup-form-container">
            <span class="popup-form-close" onclick="togglePopup('deleteReviewFormOverlay')">&times;</span>
            <h2>Delete Review</h2>
            <form action="profiledeletereview.php" id="deleteReviewForm" method="POST">
                <input type="hidden" name="review_id" id="deleteReviewId" value="">
                <p>Are you sure you want to delete this review?</p>
                <button type="submit">Delete Review</button>
            </form>
        </div>
    </div>

    <script>
        function toggleOrderDetails(orderId) {
            // Fetch order details from the server
            fetch('get_order_details.php?order_id=' + orderId)
                .then(response => response.json())
                .then(data => {
                    // Populate the modal with order items
                    const orderItemsList = document.getElementById('orderItemsList');
                    orderItemsList.innerHTML = '';

                    data.items.forEach(item => {
                        const li = document.createElement('li');
                        li.textContent = item.name + ' - Quantity: ' + item.quantity + ', Price: ' + item.price;
                        orderItemsList.appendChild(li);
                    });

                    // Show the modal
                    document.getElementById('orderDetailsModal').style.display = 'block';
                })
                .catch(error => console.error('Error:', error));
        }

        function closeOrderDetails() {
            document.getElementById('orderDetailsModal').style.display = 'none';
        }

        function payOrder() {
            var orderId = document.getElementById('order_id').value;
            
            if (orderId.trim() === '') {
                alert('Order ID is required.');
                return;
            }
            
            if (confirm('Are you sure you want to confirm the payment?')) {
                // AJAX request to process payment
                var formData = new FormData();
                formData.append('order_id', orderId);
                
                fetch('payment.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update UI or show success message
                        alert('Payment successful!');
                        // Reload or update order status
                        location.reload(); // Example: Refresh the page
                    } else {
                        alert('Payment failed.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred during payment.');
                });
            }
        }

        // Function to toggle popup visibility and set form fields if an ID is provided
        function togglePopup(formId, id = null) {
            var popup = document.getElementById(formId);
            popup.style.display = popup.style.display === "none" ? "flex" : "none";

            // If an ID is provided, update form fields accordingly
            if (id !== null) {
                if (formId === 'editBlogFormOverlay') {
                    // Set the form fields for editing a blog
                    const blog = <?php echo json_encode($userBlogs); ?>.find(blog => blog.id === id);
                    if (blog) {
                        document.getElementById('editBlogId').value = blog.id;
                        document.getElementById('editBlogTitle').value = blog.title;
                        document.getElementById('editBlogContent').value = blog.content;
                        document.getElementById('editBlogAuthorName').value = blog.author_name;
                    }
                } else if (formId === 'deleteBlogFormOverlay') {
                    // Set the blog ID for deleting a blog
                    document.getElementById('deleteBlogId').value = id;
                } else if (formId === 'editReviewFormOverlay') {
                    // Set the form fields for editing a review
                    const review = <?php echo json_encode($userReviews); ?>.find(review => review.id === id);
                    if (review) {
                        document.getElementById('editReviewId').value = review.id;
                        document.getElementById('editReviewTitle').value = review.title;
                        document.getElementById('editReviewContent').value = review.content;
                        document.getElementById('editReviewAuthorName').value = review.author_name;
                    }
                } else if (formId === 'deleteReviewFormOverlay') {
                    // Set the review ID for deleting a review
                    document.getElementById('deleteReviewId').value = id;
                }
            }
        }

        // Script to display the profile photo
        document.addEventListener("DOMContentLoaded", function() {
            const profilePhotoElement = document.getElementById('profilePhoto');
            if (profilePhotoElement) {
                profilePhotoElement.src = "<?php echo htmlspecialchars($profile_photo); ?>";
            }
        });

        // Initially hide the popups on page load
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.popup-form-overlay').forEach(function(popup) {
                popup.style.display = 'none';
            });
        });
    </script>
</body>
</html>
