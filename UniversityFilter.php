<?php
require_once("connect.php");
$servername = 'localhost';
$username = "root";
$password = "";
$dbname = "Project_database";

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


$scholarships = [];
$hasResults = false;
$errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['major']) && isset($_POST['funding']) && isset($_POST['country'])) {
    $major1 = trim($_POST['major']);
    $funding1 = trim($_POST['funding']);
    $country1 = trim($_POST['country']);

    // Build dynamic WHERE conditions based on what user provided
    $conditions = [];
    $params = [];
    $types = "";

    // Always include funding condition (even if 0)
    $conditions[] = "Funding >= ?";
    $params[] = $funding1;
    $types .= "d";

    // Always include date condition
    $conditions[] = "Last_Date > CURDATE()";

    // Only add department condition if not empty
    if (!empty($major1)) {
        $conditions[] = "Department LIKE ?";
        $params[] = "%$major1%";
        $types .= "s";
    }

    // Only add country condition if not empty
    if (!empty($country1)) {
        $conditions[] = "Country LIKE ?";
        $params[] = "%$country1%";
        $types .= "s";
    }

    // Build the SQL query dynamically
    $sql = "SELECT * FROM University WHERE " . implode(" AND ", $conditions);
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        // Only bind parameters if we have any
        if (!empty($params)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $scholarships[] = $row;
            }
            $hasResults = true;
        } else {
            $errorMessage = "No scholarships found matching your criteria.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $errorMessage = "Database query error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scholarship Filter | CareerHigh</title>
    <link rel="icon" type="image/png" href="img/Landing_logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/University Filteration.css">
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
                <h1>Find Your Perfect Scholarship</h1>
                <p class="subtitle">Filter opportunities based on your preferences</p>
            </header>
            
            <form class="filter-form" id="filterForm" method="POST" action="UniversityFilter.php">
                <div class="form-row">
                    <div class="form-group">
                        <label for="funding"><i class="fas fa-money-bill-wave"></i> Minimum Funding Amount</label>
                        <input type="number" id="funding" placeholder="e.g. 5000, 10000 etc." name="funding" 
                               value="<?php echo isset($_POST['funding']) ? htmlspecialchars($_POST['funding']) : '0'; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="country"><i class="fas fa-globe"></i> Country (Optional)</label>
                        <input type="text" id="country" placeholder="e.g. USA, UK, Canada (leave empty for all)" name="country" 
                               value="<?php echo isset($_POST['country']) ? htmlspecialchars($_POST['country']) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="major"><i class="fas fa-graduation-cap"></i> Major/Department (Optional)</label>
                        <input type="text" id="major" placeholder="e.g. Computer Science, Engineering (leave empty for all)" name="major" 
                               value="<?php echo isset($_POST['major']) ? htmlspecialchars($_POST['major']) : ''; ?>">
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">Apply Filters</button>
            </form>
            
            <!-- Results Section -->
            <?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
            <div class="results-section visible" id="resultsSection">
                <div class="results-header">
                    <h2 class="results-title">Matching Scholarships</h2>
                    <div class="results-count">Showing <span id="resultsCount"><?php echo count($scholarships); ?></span> results</div>
                </div>
                
                <div class="results-grid" id="resultsGrid">
                    <?php if ($hasResults): ?>
                        <?php foreach ($scholarships as $scholarship): ?>
                            <div class="result-card">
                                <h3 class="scholarship-name"><?php echo htmlspecialchars($scholarship['Name']); ?></h3>
                                <div class="scholarship-details">
                                    <div class="scholarship-detail">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <span>$<?php echo htmlspecialchars(number_format($scholarship['Funding'], 2)); ?></span>
                                    </div>
                                    <div class="scholarship-detail">
                                        <i class="fas fa-globe"></i>
                                        <span><?php echo htmlspecialchars($scholarship['Country']); ?></span>
                                    </div>
                                    <div class="scholarship-detail">
                                        <i class="fas fa-graduation-cap"></i>
                                        <span><?php echo htmlspecialchars($scholarship['Department']); ?></span>
                                    </div>
                                    <div class="scholarship-detail">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>Apply by: <?php echo htmlspecialchars(date('F j, Y', strtotime($scholarship['Last_Date']))); ?></span>
                                    </div>
                                    <div class="scholarship-detail">
                                        <i class="fas fa-envelope"></i>
                                        <span><?php echo htmlspecialchars($scholarship['admission_Email']); ?></span>
                                    </div>
                                </div>
                                <a href="mailto:<?php echo htmlspecialchars($scholarship['admission_Email']); ?>" class="apply-btn">Contact Admissions</a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-results">
                            <i class="fas fa-search"></i>
                            <h3>No Results Found</h3>
                            <p><?php echo htmlspecialchars($errorMessage); ?></p>
                            <p>Try adjusting your search criteria:</p>
                            <ul>
                                <li>Lower the minimum funding amount</li>
                                <li>Try broader search terms for major or country</li>
                                <li>Extend the deadline date range</li>
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