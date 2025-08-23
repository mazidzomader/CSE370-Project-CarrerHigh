<?php
require_once("UniversityDB.php"); //change later

$mentors = [];
$hasResults = false;
$errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['expertise'])) {
    $expertise = trim($_POST['expertise']);
    $language = isset($_POST['language']) ? trim($_POST['language']) : '';
    $remuneration = isset($_POST['remuneration']) ? trim($_POST['remuneration']) : '';

    // Check if expertise is provided (required field)
    if (empty($expertise)) {
        $errorMessage = "Expertise field is required.";
    } else {
        // Build query with subquery to get all languages for each mentor
        $sql = "SELECT m.ID, m.Name, m.Email, m.Remuneration, m.Timing_Schedule, m.Rating,
                (SELECT GROUP_CONCAT(DISTINCT ml2.Language SEPARATOR ', ') 
                 FROM Mentor_Language ml2 
                 WHERE ml2.MentorID = m.ID) as Languages
                FROM Mentor m 
                INNER JOIN Mentor_Expertise me ON m.ID = me.MentorID 
                WHERE me.Expertise LIKE ?";
        $params = ["%$expertise%"];
        $types = "s";

        // Add language condition if provided
        if (!empty($language)) {
            $sql .= " AND m.ID IN (SELECT ml.MentorID FROM Mentor_Language ml WHERE ml.Language LIKE ?)";
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
        $sql .= " GROUP BY m.ID";

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
    <title>Scholarship Filter | CareerHigh</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/MentorFilter.css">
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
            <a href="#" class="menu-item">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
            <a href="UniversityFilter.php" class="menu-item">
                <i class="fa fa-university" aria-hidden="true"></i>
                <span>Search University</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-book"></i>
                <span>Exam</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-chart-line"></i>
                <span>Progress</span>
            </a>
            <a href="MentorFilter.php" class="menu-item">
                <i class="fa-solid fa-chalkboard-user"></i>
                <span>Mentors Available</span>
            </a>
        </div>
        <div class="sidebar-footer">
            <span>CareerHigh v1.0</span>
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
            <?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
            <div class="results-section visible" id="resultsSection">
                <div class="results-header">
                    <h2 class="results-title">Matching Mentors</h2>
                    <div class="results-count">Showing <span id="resultsCount"><?php echo count($mentors); ?></span> results</div>
                </div>
                
                <div class="results-grid" id="resultsGrid">
                    <?php if ($hasResults): ?>
                        <?php foreach ($mentors as $mentor): ?>
                            <div class="result-card">
                                <!-- <button class="add-mentor-btn" onclick="addMentor(<?php echo $mentor['ID']; ?>)">+</button> -->
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
                                <a href="#" class="apply-btn">Add Mentor</a>

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
            // You can customize this function based on your needs
            // For example, send AJAX request to save to database
            
            const confirmed = confirm('Add this mentor to your list?');
            if (confirmed) {
                // Here you can add AJAX call to save to database
                // Example:
                // fetch('addMentor.php', {
                //     method: 'POST',
                //     headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                //     body: 'mentorId=' + mentorId
                // });
                
                alert('Mentor added successfully!');
                
                // Optional: Change button appearance to show it's been added
                const button = event.target;
                button.innerHTML = '✓';
                button.style.backgroundColor = '#28a745';
                button.disabled = true;
            }
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