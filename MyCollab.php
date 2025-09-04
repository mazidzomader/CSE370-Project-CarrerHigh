<?php
session_start();

// Require login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$userId = (int) $_SESSION['user_id'];

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "demo_project"; // <- change if needed

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch collaborations of user
$sql = "SELECT r.CollabID, r.ProjectName, r.Description, r.startdate, 
               r.CurrentPeople, r.MaxPeople
        FROM RESEARCH_COLLABORATION r
        INNER JOIN COLLABORATION_PARTICIPANTS p 
        ON r.CollabID = p.CollabID
        WHERE p.UserID = ?
        ORDER BY r.startdate ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Collaborations</title>
    <link rel="stylesheet" href="css/ResearchCollaboration.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
        <a href="UniversityFilter.php" class="menu-item">
            <i class="fa-solid fa-road"></i>
            <span>Roadmap</span>
        </a>
        <a href="UniversityFilter.php" class="menu-item">
            <i class="fa fa-university" aria-hidden="true"></i>
            <span>Search University</span>
        </a>
        <a href="research_idea.php" class="menu-item">
            <i class="fa-solid fa-lightbulb"></i>
            <span>Research Idea</span>
        </a>
        <a href="#" class="menu-item">
            <i class="fa-solid fa-passport"></i>
            <span>Documents</span>
        </a>
        <a href="#" class="menu-item">
            <i class="fa-solid fa-file-pen"></i>
            <span>Exam</span>
        </a>
        <a href="#" class="menu-item">
                <i class="bi bi-activity"></i>
            <span>Activity</span>
        </a>
        <a href="ResearchCollaboration.php" class="menu-item">
            <i class="fa-solid fa-puzzle-piece"></i>
            <span>Collaboration</span>
        </a>
        <a href="MyCollab.php" class="menu-item">
                <i class="fas fa-handshake"></i>
            <span>My Collaboration</span>
        </a>
        <a href="MentorFilter.php" class="menu-item">
            <i class="fa-solid fa-chalkboard-user"></i>
            <span>Mentors Available</span>
        </a>
    </div>
    <div class="sidebar-footer">
        <span>CareerHigh<br>v1.0</span>
    </div>
</div>
    <div class="container">
        <h1>My Collaborations</h1>

        <div class="collab-list">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="collab-card">
                        <h2><?= htmlspecialchars($row['ProjectName']) ?></h2>
                        <p class="desc"><?= nl2br(htmlspecialchars($row['Description'])) ?></p>

                        <div class="card-meta">
                            <span class="badge date">Start: <?= htmlspecialchars($row['startdate']) ?></span>
                            <span class="badge">Members: <?= (int)$row['CurrentPeople'] ?> / <?= (int)$row['MaxPeople'] ?></span>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>You haven’t joined any collaborations yet.</p>
            <?php endif; ?>
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
<?php
$stmt->close();
$conn->close();
?>
