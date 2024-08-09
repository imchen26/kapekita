<?php
session_start();

// Redirect to login page if not authenticated
if (!isset($_SESSION['is_admin'])) {
    header("Location: login.php");
    exit();
}

include 'includes/db.php';
include 'adminnavbar.php';

// Fetch reviews
$sql = "SELECT * FROM reviews";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reviews - Coffee Shop</title>
    <link rel="stylesheet" href="css/adminstyle.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <style>
        body {
            background-image: url('image/coffeebg.jpg');
        }
    </style>
</head>
<body>
<div class="content-container">
    <div class="container">
        <h2>Manage Reviews</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User ID</th>
                    <th>Title</th>
                    <th>Content</th>
                    <th>Rating</th>
                    <th>Author Name</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['user_id']; ?></td>
                        <td><?php echo $row['title']; ?></td>
                        <td><?php echo $row['content']; ?></td>
                        <td><?php echo $row['rating']; ?></td>
                        <td><?php echo $row['author_name']; ?></td>
                        <td><?php echo date('M j, Y - g:i A', strtotime($row['created_at'])); ?></td>
                        <td>
                            <form method="post" action="editreview.php">
                                <input type="hidden" name="review_id" value="<?php echo $row['id']; ?>">
                                <button type="button" onclick="showEditForm(<?php echo $row['id']; ?>)" class="btn btn-primary btn-sm" style="margin-bottom: 5px;">Edit</button>
                            </form>
                            <form method="post" action="deletereview.php">
                                <input type="hidden" name="review_id" value="<?php echo $row['id']; ?>">
                                <button type="button" onclick="showDeleteForm(<?php echo $row['id']; ?>)" class="btn btn-danger btn-sm" style="margin: auto;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- Add Review Button -->
        <div class="mt-5"> <!-- Increased margin top to 5 -->
            <button class="btn btn-success" id="btn-show-add-review-form">Add Review</button>
        </div>
    </div>
</div>

<!-- Add Review Form -->
<div class="popup-form-overlay" id="add-review-overlay">
    <div class="popup-form-container">
        <span class="popup-form-close" onclick="closeForm()">&times;</span>
        <h2>Add Review</h2>
        <form method="post" action="addreview.php">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required>
            <label for="content">Content:</label>
            <textarea id="content" name="content" rows="4" required></textarea>
            <label for="rating">Rating:</label>
            <input type="number" id="rating" name="rating" min="1" max="5" required>
            <label for="user_id">User:</label>
            <select id="user_id" name="user_id" required>
                <?php
                // Fetch users from the users table
                $sql_users = "SELECT id, full_name FROM users";
                $result_users = mysqli_query($conn, $sql_users);
                while ($row_users = mysqli_fetch_assoc($result_users)) {
                    echo "<option value='" . $row_users['id'] . "'>" . $row_users['full_name'] . "</option>";
                }
                ?>
            </select>
            <button type="submit" name="add_review" class="btn btn-success" style="margin-top: 15px;">Add Review</button>
        </form>
    </div>
</div>

<!-- Edit Review Form -->
<div class="popup-form-overlay" id="edit-review-overlay">
    <div class="popup-form-container">
        <span class="popup-form-close" onclick="closeForm()">&times;</span>
        <h2>Edit Review</h2>
        <form id="edit-review-form" method="post" action="editreview.php">
            <input type="hidden" id="edit_review_id" name="review_id">
            <label for="edit_title">Title:</label>
            <input type="text" id="edit_title" name="title" required>
            <label for="edit_content">Content:</label>
            <textarea id="edit_content" name="content" rows="4" required></textarea>
            <label for="edit_rating">Rating:</label>
            <input type="number" id="edit_rating" name="rating" min="1" max="5" required>
            <label for="edit_user_id">User:</label>
            <select id="edit_user_id" name="user_id" required>
                <?php
                // Fetch users from the users table
                mysqli_data_seek($result_users, 0); // Reset pointer
                while ($row_users = mysqli_fetch_assoc($result_users)) {
                    echo "<option value='" . $row_users['id'] . "'>" . $row_users['full_name'] . "</option>";
                }
                ?>
            </select>
            <button type="submit" name="edit_review" class="btn btn-success" style="margin-top: 15px;">Save Changes</button>
        </form>
    </div>
</div>

<!-- Delete Review Form -->
<div class="popup-form-overlay" id="delete-review-overlay">
    <div class="popup-form-container">
        <span class="popup-form-close" onclick="closeForm()">&times;</span>
        <h2>Delete Review</h2>
        <form id="delete-review-form" method="post" action="deletereview.php">
            <input type="hidden" id="delete_review_id" name="review_id">
            <p>Are you sure you want to delete this review?</p>
            <button type="submit" name="delete_review" class="btn btn-danger">Delete Review</button>
        </form>
    </div>
</div>

<script>
    // Function to show Add Review form
    document.getElementById('btn-show-add-review-form').addEventListener('click', function() {
        document.getElementById('add-review-overlay').style.display = 'flex';
    });

    // Function to show Edit Review form
    function showEditForm(reviewId) {
        document.getElementById('edit-review-overlay').style.display = 'flex';
        document.getElementById('edit_review_id').value = reviewId;
    }

    // Function to show Delete Review form
    function showDeleteForm(reviewId) {
        document.getElementById('delete-review-overlay').style.display = 'flex';
        document.getElementById('delete_review_id').value = reviewId;
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
