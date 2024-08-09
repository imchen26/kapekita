<?php
// Ensure session is started first
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'includes/db.php';

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$user_name = '';

if ($is_logged_in) {
    // Check if the logged-in user is an admin
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT * FROM admins WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // User is an admin
        $user_name = "Admin";
    } else {
        // User is not an admin, redirect to appropriate page
        header("Location: home.php");
        exit();
    }
} else {
    // If user is not logged in, redirect to login page
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kape Kita Coffee Shop</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <style>
        .admin-menu {
            background-color: #b2744c;
            font-family: "Nunito", sans-serif;
            padding: 10px 0; 
            border-radius: 10px;
        }

        .admin-menu ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
            text-align: center;
        }

        .admin-menu li {
            display: inline;
        }

        .admin-menu li a {
            text-decoration: none;
            color: black;
            font-weight: bold;
            padding: 8px 12px;
            transition: 0.5s;
            border-radius: 5px;
        }

        .admin-menu li a:hover {
            background-color: #f7f5f2;
            color: black;
        }
    </style>
</head>

<body>
    <div class="all-content">
        <nav class="navbar navbar-expand-lg" id="navbar">
            <div class="container-fluid">
                <a class="navbar-brand" href="#" id="logo"><img src="./image/logo.png" alt=""></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span><i class="fa-solid fa-bars" style="color: white; font-size: 23px;"></i></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Admin Menu -->
                    <div class="admin-menu">
                        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                            <li class="nav-item">
                                <a class="nav-link" href="admin.php">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="manageuser.php">Manage Users</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="manageitem.php">Manage Items</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="manageorder.php">Manage Orders</a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownReviews" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Manage Reviews
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="navbarDropdownReviews">
                                    <li><a class="dropdown-item" href="managereview.php">Manage Reviews</a></li>
                                    <li><a class="dropdown-item" href="manageblog.php">Manage Blogs</a></li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="adminsettings.php">Settings</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="debug_register.php">Register(debug)</a>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Admin Dropdown -->
                    <ul class="navbar-nav">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo $user_name; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-1o+YFjssZd/P3QqB0NZ5f/t5or8JBCR6TKmGlB/8x/hsrvYAZtL2hRApzKcJJ3cH" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-KyZXEAg3QhqLMpG8r+WB0nqBh/lYvJLxZovbZwF9N9J1gDcMG/fdsoXdiN7XmU+J" crossorigin="anonymous"></script>
</body>
</html>
