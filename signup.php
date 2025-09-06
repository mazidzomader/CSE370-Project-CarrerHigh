<?php
// Database connection
$servername = 'localhost';
$username = "root";
$password = "";
$dbname = "Project_database";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$successMessage = "";
$errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $userType = trim($_POST['userType']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);

    // Validation
    if (empty($name) || empty($email) || empty($userType) || empty($password) || empty($confirmPassword)) {
        $errorMessage = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Invalid email format.";
    } elseif (!in_array($userType, ['Student', 'Mentor'])) {
        $errorMessage = "Please select a valid role.";
    } elseif (strlen($password) < 6) {
        $errorMessage = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirmPassword) {
        $errorMessage = "Passwords do not match.";
    } else {
        // Check if email already exists in USER table
        $checkEmailSql = "SELECT UserID FROM USER WHERE Email = ?";
        $checkStmt = mysqli_prepare($conn, $checkEmailSql);
        
        if ($checkStmt) {
            mysqli_stmt_bind_param($checkStmt, "s", $email);
            mysqli_stmt_execute($checkStmt);
            $result = mysqli_stmt_get_result($checkStmt);
            
            if (mysqli_num_rows($result) > 0) {
                $errorMessage = "An account with this email already exists.";
                mysqli_stmt_close($checkStmt);
            } else {
                mysqli_stmt_close($checkStmt);
                
                // Start transaction
                mysqli_begin_transaction($conn);
                
                try {
                    // Insert into USER table
                    $userSql = "INSERT INTO USER (Name, Email, UserType, Registration_Date, Status) VALUES (?, ?, ?, CURDATE(), 'Active')";
                    $userStmt = mysqli_prepare($conn, $userSql);
                    
                    if (!$userStmt) {
                        throw new Exception("Failed to prepare user insert statement");
                    }
                    
                    mysqli_stmt_bind_param($userStmt, "sss", $name, $email, $userType);
                    
                    if (!mysqli_stmt_execute($userStmt)) {
                        throw new Exception("Failed to insert user");
                    }
                    
                    $userId = mysqli_insert_id($conn);
                    mysqli_stmt_close($userStmt);
                    
                    // Insert into LOGIN_CREDENTIALS table
                    $credentialsSql = "INSERT INTO LOGIN_CREDENTIALS (UserID, Email, Password) VALUES (?, ?, ?)";
                    $credentialsStmt = mysqli_prepare($conn, $credentialsSql);
                    
                    if (!$credentialsStmt) {
                        throw new Exception("Failed to prepare credentials insert statement");
                    }
                    
                    mysqli_stmt_bind_param($credentialsStmt, "iss", $userId, $email, $password);
                    
                    if (!mysqli_stmt_execute($credentialsStmt)) {
                        throw new Exception("Failed to insert login credentials");
                    }
                    
                    mysqli_stmt_close($credentialsStmt);
                    
                    // Insert into appropriate subclass table
                    if ($userType === 'Student') {
                        $studentSql = "INSERT INTO STUDENT (UserID, Degree_Programme, MentorID, No_of_Mentor) VALUES (?, '', NULL, 0)";
                        $studentStmt = mysqli_prepare($conn, $studentSql);
                        
                        if (!$studentStmt) {
                            throw new Exception("Failed to prepare student insert statement");
                        }
                        
                        mysqli_stmt_bind_param($studentStmt, "i", $userId);
                        
                        if (!mysqli_stmt_execute($studentStmt)) {
                            throw new Exception("Failed to insert student record");
                        }
                        
                        mysqli_stmt_close($studentStmt);
                        
                    } elseif ($userType === 'Mentor') {
                        $mentorSql = "INSERT INTO MENTOR (UserID, Availability_Schedule, Remuneration, Rating) VALUES (?, '', 0.00, 0.00)";
                        $mentorStmt = mysqli_prepare($conn, $mentorSql);
                        
                        if (!$mentorStmt) {
                            throw new Exception("Failed to prepare mentor insert statement");
                        }
                        
                        mysqli_stmt_bind_param($mentorStmt, "i", $userId);
                        
                        if (!mysqli_stmt_execute($mentorStmt)) {
                            throw new Exception("Failed to insert mentor record");
                        }
                        
                        mysqli_stmt_close($mentorStmt);
                    }
                    
                    // Commit transaction
                    mysqli_commit($conn);
                    
                    // Redirect to login page
                    header("Location: login.php");
                    exit();
                    
                } catch (Exception $e) {
                    // Rollback transaction on error
                    mysqli_rollback($conn);
                    $errorMessage = "Registration failed. Please try again.";
                    error_log("Registration error: " . $e->getMessage());
                }
            }
        } else {
            $errorMessage = "Database error. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="img/Landing_logo.png">
    <title>Sign Up - Create Account</title>
    <link rel="stylesheet" href="css/signin.css">
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
        
        <?php if (!empty($errorMessage)): ?>
            <div class="error-message" style="background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 5px; border: 1px solid #f5c6cb;">
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>
        
        <form class="login-form" method="POST" action="">
            <div class="form-group">
                <input type="text" name="name" placeholder="Full name" 
                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <input type="email" name="email" placeholder="Email address" 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <select name="userType" required>
                    <option value="" disabled <?php echo !isset($_POST['userType']) ? 'selected' : ''; ?>>Select Role</option>
                    <option value="Student" <?php echo (isset($_POST['userType']) && $_POST['userType'] === 'Student') ? 'selected' : ''; ?>>Student</option>
                    <option value="Mentor" <?php echo (isset($_POST['userType']) && $_POST['userType'] === 'Mentor') ? 'selected' : ''; ?>>Mentor</option>
                </select>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="form-group">
                <input type="password" name="confirmPassword" placeholder="Confirm password" required>
            </div>
            <div class="login-options">
                <label class="remember-me">
                    <input type="checkbox" required> I agree to the <a href="#" class="terms-link"> Terms & Conditions</a>
                </label>
            </div>
            <button type="submit" class="login-btn" style="margin-bottom: 10px;">Create Account</button>
        </form>
        
        <div class="signup-link">
            Already have an account? <a href="login.php">Sign in</a>
        </div>
    </div>
</body>
</html>