<?php
session_start();
// Database connection
$servername = 'localhost';
$username = "root";
$password = "";
$dbname = "demo_project";

$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    mysqli_select_db($conn, $dbname);
}

// Login processing
if (isset($_POST['user']) && isset($_POST['pass'])){
    $u = $_POST['user'];
    $p = $_POST['pass'];

    // Updated SQL query to use LOGIN_CREDENTIALS table
    $sql = "SELECT lc.UserID, u.UserType FROM LOGIN_CREDENTIALS lc 
            JOIN USER u ON lc.UserID = u.UserID 
            WHERE lc.Email = '$u' AND lc.Password = '$p'";
    
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) != 0) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $row['UserID'];
        $_SESSION['user_type'] = $row['UserType'];

        // Redirect based on UserType
        if ($row['UserType'] == "Student") {
            header("Location: dashboard.php");
            exit();
        } elseif ($row['UserType'] == "Mentor") {
            header("Location: mentordash.php");
            exit();
        } else {
            // Default if UserType is unexpected
            header("Location: dashboard.php");
            exit();
        }
    }
    else{
        $error_message = "Invalid email or password";
    }
}
?>
