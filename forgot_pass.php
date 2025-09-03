<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Reset Your Password</title>
    <link rel="stylesheet" href="css/forgot_pass.css">
</head>
<body>
    <div class="bg-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="login-container">
        <div class="logo-section">
            <img src="img/logo.png" alt="Company Logo" class="logo">
        </div>
        
        <div class="login-header">
            <h1>Reset Password</h1>
            <p>Enter your email and we'll send you a link to reset your password</p>
        </div>

        <form class="login-form">
            <div class="form-group">
                <input type="email" placeholder="Enter your email address" required>
            </div>

            <button type="submit" class="send-btn">
                <div class="svg-wrapper-1">
                    <div class="svg-wrapper">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            width="24"
                            height="24"
                        >
                            <path fill="none" d="M0 0h24v24H0z"></path>
                            <path
                                fill="currentColor"
                                d="M1.946 9.315c-.522-.174-.527-.455.01-.634l19.087-6.362c.529-.176.832.12.684.638l-5.454 19.086c-.15.529-.455.547-.679.045L12 14l6-8-8 6-8.054-2.685z"
                            ></path>
                        </svg>
                    </div>
                </div>
                <span>Send</span>
            </button>
        </form>

        <div class="back-to-login">
            <a href="login.php" class="back-link">
                <span class="back-arrow">←</span>
                Back to Log in
            </a>
        </div>

        <div class="help-section">
            <p class="help-text">
                Remember your password? <a href="login.php" class="signin-link">Sign in</a>
            </p>
            <p class="help-text">
                Don't have an account? <a href="signup.php" class="signup-link-alt">Sign up</a>
            </p>
        </div>
    </div>
</body>
</html>