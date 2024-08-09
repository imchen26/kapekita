<?php
session_start();

// Redirect to login page if not authenticated
if (!isset($_SESSION['is_admin'])) {
    header("Location: login.php");
    exit();
}

include 'adminnavbar.php';
include 'includes/db.php'; // Make sure to include your database connection

// Fetch blogs from the database
$sql = "SELECT * FROM blogs";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Blogs - Coffee Shop</title>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="css/adminstyle.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <style>
        body {
            background-image: url('image/coffeebg.jpg');
        }
        .blog-image {
            max-width: 100px; 
            max-height: 75px; 
        }
    </style>
</head>
<body>

<div class="content-container">
    <div class="container">
        <h2>Manage Blogs</h2>
        <!-- Button to show Add Blog form -->
        <button id="btn-show-add-blog-form" class="btn btn-success mb-3">Add Blog</button>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Content</th>
                    <th>Author ID</th>
                    <th>Author Name</th>
                    <th>Image</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['title']; ?></td>
                        <td><?php echo $row['content']; ?></td>
                        <td><?php echo $row['author_id']; ?></td>
                        <td><?php echo $row['author_name']; ?></td>
                        <td><img src="<?php echo $row['image']; ?>" alt="Blog Image" class="blog-image"></td>
                        <td><?php echo date('M j, Y - g:i A', strtotime($row['created_at'])); ?></td>
                        <td>
                            <!-- Edit button with onclick event to show edit form -->
                            <button class="btn btn-primary btn-sm" onclick="showEditForm(<?php echo $row['id']; ?>)">Edit</button>
                            <!-- Delete button with onclick event to show delete confirmation -->
                            <button class="btn btn-danger btn-sm" onclick="showDeleteForm(<?php echo $row['id']; ?>)">Delete</button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Blog Form -->
<div class="popup-form-overlay" id="add-blog-overlay">
    <div class="popup-form-container">
        <span class="popup-form-close" onclick="closeForm()">&times;</span>
        <h2>Add Blog</h2>
        <form id="add-blog-form" action="addblog.php" method="POST" enctype="multipart/form-data">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required>
            <label for="content">Content:</label>
            <textarea id="content" name="content" rows="4" required></textarea>
            <label for="author_id">Author:</label>
            <select id="author_id" name="author_id" required>
                <?php
                // Fetch authors from the users table
                $sql_users = "SELECT id, full_name FROM users";
                $result_users = mysqli_query($conn, $sql_users);
                while ($row_users = mysqli_fetch_assoc($result_users)) {
                    echo "<option value='" . $row_users['id'] . "'>" . $row_users['full_name'] . "</option>";
                }
                ?>
            </select>
            <label for="image" style="margin-top: 10px; margin-bottom: 5px;">Image:</label>
            <input type="file" id="image" name="image" accept=".jpg, .jpeg, .png" required>
            <button type="submit" class="btn btn-success" style="margin-top: 10px;">Add Blog</button>
        </form>
    </div>
</div>
</div>

<!-- Edit Blog Form -->
<div class="popup-form-overlay" id="edit-blog-overlay">
    <div class="popup-form-container">
        <span class="popup-form-close" onclick="closeForm()">&times;</span>
        <h2>Edit Blog</h2>
        <form id="edit-blog-form" enctype="multipart/form-data">
            <input type="hidden" id="edit_blog_id" name="blog_id">
            <label for="edit_title">Title:</label>
            <input type="text" id="edit_title" name="title" required>
            <label for="edit_content">Content:</label>
            <textarea id="edit_content" name="content" rows="4" required></textarea>
            <label for="edit_author_id">Author:</label>
            <select id="edit_author_id" name="author_id" required>
                <?php
                // Fetch authors from the users table
                mysqli_data_seek($result_users, 0); // Reset pointer
                while ($row_users = mysqli_fetch_assoc($result_users)) {
                    echo "<option value='" . $row_users['id'] . "'>" . $row_users['full_name'] . "</option>";
                }
                ?>
            </select>
            <label for="edit_image" style="margin-top: 10px; margin-bottom: 5px;">Image:</label>
            <input type="file" id="edit_image" name="image" accept=".jpg, .jpeg, .png">
            <button type="button" onclick="saveChanges()" class="btn btn-success" style="margin-top: 10px;">Save Changes</button>
        </form>
    </div>
</div>


<!-- Delete Blog Confirmation Form -->
<div class="popup-form-overlay" id="delete-blog-overlay">
    <div class="popup-form-container">
        <span class="popup-form-close" onclick="closeForm()">&times;</span>
        <h2>Delete Blog</h2>
        <form id="delete-blog-form">
            <input type="hidden" id="delete_blog_id" name="blog_id">
            <p>Are you sure you want to delete this blog?</p>
            <button type="button" onclick="deleteBlog()" class="btn btn-danger">Delete Blog</button>
        </form>
    </div>
</div>

<!-- Include Bootstrap JS at the end of the body -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JavaScript for handling pop-up forms -->
<script>
    // Function to show Add Blog form
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('btn-show-add-blog-form').addEventListener('click', function() {
            document.getElementById('add-blog-overlay').style.display = 'flex';
        });
    });

    // Function to show Edit Blog form
    function showEditForm(blogId) {
        document.getElementById('edit-blog-overlay').style.display = 'flex';
        document.getElementById('edit_blog_id').value = blogId;
        // Fetch blog details via JavaScript or pre-loaded data if available
        let xhr = new XMLHttpRequest();
        xhr.open("GET", "getblogdetails.php?id=" + blogId, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    let blog = JSON.parse(xhr.responseText);
                    document.getElementById('edit_title').value = blog.title;
                    document.getElementById('edit_content').value = blog.content;
                    document.getElementById('edit_author_id').value = blog.author_id;
                    document.getElementById('edit_author_name').value = blog.author_name;
                } else {
                    console.error("Failed to fetch blog details.");
                }
            }
        };
        xhr.send();
    }

    // Function to show Delete Blog confirmation
    function showDeleteForm(blogId) {
        document.getElementById('delete-blog-overlay').style.display = 'flex';
        document.getElementById('delete_blog_id').value = blogId;
    }

    // Function to close any form overlay
    function closeForm() {
        document.querySelectorAll('.popup-form-overlay').forEach(function(el) {
            el.style.display = 'none';
        });
    }

    function saveChanges() {
        let form = document.getElementById('edit-blog-form');
        let formData = new FormData(form);

        // AJAX request to save changes
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "editblog.php", true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    console.log(xhr.responseText);
                    location.reload();
                } else {
                    console.error("Failed to save changes.");
                }
            }
        };
        xhr.send(formData);

        closeForm();
    }

    // Function to delete a blog
    function deleteBlog() {
        let blogId = document.getElementById('delete_blog_id').value;

        // AJAX request to delete blog
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "deleteblog.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    console.log("Blog deleted successfully.");
                    location.reload();
                } else {
                    console.error("Failed to delete blog.");
                }
            }
        };
        xhr.send("blog_id=" + blogId);
        
        closeForm();
    }
</script>
</body>
</html>