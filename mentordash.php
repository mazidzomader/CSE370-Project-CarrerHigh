<?php
require_once("connect.php");
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Database connection
$servername = 'localhost';
$username = "root";
$password = "";
$dbname = "Project_database";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$user_details = null;
$mentor_details = null;
$students_list = [];
$expertise_areas = [];
$languages = [];

// Fetch user details for mentors only
if ($user_type === 'Mentor') {
    // Get basic user information
    $sql = "SELECT * FROM USER WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user_details = $result->fetch_assoc();
    }
    $stmt->close();

    // Get mentor details
    $sql = "SELECT * FROM MENTOR WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $mentor_details = $result->fetch_assoc();
    }
    $stmt->close();

    // Get mentor's expertise areas
    $sql = "SELECT ExpertiseArea FROM MENTOR_EXPERTISE WHERE MentorID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $expertise_areas[] = $row['ExpertiseArea'];
    }
    $stmt->close();

    // Get mentor's languages
    $sql = "SELECT Language FROM MENTOR_LANGUAGES WHERE MentorID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $languages[] = $row['Language'];
    }
    $stmt->close();

    // Get list of students mentored by this mentor using the relationship table
    $sql = "SELECT s.*, u.Name as StudentName, u.Email as StudentEmail, u.Registration_Date,
                   msr.Assignment_Date, msr.Status as RelationshipStatus
            FROM MENTOR_STUDENT_RELATIONSHIP msr
            JOIN STUDENT s ON msr.StudentID = s.UserID 
            JOIN USER u ON s.UserID = u.UserID 
            WHERE msr.MentorID = ? AND msr.Status = 'Active'
            ORDER BY u.Name";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $students_list[] = $row;
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="img/Landing_logo.png">
    <title>CareerHigh Mentor Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/mentordash.css">
