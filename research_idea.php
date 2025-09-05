<?php
// // research_idea.php - SECURE VERSION
// session_start();

// // Redirect to login if not authenticated
// if (!isset($_SESSION['user_id'])) {
//     header("Location: login.php");
//     exit();
// }

// Database connection - use your existing connection method
require_once("connect.php"); // Use your existing connection file

// Get authenticated user ID
$user_id = $_SESSION['user_id'];

// Handle Add Idea
if (isset($_POST['add'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $status = 'In Progress';
    
    $sql = "INSERT INTO research_ideas (title, description, status, user_id, date_created) 
            VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $title, $description, $status, $user_id);
    
    if ($stmt->execute()) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
    $stmt->close();
}

// Handle Delete Idea
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $sql = "DELETE FROM research_ideas WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle Edit Idea
if (isset($_POST['edit_idea'])) {
    $id = (int)$_POST['idea_id'];
    $title = $_POST['idea_title'];
    $description = $_POST['idea_description'];
    
    $sql = "UPDATE research_ideas SET title = ?, description = ? 
            WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $title, $description, $id, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle Mark as Completed
if (isset($_GET['complete'])) {
    $id = (int)$_GET['complete'];
    $sql = "UPDATE research_ideas SET status = 'Completed' WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch user's ideas
$sql = "SELECT * FROM research_ideas WHERE user_id = ? ORDER BY date_created DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Track edit forms
$edit_idea_id = isset($_GET['edit_idea']) ? (int)$_GET['edit_idea'] : null;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Research Ideas</title>
    <link rel="stylesheet" type="text/css" href="css/research.css">
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
        <a href="#" class="menu-item">
            <i class="fa-solid fa-road"></i>
            <span>Roadmap</span>
        </a>
        <a href="#" class="menu-item">
            <i class="fas fa-tasks"></i>
            <span>Task</span>
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
            <i class='fas fa-bell'></i>
            <span>Upcoming Exam</span>
        </a>
        <a href="ExamTracker.php" class="menu-item">
            <i class="fa-solid fa-file-pen"></i>
            <span>Exam Tracking</span>
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
    <header>
        <h1>💡Research Ideas</h1>
        <p class="subtitle">Manage and track your research projects</p>
    </header>

    <!-- Form to add new research ideas -->
    <form method="POST" action="" class="research-form">
        <label for="idea-title">Idea Title</label>
        <input type="text" id="idea-title" name="title" required placeholder="Enter a title for your research idea">
        
        <label for="idea-description">Idea Description</label>
        <textarea id="idea-description" name="description" required placeholder="Describe your research idea in detail"></textarea>

        <button type="submit" class="btn-submit" name="add">Add Idea</button>
    </form>

    <!-- Area to display idea description when clicked -->
    <div id="descBox" class="desc-box">Click on a title to view description here...</div>

    <!-- Table displaying all research ideas -->
    <div class="research-tables">
        <h2>Your Research Ideas</h2>
        <table class="ideas-table">
            <thead>
                <tr>
                    <th>SL.No</th>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Creation Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Initialize serial number
                $sl_no = 1;
                if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $sl_no; ?></td>
                            <td>
                                <a href="#" class="title-link" onclick="showDescription('<?php echo htmlspecialchars($row['description']); ?>')">
                                    <?php echo htmlspecialchars($row['title']); ?>
                                </a>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $row['status'])); ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($row['date_created'])); ?></td>
                            <td>
                                <div class="action-icons">
                                    <!-- Delete action with icon only -->
                                    <a class="action-icon icon-delete" href="?delete=<?php echo $row['id']; ?>" 
                                       onclick="return confirm('Are you sure you want to delete this idea?')" title="Delete">🗑</a>
                                    <!-- Complete action with icon only (only show if not completed) -->
                                    <?php if ($row['status'] != 'Completed'): ?>
                                        <a class="action-icon icon-complete" href="?complete=<?php echo $row['id']; ?>" title="Mark as Complete">✅</a>
                                    <?php endif; ?>
                                    <!-- Edit action with icon only -->
                                    <a class="action-icon icon-edit" href="?edit_idea=<?php echo $row['id']; ?>" title="Edit">✏️</a>
                                </div>
                            </td>
                        </tr>
                        <!-- Edit form (shown when edit icon is clicked) -->
                        <?php if ($edit_idea_id === (int)$row['id']): ?>
                            <tr>
                                <td colspan="5">
                                    <form method="POST" action="" class="edit-form">
                                        <input type="hidden" name="idea_id" value="<?php echo $row['id']; ?>">
                                        <label>Title</label>
                                        <input type="text" name="idea_title" value="<?php echo htmlspecialchars($row['title']); ?>" required>
                                        <label>Description</label>
                                        <textarea name="idea_description" required><?php echo htmlspecialchars($row['description']); ?></textarea>
                                        <button type="submit" class="btn-submit" name="edit_idea">Save Changes</button>
                                        <a href="research_idea.php" class="btn-cancel">Cancel</a>
                                    </form>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php $sl_no++; ?>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="empty-state">You haven't created any research ideas yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Function to show description in the description box
    function showDescription(desc) {
        document.getElementById("descBox").innerText = desc;
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
<?php 
$stmt->close();
$conn->close();
?>

