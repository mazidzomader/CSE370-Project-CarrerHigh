<?php
require_once("connect.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Welcome Back</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="icon" type="image/png" href="img/Landing_logo.png">
</head>
<body>
    <div class="bg-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="login-container">
        <div class="logo-section">
            <img src="img/logo.png" alt="Company Logo" class="logo">
        </div>
        <div class="login-header">
            <h1>Welcome Back</h1>
            <p>Please sign in to your account</p>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="error-message" style="color: #dc3545; text-align: center; margin-bottom: 20px; padding: 10px; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px;">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form class="login-form" method="POST">
            <div class="form-group">
                <input type="email" name="user" placeholder="Email address" required>
            </div>

            <div class="form-group">
                <input type="password" name="pass" placeholder="Password" required>
            </div>

            <div class="login-options">
                <label class="remember-me">
                    <input type="checkbox"> Remember me
                </label>
            </div>

            <button type="submit" class="login-btn" style="margin-bottom:20px;">Log in</button>
        </form>

        <div class="social-login">
            <a href="index.php" class="social-btn">Home</a>
        </div> 

        <div class="signup-link">
            Don't have an account? <a href="signup.php">Sign up</a>
        </div>
    </div>
</body>
</html>