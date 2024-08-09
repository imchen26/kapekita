<?php
$servername = "kapekita.com";
$username = "root";
$password = "";
$dbname = "secwb"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>