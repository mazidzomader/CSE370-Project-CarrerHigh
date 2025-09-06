<?php
require_once("connect.php");
// Database connection
$servername = 'localhost';
$username = "root";
$password = "";
$dbname = "Project_database";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// session_start();

$mentors = [];
$hasResults = false;
$errorMessage = "";

// Handle Add Mentor AJAX request
if (isset($_POST['action']) && $_POST['action'] === 'add_mentor' && isset($_POST['mentor_id'])) {
    $mentorId = intval($_POST['mentor_id']);
    $studentId = $_SESSION['user_id'] ?? null;
    
    header('Content-Type: application/json');
    
    if (!$studentId) {
        echo json_encode(['success' => false, 'message' => 'Please log in to add a mentor.']);
        exit();
    }
    
    // Check if student already has a mentor
    $checkSql = "SELECT MentorID FROM STUDENT WHERE UserID = ?";
    $checkStmt = mysqli_prepare($conn, $checkSql);
    mysqli_stmt_bind_param($checkStmt, "i", $studentId);
    mysqli_stmt_execute($checkStmt);
    $result = mysqli_stmt_get_result($checkStmt);
    $studentData = mysqli_fetch_assoc($result);
    mysqli_stmt_close($checkStmt);
    
    if ($studentData && $studentData['MentorID'] !== null) {
        echo json_encode(['success' => false, 'message' => 'Mentor already assigned']);
        exit();
    }
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Update STUDENT table with mentor assignment
        $updateStudentSql = "UPDATE STUDENT SET MentorID = ?, No_of_Mentor = 1 WHERE UserID = ?";
        $updateStmt = mysqli_prepare($conn, $updateStudentSql);
        mysqli_stmt_bind_param($updateStmt, "ii", $mentorId, $studentId);
        
        if (!mysqli_stmt_execute($updateStmt)) {
            throw new Exception("Failed to update student record");
        }
        mysqli_stmt_close($updateStmt);
        
        // Insert into MENTOR_STUDENT_RELATIONSHIP table
        $relationshipSql = "INSERT INTO MENTOR_STUDENT_RELATIONSHIP (MentorID, StudentID, Assignment_Date, Status) VALUES (?, ?, CURDATE(), 'Active')";
        $relationshipStmt = mysqli_prepare($conn, $relationshipSql);
        mysqli_stmt_bind_param($relationshipStmt, "ii", $mentorId, $studentId);
        
        if (!mysqli_stmt_execute($relationshipStmt)) {
            throw new Exception("Failed to create mentor-student relationship");
        }
        mysqli_stmt_close($relationshipStmt);
        
        mysqli_commit($conn);
        echo json_encode(['success' => true, 'message' => 'Mentor added successfully!']);
        exit();
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo json_encode(['success' => false, 'message' => 'Failed to add mentor. Please try again.']);
        exit();
    }
}

