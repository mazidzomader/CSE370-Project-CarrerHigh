<?php
require_once('DBconnect.php');

if (isset($_POST['user']) && isset($_POST['pass'])){
    $u = $_POST['user'];
    $p = $_POST['pass'];

    $sql = "SELECT * FROM users WHERE username = 
    '$u' AND password = '$p'";

    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) != 0){
        // echo ($p);
        header("Location: dashboard.php");
    }
    else{
        // echo "Username or Password is Wrong";
        header("Location: login.php");
    }
} 
?>

