<?php
$servername = 'localhost';
$username = "root";
$password = "";
$dbname = "school";

$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    // echo "Connection Successful";
    mysqli_select_db($conn, $dbname);
}
?>
