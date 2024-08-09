<?php
session_start();

// Redirect to login page if not authenticated
if (!isset($_SESSION['is_admin'])) {
    header("Location: login.php");
    exit();
}

include 'adminnavbar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Kape Kita Coffee Shop</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <!-- bootstrap links -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!-- bootstrap links -->
    <!-- fonts links -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <!-- fonts links -->
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
        }

        .admin-container {
            position: relative;
            font-family: "Nunito", sans-serif;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .background-image {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        .background-image img {
            width: 100%;
            height: 100%;
            filter: blur(3px);
            object-fit: cover;
        }

        .text-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: #ffffff;
            z-index: 1;
        }

        .text-content h1 {
            font-size: 3em;
            margin-bottom: 20px;
        }

        .text-content p {
            font-size: 1.5em;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="background-image">
            <img src="image/coffeebg.jpg" alt="Coffee Background">
        </div>
        
        <!-- Text content above the background -->
        <div class="text-content">
            <h1>Welcome to the Admin Dashboard</h1>
            <p>Manage your coffee shop operations effectively.</p>
        </div>
        
        <!-- Your admin content here (optional) -->
    </div>
</body>
</html>