</head>
<body>
    <?php if ($user_type === 'Mentor'): ?>
    <!-- Sidebar -->
    <div class="main-content">


    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo">CareerHigh</div>
            <button class="toggle-btn">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        <div class="menu-items">
            <a href="mentordash.php" class="menu-item">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="MentorProfile.php" class="menu-item">
                <i class="fas fa-user"></i>
                <span>Edit Profile</span>
            </a>
            <a href="Mystudent.php" class="menu-item">
                    <i class="fas fa-users"></i>
                <span>My Students</span>
            </a>
            <a href="research_idea.php" class="menu-item">
                <i class="fa-solid fa-lightbulb"></i>
                <span>Research Idea</span>
            </a>
            <a href="ResearchCollaboration.php" class="menu-item">
                    <i class="fa-solid fa-puzzle-piece"></i>
                <span>Collaboration</span>
            </a>
            <a href="MyCollab.php" class="menu-item">
                    <i class="fas fa-handshake"></i>
                <span>My Collaboration</span>
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

    <!-- Dashboard Content -->
    <div class="dashboard">
        <div class="welcome-banner">
            <h1>Welcome back, <?php echo htmlspecialchars($user_details['Name'] ?? 'Mentor'); ?>!</h1>
            <p>Here's an overview of your mentoring activities and students.</p>
        </div>

        <div class="mentor-dashboard-grid">
            <!-- Left Side: Mentor Details -->
            <div class="mentor-details-section">
                <!-- Personal Details Card -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-user-circle"></i>
                        <h2>Personal Details</h2>
                    </div>
                    <div class="detail-item">
                        <label>Full Name</label>
                        <p><?php echo htmlspecialchars($user_details['Name'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="detail-item">
                        <label>Email</label>
                        <p><?php echo htmlspecialchars($user_details['Email'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="detail-item">
                        <label>Registration Date</label>
                        <p><?php echo htmlspecialchars($user_details['Registration_Date'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="detail-item">
                        <label>Status</label>
                        <p><span class="status-badge status-<?php echo strtolower($user_details['Status'] ?? 'active'); ?>">
                            <?php echo htmlspecialchars($user_details['Status'] ?? 'Active'); ?>
                        </span></p>
                    </div>
                </div>

                <!-- Mentor Professional Details Card -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <h2>Professional Details</h2>
                    </div>
                    <div class="detail-item">
                        <label>Rating</label>
                        <p><?php echo $mentor_details['Rating'] ? htmlspecialchars($mentor_details['Rating']) . '/5.0 ⭐' : 'N/A'; ?></p>
                    </div>
                    <div class="detail-item">
                        <label>Remuneration</label>
                        <p><?php echo $mentor_details['Remuneration'] ? "$" . htmlspecialchars($mentor_details['Remuneration']) : 'N/A'; ?></p>
                    </div>
                    <div class="detail-item">
                        <label>Availability</label>
                        <p><?php echo htmlspecialchars($mentor_details['Availability_Schedule'] ?? 'N/A'); ?></p>
                    </div>
                    <?php if (!empty($expertise_areas)): ?>
                    <div class="detail-item">
                        <label>Expertise Areas</label>
                        <div class="expertise-tags">
                            <?php foreach ($expertise_areas as $expertise): ?>
                                <span class="expertise-tag"><?php echo htmlspecialchars($expertise); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($languages)): ?>
                    <div class="detail-item">
                        <label>Languages</label>
                        <div class="languages-list">
                            <?php foreach ($languages as $language): ?>
                                <span class="language-item"><?php echo htmlspecialchars($language); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Statistics Card -->
                <div class="stats-card">
                    <div class="stats-number"><?php echo count($students_list); ?></div>
                    <div class="stats-label">Active Students</div>
                </div>
            </div>

            <!-- Right Side: Students List -->
            <div class="students-section">
                <div class="students-list">
                    <div class="card-header">
                        <i class="fas fa-users"></i>
                        <h2>My Students (<?php echo count($students_list); ?>)</h2>
                    </div>
                    
                    <?php if (empty($students_list)): ?>
                        <div class="no-students">
                            <i class="fas fa-user-graduate"></i>
                            <h3>No Active Students</h3>
                            <p>You don't have any active students assigned to mentor at the moment.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($students_list as $student): ?>
                            <div class="student-item">
                                <div class="student-info">
                                    <h4><?php echo htmlspecialchars($student['StudentName']); ?></h4>
                                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($student['StudentEmail']); ?></p>
                                    <p><i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars($student['Degree_Programme'] ?? 'N/A'); ?></p>
                                    <p><i class="fas fa-calendar"></i> Mentoring since: <?php echo htmlspecialchars($student['Assignment_Date']); ?></p>
                                </div>
                                <!-- <button class="view-btn" onclick="viewStudentDetails(<?php echo htmlspecialchars(json_encode($student)); ?>)">
                                    <i class="fas fa-eye"></i>
                                    View Details
                                </button> -->
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Calendar -->
        <div class="calendar-container">
                <div class="calendar-header">
                    <h2 id="calTitle">June 2025</h2>
                    <div class="month-nav">
                        <button id="prev"><i class="fas fa-chevron-left"></i></button>
                        <button id="next"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
                <div id="calendar">
                    <!-- Calendar will be rendered by JavaScript -->
                </div>
            </div>

        <!-- Footer -->
        <footer>
            <p>© 2025 CareerHigh. All rights reserved.</p>
        </footer>
    </div>

    <!-- Student Details Modal -->
    <div id="studentModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div class="modal-header">
                <h2><i class="fas fa-user-graduate"></i> Student Details</h2>
            </div>
            <div id="studentDetailsContent">
                <!-- Student details will be populated by JavaScript -->
            </div>
        </div>
    </div>

    <script>
        // Sidebar toggle functionality
        const toggleBtn = document.querySelector('.toggle-btn');
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');

        if (toggleBtn && sidebar && mainContent) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
            });
        }

        // Notification functionality
        const notificationBtn = document.querySelector('.notification-btn');
        if (notificationBtn) {
            notificationBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                // Add notification functionality here
            });
        }

        // Calendar functionality - FIXED VERSION
        document.addEventListener('DOMContentLoaded', function() {
            const calRoot = document.getElementById('calendar');
            const title = document.getElementById('calTitle');
            const prevBtn = document.getElementById('prev');
            const nextBtn = document.getElementById('next');

            console.log('Calendar elements:', { calRoot, title, prevBtn, nextBtn }); // Debug log

            if (!calRoot || !title || !prevBtn || !nextBtn) {
                console.error('Calendar elements not found:', {
                    calRoot: !!calRoot,
                    title: !!title,
                    prevBtn: !!prevBtn,
                    nextBtn: !!nextBtn
                });
                return;
            }

            let dt = new Date();
            let year = dt.getFullYear();
            let month = dt.getMonth();

            function renderCalendar(y, m) {
                console.log('Rendering calendar for:', y, m); // Debug log
                
                calRoot.innerHTML = '';
                const firstDay = new Date(y, m, 1).getDay();
                const daysInMonth = new Date(y, m + 1, 0).getDate();

                // Update title
                const monthNames = ["January", "February", "March", "April", "May", "June",
                    "July", "August", "September", "October", "November", "December"];
                title.textContent = `${monthNames[m]} ${y}`;

                // Create weekday headers
                const weekdays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                const wkRow = document.createElement('div');
                wkRow.className = 'wk-row';
                weekdays.forEach(w => {
                    const el = document.createElement('div');
                    el.className = 'wkcell';
                    el.textContent = w;
                    wkRow.appendChild(el);
                });
                calRoot.appendChild(wkRow);

                // Create calendar grid
                const grid = document.createElement('div');
                grid.className = 'cal-grid';

                // Add empty cells for days before the first day of month
                let startOffset = firstDay === 0 ? 6 : firstDay - 1;
                for (let i = 0; i < startOffset; i++) {
                    const emptyCell = document.createElement('div');
                    emptyCell.className = 'cal-cell other-month';
                    const prevMonth = new Date(y, m, 0);
                    const prevMonthDays = prevMonth.getDate();
                    emptyCell.textContent = prevMonthDays - (startOffset - i - 1);
                    grid.appendChild(emptyCell);
                }

                // Add current month days
                const today = new Date();
                for (let i = 1; i <= daysInMonth; i++) {
                    const cell = document.createElement('div');
                    cell.className = 'cal-cell';
                    cell.textContent = i;

                    // Highlight today
                    if (i === today.getDate() && m === today.getMonth() && y === today.getFullYear()) {
                        cell.classList.add('today');
                    }

                    grid.appendChild(cell);
                }

                // Add empty cells for days after the last day of month
                const totalCells = 42;
                const remainingCells = totalCells - (startOffset + daysInMonth);
                for (let i = 1; i <= remainingCells; i++) {
                    const emptyCell = document.createElement('div');
                    emptyCell.className = 'cal-cell other-month';
                    emptyCell.textContent = i;
                    grid.appendChild(emptyCell);
                }

                calRoot.appendChild(grid);
            }

            // Event listeners for navigation
            prevBtn.addEventListener('click', () => {
                console.log('Previous month clicked'); // Debug log
                month--;
                if (month < 0) {
                    month = 11;
                    year--;
                }
                renderCalendar(year, month);
            });

            nextBtn.addEventListener('click', () => {
                console.log('Next month clicked'); // Debug log
                month++;
                if (month > 11) {
                    month = 0;
                    year++;
                }
                renderCalendar(year, month);
            });

            // Initial render
            console.log('Initial calendar render'); // Debug log
            renderCalendar(year, month);
        });
    </script>
    
    <?php else: ?>
        <div style="text-align: center; padding: 50px;">
            <h2>Access Denied</h2>
            <p>This dashboard is only accessible to mentors.</p>
            <a href="dashboard.php" style="color: #7e57c2; text-decoration: none;">Return to Dashboard</a>
        </div>
    <?php endif; ?>
</body>
</html>