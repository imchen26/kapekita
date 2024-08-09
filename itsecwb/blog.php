<?php
session_start(); // Start the session
include 'includes/db.php';
include 'navbar.php';

// Fetch blogs from the database
$sql = "SELECT * FROM blogs ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Blogs - Kape Kita Coffee Shop</title>
    <!-- Include your CSS and any necessary scripts -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <!-- bootstrap links -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!-- fonts links -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <!-- fonts links -->
    <style>
        /* Your existing CSS */
        <?php include 'css/style.css'; ?>

        /* Popup form CSS */
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
            position: relative;
            background-color: #b2744c;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            width: 60%;
            max-width: 600px;
            color: white;
            overflow-y: auto;
            max-height: 80%;
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
        .popup-form-container form input[type="number"],
        .popup-form-container form input[type="email"],
        .popup-form-container form input[type="password"],
        .popup-form-container form input[type="file"] {
            width: calc(100% - 20px);
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
        }
    </style>
    <script>
        function togglePopup() {
            var popup = document.getElementById('popupForm');
            popup.style.display = popup.style.display === 'flex' ? 'none' : 'flex';
        }
    </script>
</head>
<body>

<!-- Include your navigation or header here -->

<section class="blogs" id="blogs">
    <div class="container">
        <h1>Latest <span>Blogs</span></h1>

        <?php
        // Check if user is logged in
        if (isset($_SESSION['user_id'])) {
            echo '<button onclick="togglePopup()" class="btn btn-primary" style="margin-top: -10px; margin-bottom: 15px; background: #b2744c;">Add Blog</button>';
        }
        ?>

        <div class="row">
            <?php
            // Check if there are any blogs
            if (mysqli_num_rows($result) > 0) {
                // Output data of each row
                while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                    <div class="col-md-4 py-3 py-md-0 mb-4">
                        <div class="card">
                            <img src="<?php echo $row['image']; ?>" alt="">
                            <div class="card-body">
                                <h3><?php echo $row['title']; ?></h3>
                                <h5><?php echo $row['author_name'] . ' / ' . date('d F Y', strtotime($row['created_at'])); ?></h5>
                                <p><?php echo $row['content']; ?></p>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<p>No blogs found.</p>";
            }
            ?>
        </div>
    </div>
</section>

<!-- Popup Form -->
<div id="popupForm" class="popup-form-overlay">
    <div class="popup-form-container">
        <span class="popup-form-close" onclick="togglePopup()">&times;</span>
        <h2>Add Blog</h2>
        <form action="add_blog.php" method="post" enctype="multipart/form-data">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required>

            <label for="content">Content:</label>
            <input type="text" id="content" name="content" required>

            <label for="author_name">Author Name:</label>
            <input type="text" id="author_name" name="author_name" required>

            <label for="image">Image:</label>
            <input type="file" id="image" name="image">

            <button type="submit">Submit</button>
        </form>
    </div>
</div>

</body>
</html>

<?php
// Close connection
mysqli_close($conn);
?>
