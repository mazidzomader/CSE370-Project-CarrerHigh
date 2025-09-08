<?php
require_once("connect.php");
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";
$errorMessage = "";

if (isset($_GET['added']) && $_GET['added'] == 1) { 
    $message = "Task added successfully!";
} 
if (isset($_GET['deleted']) && $_GET['deleted'] == 1) {
    $message = "Task deleted successfully!";
}

// Handle task creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_task'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $due_date = !empty($_POST['due_date']) ? trim($_POST['due_date']) : null;

    if (!empty($title) && !empty($category)) {
        // Prepare and execute the INSERT query
        $sql = "INSERT INTO tasks (user_id, title, description, category, due_date) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "issss", $_SESSION['user_id'], $title, $description, $category, $due_date);
            
            if (mysqli_stmt_execute($stmt)) {
                $message = "Task added successfully!";
                header("Location: task.php?success=1");
                exit();
            } else {
                $errorMessage = "Error adding task: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        } else {
            $errorMessage = "Database error: " . mysqli_error($conn);
        }
    } else {
        $errorMessage = "Title and category are required!";
    }
}

// Handle task deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $sql = "DELETE FROM tasks WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ii", $delete_id, $_SESSION['user_id']);
        
        if (mysqli_stmt_execute($stmt)) {
            $message = "Task deleted successfully!";
            header("Location: task.php?success=1");
            exit();
        } else {
            $errorMessage = "Error deleting task: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        $errorMessage = "Database error: " . mysqli_error($conn);
    }
}

// Handle task completion toggle
if (isset($_GET['toggle_id'])) {
    $toggle_id = $_GET['toggle_id'];
    $sql = "UPDATE tasks SET completed = NOT completed WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ii", $toggle_id, $_SESSION['user_id']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header("Location: task.php?success=1");
        exit();
    }
}

// Fetch logged in user tasks
$tasks = [];
$sql = "SELECT * FROM tasks 
        WHERE user_id = ? 
        ORDER BY 
        FIELD(category, 'school', 'high-school', 'university', 'bachelor', 'master', 'phd', 'job', 'other'),
        due_date ASC, created_at ASC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $tasks[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Career Roadmap Tasks | CareerHigh</title>
    <link rel="icon" type="image/png" href="img/Landing_logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/Tasks.css">   <!-- the link should be changed if css file is not loaded -->
</head>
<body>
    <!-- Sidebar (same as UniversityFilter.php) -->
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
                <h1>Your Career Roadmap Tasks</h1>
                <p class="subtitle">Plan your journey from school to career</p>
            </header>
            
            <!-- Add Task Form -->
            <form class="task-form" method="POST" action="task.php">
                <div class="form-row">
                    <div class="form-group">
                        <label for="title"><i class="fas fa-heading"></i> Task Title</label>
                        <input type="text" id="title" name="title" placeholder="e.g., Prepare for SAT exams" required>
                    </div>
                </div>
                <div class="form-group">
                        <label for="category"><i class="fas fa-layer-group"></i> Category</label>
                        <select id="category" name="category" required>
                            <option value="">Select a category</option>
                            <option value="school">School</option>
                            <option value="high-school">High School</option>
                            <option value="university">University</option>
                            <option value="bachelor">Bachelor's Degree</option>
                            <option value="master">Master's Degree</option>
                            <option value="phd">PhD</option>
                            <option value="job">Job/Career</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                <br>
                <div class="form-row">
                    <div class="form-group">
                        <label for="description"><i class="fas fa-align-left"></i> Description (Optional)</label>
                        <textarea id="description" name="description" placeholder="Add details about this task..."></textarea>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="due_date"><i class="fas fa-calendar-day"></i> Due Date (Optional)</label>
                        <input type="date" id="due_date" name="due_date">
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" name="add_task" class="btn-submit">Add Task</button>
                    </div>
                </div>
            </form>
            
            <!-- Messages -->
            <?php if (!empty($message)): ?>
                <div class="message success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo htmlspecialchars($message); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errorMessage)): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($errorMessage); ?></span>
                </div>
            <?php endif; ?>
            
            <!-- Tasks List -->
            <div class="tasks-section">
                <div class="tasks-header">
                    <h2 class="tasks-title">Your Career Tasks</h2>
                    <div class="tasks-count">Total: <span><?php echo count($tasks); ?></span> tasks</div>
                </div>
                
                <?php if (count($tasks) > 0): ?>
                    <div class="tasks-list">
                        <?php foreach ($tasks as $task): ?>
                            <div class="task-card <?php echo $task['completed'] ? 'completed' : ''; ?>">
                                <div class="task-check">
                                    <a href="task.php?toggle_id=<?php echo $task['id']; ?>" class="check-btn">
                                        <i class="fas fa-<?php echo $task['completed'] ? 'check-circle' : 'circle'; ?>"></i>
                                    </a>
                                </div>
                                <div class="task-content">
                                    <h3 class="task-title"><?php echo htmlspecialchars($task['title']); ?></h3>
                                    <?php if (!empty($task['description'])): ?>
                                        <p class="task-description"><?php echo htmlspecialchars($task['description']); ?></p>
                                    <?php endif; ?>
                                    <div class="task-meta">
                                        <span class="task-category"><?php echo ucfirst($task['category']); ?></span>
                                        <?php if (!empty($task['due_date'])): ?>
                                            <span class="task-due">
                                                <i class="fas fa-calendar"></i>
                                                <?php echo date('M j, Y', strtotime($task['due_date'])); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="task-actions">
                                    <a href="task.php?delete_id=<?php echo $task['id']; ?>" class="delete-btn" 
                                       onclick="return confirm('Are you sure you want to delete this task?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-tasks">
                        <i class="fas fa-clipboard-list"></i>
                        <h3>No Tasks Yet</h3>
                        <p>Start planning your career journey by adding tasks above.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="copyright">
            © 2025 CareerHigh. All rights reserved.
        </div>
    </div>

    <script>
        // Sidebar toggle functionality (same as UniversityFilter.php)
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