// Handle mentor search
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['expertise'])) {
    $expertise = trim($_POST['expertise']);
    $language = isset($_POST['language']) ? trim($_POST['language']) : '';
    $remuneration = isset($_POST['remuneration']) ? trim($_POST['remuneration']) : '';

    // Check if expertise is provided (required field)
    if (empty($expertise)) {
        $errorMessage = "Expertise field is required.";
    } else {
        // Build query with subquery to get all languages for each mentor
        $sql = "SELECT u.UserID, u.Name, u.Email, m.Remuneration, m.Availability_Schedule as Timing_Schedule, m.Rating,
                (SELECT GROUP_CONCAT(DISTINCT ml.Language SEPARATOR ', ') 
                 FROM MENTOR_LANGUAGES ml 
                 WHERE ml.MentorID = u.UserID) as Languages
                FROM USER u 
                INNER JOIN MENTOR m ON u.UserID = m.UserID 
                INNER JOIN MENTOR_EXPERTISE me ON u.UserID = me.MentorID 
                WHERE u.UserType = 'Mentor' AND u.Status = 'Active' AND me.ExpertiseArea LIKE ?";
        $params = ["%$expertise%"];
        $types = "s";

        // Add language condition if provided
        if (!empty($language)) {
            $sql .= " AND u.UserID IN (SELECT ml.MentorID FROM MENTOR_LANGUAGES ml WHERE ml.Language LIKE ?)";
            $params[] = "%$language%";
            $types .= "s";
        }

        // Add remuneration condition if provided
        if (!empty($remuneration)) {
            $sql .= " AND m.Remuneration <= ?";
            $params[] = $remuneration;
            $types .= "d";
        }

        // Group by mentor ID to avoid duplicates
        $sql .= " GROUP BY u.UserID, u.Name, u.Email, m.Remuneration, m.Availability_Schedule, m.Rating";

        // Execute the query
        $stmt = mysqli_prepare($conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $mentors[] = $row;
                }
                $hasResults = true;
            } else {
                $errorMessage = "No mentors found matching your criteria.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $errorMessage = "Database query error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentor Filter | CareerHigh</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/Mentor Filter.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo">CareerHigh</div>
            <button class="toggle-btn">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        <div class="menu-items">
            <a href="dashboard.php" class="menu-item">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="studentProfile.php" class="menu-item">
                <i class="fas fa-user"></i>
                <span>Edit Profile</span>
            </a>
            <a href="Roadmap.php" class="menu-item">
                <i class="fa-solid fa-road"></i>
                <span>Roadmap</span>
            </a>
            <a href="Task.php" class="menu-item">
                <i class="fas fa-tasks"></i>
                <span>Tasks</span>
            </a>
            <a href="UniversityFilter.php" class="menu-item">
                <i class="fa fa-university" aria-hidden="true"></i>
                <span>Search University</span>
            </a>
            <a href="research_idea.php" class="menu-item">
                <i class="fa-solid fa-lightbulb"></i>
                <span>Research Idea</span>
            </a>
            <a href="Document.php" class="menu-item">
                <i class="fa-solid fa-passport"></i>
                <span>Documents</span>
            </a>
            <a href="ExamNotification.php" class="menu-item">
                <i class='fas fa-bell'></i>
                <span>Upcoming Exam</span>
            </a>
            <a href="ExamTracker.php" class="menu-item">
                <i class="fa-solid fa-file-pen"></i>
                <span>Exam Tracker</span>
            </a>
            <a href="Activity.php" class="menu-item">
                    <i class="bi bi-activity"></i>
                <span>Activity</span>
            </a>
            <a href="MyCollab.php" class="menu-item">
                    <i class="fas fa-handshake"></i>
                <span>My Collaboration</span>
            </a>
            <a href="ResearchCollaboration.php" class="menu-item">
                    <i class="fas fa-puzzle-piece"></i>
                <span>Project Collaboration</span>
            </a>
            <a href="MentorFilter.php" class="menu-item">
                <i class="fa-solid fa-chalkboard-user"></i>
                <span>Mentors Available</span>
            </a>
            <a href="logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
        <div class="sidebar-footer">
            <span>CareerHigh<br>v1.0</span>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <header>
                <h1>Find Your Perfect Mentor</h1>
                <p class="subtitle">Filter mentors based on your preferences</p>
            </header>
            
            <form class="filter-form" id="filterForm" method="POST" action="MentorFilter.php">
                <div class="form-row">
                    <div class="form-group">
                        <label for="expertise"><i class="fa fa-cogs" aria-hidden="true"></i>Expertise Area</label>
                        <input type="text" id="expertise" placeholder="e.g. Computer Science, Economy etc." name="expertise" 
                               value="<?php echo isset($_POST['expertise']) ? htmlspecialchars($_POST['expertise']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="language"><i class="fas fa-globe"></i> Language (Optional)</label>
                        <input type="text" id="language" placeholder="e.g. English, Bangla (leave empty for all)" name="language" 
                               value="<?php echo isset($_POST['language']) ? htmlspecialchars($_POST['language']) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="remuneration"><i class="fas fa-money-bill-wave"></i> Max Remuneration (Optional)</label>
                        <input type="number" id="remuneration" placeholder="e.g. 5000, 10000 (leave empty for all)" name="remuneration" 
                               value="<?php echo isset($_POST['remuneration']) ? htmlspecialchars($_POST['remuneration']) : ''; ?>">
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">Apply Filters</button>
            </form>
            
            <!-- Results Section -->
            <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['action'])): ?>
            <div class="results-section visible" id="resultsSection">
                <div class="results-header">
                    <h2 class="results-title">Matching Mentors</h2>
                    <div class="results-count">Showing <span id="resultsCount"><?php echo count($mentors); ?></span> results</div>
                </div>
                
                <div class="results-grid" id="resultsGrid">
                    <?php if ($hasResults): ?>
                        <?php foreach ($mentors as $mentor): ?>
                            <div class="result-card">
                                <h3 class="mentor-name"><?php echo htmlspecialchars($mentor['Name']); ?></h3>
                                <div class="mentor-details">
                                    <div class="mentor-detail">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <span>$<?php echo htmlspecialchars(number_format($mentor['Remuneration'], 2)); ?></span>
                                    </div>
                                    <div class="mentor-detail">
                                        <i class="fas fa-clock"></i>
                                        <span><?php echo htmlspecialchars($mentor['Timing_Schedule']); ?></span>
                                    </div>
                                    <div class="mentor-detail">
                                        <i class="fas fa-star"></i>
                                        <span>Rating: <?php echo htmlspecialchars($mentor['Rating']); ?>/5</span>
                                    </div>
                                    <div class="mentor-detail">
                                        <i class="fas fa-envelope"></i>
                                        <span><?php echo htmlspecialchars($mentor['Email']); ?></span>
                                    </div>
                                    <div class="mentor-detail">
                                        <i class="fas fa-language"></i>
                                        <span><?php echo htmlspecialchars($mentor['Languages'] ?? 'Not specified'); ?></span>
                                    </div>
                                </div>
                                <a href="mailto:<?php echo htmlspecialchars($mentor['Email']); ?>" class="apply-btn">Contact Mentor</a>
                                <button class="apply-btn" onclick="addMentor(<?php echo $mentor['UserID']; ?>)">Add Mentor</button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-results">
                            <i class="fas fa-search"></i>
                            <h3>No Results Found</h3>
                            <p><?php echo htmlspecialchars($errorMessage); ?></p>
                            <p>Try adjusting your search criteria:</p>
                            <ul>
                                <li>Lower the remuneration amount</li>
                                <li>Try broader search terms for expertise or language</li>
                                <li>Check if the expertise area is spelled correctly</li>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="copyright">
            © 2025 CareerHigh. All rights reserved.
        </div>
    </div>

    <script>
        function addMentor(mentorId) {
            // Send AJAX request to add mentor
            fetch('MentorFilter.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=add_mentor&mentor_id=' + mentorId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    // Change button appearance to show it's been added
                    const button = event.target;
                    button.innerHTML = '✓ Added';
                    button.style.backgroundColor = '#28a745';
                    button.disabled = true;
                } else {
                    alert(data.message);
                }
            })
            // .catch(error => {
            //     alert('Error adding mentor. Please try again.');
            //     console.error('Error:', error);
            // });
        }
        
        // Sidebar toggle functionality
        const toggleBtn = document.querySelector('.toggle-btn');
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });
        
        // Handle initial mobile state
        function handleResize() {
            if (window.innerWidth <= 600) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
            } else if (window.innerWidth <= 900) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
            }
        }
        
        // Initialize on load
        window.addEventListener('load', handleResize);
        window.addEventListener('resize', handleResize);
    </script>
</body>
</html>