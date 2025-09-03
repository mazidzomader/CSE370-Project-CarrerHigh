<?php
$servername = 'localhost';
$username = "root";
$password = "";
$dbname = "U_S";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to prevent encoding issues
$conn->set_charset("utf8");

// Optional: Uncomment the line below for debugging (remove in production)
// echo "Connected successfully to database: " . $dbname;
?>