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
$dbname = "demo_project";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$user_details = null;
$student_details = null;
$mentor_details = null;

// Fetch user details for students only
if ($user_type === 'Student') {
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

    // Get student details with mentor information
    $sql = "SELECT s.*, 
                   mentor.Name as MentorName, 
                   mentor.Email as MentorEmail,
                   m.Rating,
                   m.Remuneration,
                   m.Availability_Schedule
            FROM STUDENT s 
            LEFT JOIN USER mentor ON s.MentorID = mentor.UserID 
            LEFT JOIN MENTOR m ON s.MentorID = m.UserID
            WHERE s.UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $student_details = $result->fetch_assoc();
    }
    $stmt->close();
}
// ========================
// Unassign Mentor Request
// ========================
if (isset($_POST['unassign_mentor']) && isset($_POST['unassign_mentor_id']) && isset($_POST['student_id'])) {
    $mentorID = intval($_POST['unassign_mentor_id']);
    $studentID = intval($_POST['student_id']);

    // Step 1: Update STUDENT table (remove mentor)
    $updateStudent = "UPDATE STUDENT SET MentorID = NULL, No_of_Mentor = 0 WHERE UserID = ?";
    $stmt1 = $conn->prepare($updateStudent);
    $stmt1->bind_param("i", $studentID);

    if ($stmt1->execute()) {
        // Step 2: Delete from MENTOR_STUDENT_RELATIONSHIP
        $deleteRelation = "DELETE FROM MENTOR_STUDENT_RELATIONSHIP WHERE MentorID = ? AND StudentID = ?";
        $stmt2 = $conn->prepare($deleteRelation);
        $stmt2->bind_param("ii", $mentorID, $studentID);
        $stmt2->execute();
        $stmt2->close();

        echo "<script>alert('Mentor unassigned successfully'); window.location.href='dashboard.php';</script>";
    } else {
        echo "<script>alert('Error while unassigning mentor: " . $conn->error . "');</script>";
    }
    $stmt1->close();
}
$conn->close();
?>

