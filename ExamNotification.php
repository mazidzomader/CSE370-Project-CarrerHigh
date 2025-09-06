<?php
// session_start();
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

// Get user ID from session (from login)
if (!isset($_SESSION['user_id'])) {
    die("Please login first.");
}
$user_id = $_SESSION['user_id'];

// Simple query to get exams within 7 days
$sql = "SELECT * FROM exams 
        WHERE user_id = $user_id 
        AND date >= CURDATE() 
        AND date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) 
        AND result IS NULL
        ORDER BY date ASC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upcoming Exams</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/Exam Notification.css">
</head>
<body>
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
    <div class="notification-container">
        <div class="notification-header">
            <div class="notification-title">📚 Upcoming Exams</div>
            <div class="notification-subtitle">Next 7 days</div>
            <?php if ($result->num_rows > 0): ?>
                <div class="notification-badge"><?php echo $result->num_rows; ?> exam<?php echo $result->num_rows > 1 ? 's' : ''; ?></div>
            <?php endif; ?>
        </div>

        <div class="notification-content">
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    // Calculate days remaining - simple method
                    $exam_date = date('Y-m-d', strtotime($row["date"]));

                    echo '<script>
                    var examDate = new Date("' . $exam_date . '");
                    var today = new Date();
                    today.setHours(0,0,0,0);
                    examDate.setHours(0,0,0,0);
                    var diffTime = examDate - today;
                    var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); // Subtract 1 to exclude exam day

                    var daysText, daysClass;
                    if (diffDays == 0) {
                        daysText = "Today";
                        daysClass = "today";
                    } else if (diffDays == 1) {
                        daysText = "Tomorrow";
                        daysClass = "tomorrow";
                    } else if (diffDays < 0) {
                        daysText = "Past";
                        daysClass = "past";
                    } else {
                        daysText = diffDays + " days";
                        daysClass = "upcoming";
                    }
                    </script>';
                    
                    echo '<div class="exam-item">';
                    echo '<div class="exam-info">';
                    echo '<div class="exam-name">' . htmlspecialchars($row["name"]) . '</div>';
                    echo '<div class="exam-date">📅 ' . date('l, F j, Y', strtotime($row["date"])) . '</div>';
                    echo '<div class="exam-status">Status: ' . $row["status"] . '</div>';
                    echo '</div>';
                    echo '<div class="days-remaining" id="days-' . $row["id"] . '">Remaining Days: <span class="days-value"></span></div>';
                    echo '<script>
                    document.getElementById("days-' . $row["id"] . '").querySelector(".days-value").textContent = daysText;
                    document.getElementById("days-' . $row["id"] . '").className += " " + daysClass;
                    </script>';
                    echo '</div>';
                }
            } else {
                echo '<div class="no-exams">';
                echo '<div class="no-exams-icon">🎉</div>';
                echo '<div class="no-exams-text">No upcoming exams!</div>';
                echo '<div class="no-exams-subtext">You\'re all clear for the next 7 days.</div>';
                echo '</div>';
            }
            ?>
        </div>

        <div class="notification-footer">
            Last updated: <span id="last-updated"></span>
            <script>
            document.getElementById('last-updated').textContent = new Date().toLocaleString('en-US', {
                month: 'short',
                day: 'numeric', 
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
            </script>
        </div>
    </div>
</body>
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
</html>

<?php
$conn->close();
?>