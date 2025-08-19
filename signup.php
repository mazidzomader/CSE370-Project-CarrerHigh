<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="img/Landing_logo.png">
    <title>Sign Up - Create Account</title>
    <link rel="stylesheet" href="css/signup.css">
</head>
<body>
    <div class="bg-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="login-container">
        <div class="login-header">
            <h1>Create Account</h1>
            <p>Join us today and get started</p>
        </div>

        <form class="login-form">
            <div class="form-group">
                <input type="text" placeholder="Full name" required>
            </div>

            <div class="form-group">
                <input type="email" placeholder="Email address" required>
            </div>

            <div class="form-group">
                <input type="password" placeholder="Password" required>
            </div>

            <div class="form-group">
                <input type="password" placeholder="Confirm password" required>
            </div>

            <div class="login-options">
                <label class="remember-me">
                    <input type="checkbox" required> I agree to the <a href="#" class="terms-link"> Terms & Conditions</a>
                </label>
            </div>

            <button type="submit" class="login-btn" style="margin-bottom: 10px;" >Create Account</button>
        </form>

        <!-- <div class="divider">
            <span>or sign up with</span>
        </div>

        <div class="social-login">
            <a href="#" class="social-btn">Google</a>
            <a href="#" class="social-btn">GitHub</a>
        </div> -->

        <div class="signup-link">
            Already have an account? <a href="login.php">Sign in</a>
        </div>
    </div>
</body>
</html>