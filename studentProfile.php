<?php
require_once("connect.php");
// session_start();

// Ensure student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Student') {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['user_id'];
$msg = "";

// ---------- Handle Form Submission ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $degreeProgramme = $_POST['Degree_Programme'] ?? '';
    $mentorID = $_POST['MentorID'] ?? null;
    
    // Convert empty string to null for MentorID
    if (empty($mentorID)) {
        $mentorID = null;
    }
    
    // Determine No_of_Mentor based on MentorID
    $noOfMentor = ($mentorID !== null) ? 1 : 0;

    // Check if student profile exists
    $check = $conn->prepare("SELECT UserID FROM student WHERE UserID=?");
    $check->bind_param("i", $userID);
    $check->execute();
    $res = $check->get_result();
    $studentExists = $res->num_rows > 0;
    $check->close();

    if ($studentExists) {
        $update = $conn->prepare("UPDATE student 
            SET Degree_Programme=?, MentorID=?, No_of_Mentor=? 
            WHERE UserID=?");
        $update->bind_param("siii", $degreeProgramme, $mentorID, $noOfMentor, $userID);
        $update->execute();
        $update->close();
    } else {
        $insert = $conn->prepare("INSERT INTO student (UserID, Degree_Programme, MentorID, No_of_Mentor) 
            VALUES (?, ?, ?, ?)");
        $insert->bind_param("isii", $userID, $degreeProgramme, $mentorID, $noOfMentor);
        $insert->execute();
        $insert->close();
    }

    // Redirect to dashboard after save
    header("Location: dashboard.php");
    exit();
}

// ---------- Fetch Current Student Data ----------
$student = [
    "Degree_Programme" => "",
    "MentorID" => "",
    "No_of_Mentor" => 0
];

$stmt = $conn->prepare("SELECT Degree_Programme, MentorID, No_of_Mentor FROM student WHERE UserID=?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();
}
$stmt->close();

// Fetch available mentors for dropdown
$mentors = [];
$mentorStmt = $conn->prepare("SELECT u.UserID, u.Name FROM user u WHERE u.UserType = 'Mentor' AND u.Status = 'Active'");
$mentorStmt->execute();
$mentorResult = $mentorStmt->get_result();
while ($row = $mentorResult->fetch_assoc()) {
    $mentors[] = $row;
}
$mentorStmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Profile</title>
    <link rel="stylesheet" href="css/studentProfile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

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
<div class="container">
    <h2>Student Profile Editing</h2>

    <form method="post" class="student-form">
        <label>Degree Programme</label>
        <input type="text" name="Degree_Programme" value="<?php echo htmlspecialchars($student['Degree_Programme']); ?>" placeholder="Enter your degree programme" required>

        <label>Mentor</label>
        <select name="MentorID">
            <option value="">Select a mentor (optional)</option>
            <?php foreach ($mentors as $mentor): ?>
                <option value="<?php echo $mentor['UserID']; ?>" 
                    <?php echo ($student['MentorID'] == $mentor['UserID']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($mentor['Name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div class="info-box">
            <p><strong>Current Status:</strong> 
                <?php if ($student['No_of_Mentor'] == 1): ?>
                    <span class="status-assigned">You have a mentor assigned</span>
                <?php else: ?>
                    <span class="status-unassigned">No mentor assigned</span>
                <?php endif; ?>
            </p>
        </div>

        <button type="submit" id="save-btn" class="btn-primary">Save Profile</button>
    </form>
</div>
<script>
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