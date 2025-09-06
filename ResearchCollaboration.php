<?php
require_once("connect.php");
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get user type from session, default to Student if not set
$userType = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'Student';
// Require login (adjust to your auth flow)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$userId = (int) $_SESSION['user_id'];

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "Project_database"; // <- change if needed

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";
$messageType = "info";

// Handle Add to Profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['collab_id'], $_POST['csrf_token']) && $_POST['action'] === 'add') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $message = "Invalid request token.";
        $messageType = "error";
    } else {
        $collabId = (int) $_POST['collab_id'];
        try {
            $conn->begin_transaction();

            // Lock the collaboration row to avoid race conditions
            $stmt = $conn->prepare("SELECT CurrentPeople, MaxPeople FROM RESEARCH_COLLABORATION WHERE CollabID = ? FOR UPDATE");
            $stmt->bind_param("i", $collabId);
            $stmt->execute();
            $stmt->bind_result($current, $max);
            if (!$stmt->fetch()) {
                $stmt->close();
                throw new Exception("Collaboration not found.");
            }
            $stmt->close();

            if ($current >= $max) {
                throw new Exception("This collaboration is full.");
            }

            // Prevent duplicate join
            $stmt = $conn->prepare("SELECT 1 FROM COLLABORATION_PARTICIPANTS WHERE CollabID = ? AND UserID = ?");
            $stmt->bind_param("ii", $collabId, $userId);
            $stmt->execute();
            $stmt->store_result();
            $already = $stmt->num_rows > 0;
            $stmt->close();

            if ($already) {
                throw new Exception("You are already added to this collaboration.");
            }

            // Insert participant
            $stmt = $conn->prepare("INSERT INTO COLLABORATION_PARTICIPANTS (CollabID, UserID) VALUES (?, ?)");
            $stmt->bind_param("ii", $collabId, $userId);
            if (!$stmt->execute()) {
                $stmt->close();
                throw new Exception("Could not add to profile.");
            }
            $stmt->close();

            // Increment current people
            $stmt = $conn->prepare("UPDATE RESEARCH_COLLABORATION SET CurrentPeople = CurrentPeople + 1 WHERE CollabID = ?");
            $stmt->bind_param("i", $collabId);
            if (!$stmt->execute()) {
                $stmt->close();
                throw new Exception("Could not update member count.");
            }
            $stmt->close();

            $conn->commit();
            $message = "Added to profile successfully!";
            $messageType = "success";
        } catch (Exception $e) {
            $conn->rollback();
            $message = $e->getMessage();
            $messageType = "error";
        }
    }
}

// Fetch ONLY available collaborations
$sql = "SELECT CollabID, Description, ProjectName, startdate, MaxPeople, CurrentPeople
        FROM RESEARCH_COLLABORATION
        WHERE CurrentPeople < MaxPeople
        ORDER BY startdate ASC";
$result = $conn->query($sql);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get user type from session, default to Student if not set
$userType = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'Student';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Research Collaborations</title>
    <link rel="stylesheet" href="css/Research Collaboration.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

</head>
<!--Sidebar-->
    <div class="sidebar">
    <div class="sidebar-header">
        <div class="logo">CareerHigh</div>
        <button class="toggle-btn">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    
    <?php if ($userType === 'Student'): ?>
    <!-- Student Menu Items -->
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
    
    <?php elseif ($userType === 'Mentor'): ?>
    <!-- Mentor Menu Items -->
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
                <i class="fas fa-handshake"></i>
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
    
    <?php else: ?>
    <!-- Default/Fallback Menu Items -->
    <div class="menu-items">
        <a href="dashboard.php" class="menu-item">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="logout.php" class="menu-item">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
    <?php endif; ?>
</div>

    <div class="container">
        <h1>Research Collaboration Opportunities</h1>

        <?php if ($message): ?>
            <div class="banner <?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="collab-list">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="collab-card">
                        <h2><?= htmlspecialchars($row['ProjectName']) ?></h2>
                        <p class="desc"><?= nl2br(htmlspecialchars($row['Description'])) ?></p>

                        <div class="card-meta">
                            <span class="badge date">Start: <?= htmlspecialchars($row['startdate']) ?></span>
                            <span class="badge">
                                Members: <?= (int)$row['CurrentPeople'] ?> / <?= (int)$row['MaxPeople'] ?>
                            </span>
                            <span class="badge">Spots left: <?= (int)$row['MaxPeople'] - (int)$row['CurrentPeople'] ?></span>
                        </div>

                        <form class="inline" method="post">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="collab_id" value="<?= (int)$row['CollabID'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <button type="submit" class="join-btn">Add to Profile</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No available collaborations right now.</p>
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
if ($result instanceof mysqli_result) { $result->free(); }
$conn->close();
?>