<?php
// ========================
// Unassign Mentor Request
// ========================

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CareerHigh Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/dash board.css">
</head>
<body>
    <?php if ($user_type === 'Student'): ?>
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
        <a href="UniversityFilter.php" class="menu-item">
            <i class="fa-solid fa-road"></i>
            <span>Roadmap</span>
        </a>
        <a href="Task.php" class="menu-item">
            <i class="fa-solid fa-road"></i>
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

    <div class="main-content">

        <!-- Dashboard Content -->
    <div class="dashboard">
        <div class="welcome-banner">
            <h1>Welcome back, <?php echo htmlspecialchars($user_details['Name'] ?? 'Student'); ?>!</h1>
            <p>Here's what's happening with your career development today.</p>
        </div>

        <div class="dashboard-grid">
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
                    <label>Degree Programme</label>
                    <p><?php echo htmlspecialchars($student_details['Degree_Programme'] ?? 'N/A'); ?></p>
                </div>
                <div class="detail-item">
                    <label>Registration Date</label>
                    <p><?php echo htmlspecialchars($user_details['Registration_Date'] ?? 'N/A'); ?></p>
                </div>
            </div>
                <!-- Mentor Details Card -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <h2>Mentor Details</h2>
                    </div>
                    <div class="detail-item" style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <label>Mentor Name</label>
                            <p><?php echo htmlspecialchars($student_details['MentorName'] ?? 'No Mentor Assigned'); ?></p>
                        </div>
                        <?php if (!empty($student_details['MentorID'])): ?>
                            <form method="POST" action="dashboard.php" onsubmit="return confirm('Are you sure you want to unassign this mentor?');">
                                <input type="hidden" name="unassign_mentor_id" value="<?php echo $student_details['MentorID']; ?>">
                                <input type="hidden" name="student_id" value="<?php echo $student_details['UserID']; ?>">
                                <button type="submit" name="unassign_mentor" class="unassign-btn">Unassign</button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <div class="detail-item">
                        <label>Mentor Email</label>
                        <p><?php echo htmlspecialchars($student_details['MentorEmail'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="detail-item">
                        <label>Mentor Rating</label>
                        <p><?php echo $student_details['Rating'] ? htmlspecialchars($student_details['Rating']) . '/5.0' : 'N/A'; ?></p>
                    </div>
                    <div class="detail-item">
                        <label>Remuneration</label>
                        <p><?php echo "$" . $student_details['Remuneration']; ?></p>
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
        </div>

        <!-- Footer -->
        <footer>
            <p>© 2025 CareerHigh. All rights reserved.</p>
        </footer>
    </div>


    <script>
document.addEventListener('DOMContentLoaded', function() {
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
    const notificationBox = document.querySelector('.notification-box');
    
    if (notificationBtn && notificationBox) {
        notificationBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            notificationBox.classList.toggle('active');
        });
        
        // Close notification when clicking outside
        document.addEventListener('click', (e) => {
            if (!notificationBox.contains(e.target) && !notificationBtn.contains(e.target)) {
                notificationBox.classList.remove('active');
            }
        });
    }
    
    // Calendar functionality
    const calRoot = document.getElementById('calendar');
    const title = document.getElementById('calTitle');
    const prevBtn = document.getElementById('prev');
    const nextBtn = document.getElementById('next');

    if (calRoot && title && prevBtn && nextBtn) {
        let dt = new Date();
        let year = dt.getFullYear();
        let month = dt.getMonth();

        function renderCalendar(y, m){
            calRoot.innerHTML = '';
            const firstDay = new Date(y, m, 1).getDay();
            const daysInMonth = new Date(y, m+1, 0).getDate();

            // Update title
            const monthNames = ["January", "February", "March", "April", "May", "June",
                "July", "August", "September", "October", "November", "December"];
            title.textContent = `${monthNames[m]} ${y}`;

            // header: weekdays (Mon..Sun)
            const weekdays = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
            const wkRow = document.createElement('div');
            wkRow.className = 'wk-row';
            weekdays.forEach(w => {
                const el = document.createElement('div');
                el.className = 'wkcell';
                el.textContent = w;
                wkRow.appendChild(el);
            });
            calRoot.appendChild(wkRow);

            // grid
            const grid = document.createElement('div');
            grid.className = 'cal-grid';

            // Add empty cells for days before the first day of month
            let startOffset = firstDay === 0 ? 6 : firstDay - 1;
            for(let i = 0; i < startOffset; i++){
                const emptyCell = document.createElement('div');
                emptyCell.className = 'cal-cell other-month';
                const prevMonth = new Date(y, m, 0);
                const prevMonthDays = prevMonth.getDate();
                emptyCell.textContent = prevMonthDays - (startOffset - i - 1);
                grid.appendChild(emptyCell);
            }

            // Add current month days
            const today = new Date();
            for(let i = 1; i <= daysInMonth; i++){
                const cell = document.createElement('div');
                cell.className = 'cal-cell';
                cell.textContent = i;
                
                // Highlight today
                if(i === today.getDate() && m === today.getMonth() && y === today.getFullYear()){
                    cell.classList.add('today');
                }
                
                grid.appendChild(cell);
            }

            // Add empty cells for days after the last day of month
            const totalCells = 42;
            const remainingCells = totalCells - (startOffset + daysInMonth);
            for(let i = 1; i <= remainingCells; i++){
                const emptyCell = document.createElement('div');
                emptyCell.className = 'cal-cell other-month';
                emptyCell.textContent = i;
                grid.appendChild(emptyCell);
            }

            calRoot.appendChild(grid);
        }

        // Event listeners for navigation
        prevBtn.addEventListener('click', () => {
            month--;
            if(month < 0){
                month = 11;
                year--;
            }
            renderCalendar(year, month);
        });

        nextBtn.addEventListener('click', () => {
            month++;
            if(month > 11){
                month = 0;
                year++;
            }
            renderCalendar(year, month);
        });

        // Initial render
        renderCalendar(year, month);
    }
});
</script>
</body>
</html><?php endif; ?>

