<?php
// activity.php
session_start();

// Require login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the authenticated user's ID
$user_id = $_SESSION['user_id'];

// Database connection
$conn = new mysqli("localhost", "root", "", "project_database");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user's name
$user_result = $conn->query("SELECT Name FROM user WHERE UserID = $user_id");
$user_name = "User";
if ($user_result && $user_result->num_rows > 0) {
    $user_data = $user_result->fetch_assoc();
    $user_name = $user_data['Name'];
}

// Add new activity
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $type = $conn->real_escape_string($_POST['type']);
    $achievements = $conn->real_escape_string($_POST['achievements']);
    $description = $conn->real_escape_string($_POST['description']);

    $sql = "INSERT INTO activities (name, type, date, achievements, description, user_id) 
            VALUES ('$name', '$type', CURDATE(), '$achievements', '$description', '$user_id')";
    
    if ($conn->query($sql)) {
        $_SESSION['success_message'] = "Activity added successfully!";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } else {
        $_SESSION['error_message'] = "Error adding activity: " . $conn->error;
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

// Delete activity
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM activities WHERE id=$id AND user_id=$user_id");
    $_SESSION['success_message'] = "Activity deleted successfully!";
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Update activity
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $name = $conn->real_escape_string($_POST['name']);
    $type = $conn->real_escape_string($_POST['type']);
    $achievements = $conn->real_escape_string($_POST['achievements']);
    $description = $conn->real_escape_string($_POST['description']);

    $sql = "UPDATE activities 
            SET name='$name', type='$type', achievements='$achievements', description='$description' 
            WHERE id=$id AND user_id=$user_id";
    
    if ($conn->query($sql)) {
        $_SESSION['success_message'] = "Activity updated successfully!";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } else {
        $_SESSION['error_message'] = "Error updating activity: " . $conn->error;
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

// Show activity description
if (isset($_GET['show'])) {
    $show_id = intval($_GET['show']);
    $_SESSION['show_activity'] = $show_id;
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Get success/error messages from session
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';

// Clear messages from session after displaying
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

// Get activity to show from session
$show_activity_id = isset($_SESSION['show_activity']) ? $_SESSION['show_activity'] : 0;
unset($_SESSION['show_activity']);

// Fetch user's activities only
$result = $conn->query("SELECT * FROM activities WHERE user_id = $user_id ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>📝 Co-Curricular Activities</title>
    <link rel="stylesheet" href="css/Activity.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* Sidebar styles */
        /* Sidebar styles */
        .sidebar {
            width: 250px;
            background: linear-gradient(to bottom, #ffffff, #f2eafd);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            overflow-y: auto;   /* ✅ makes it scrollable */
            overflow-x: hidden; /* ✅ prevents sideways scroll */
        }

        .sidebar.collapsed {
            width: 70px;
        }

        .sidebar-header {
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(147, 112, 219, 0.2);
            position: relative; /* Added to contain the toggle button */
            min-height: 60px; /* Ensure consistent height */
        }

        .sidebar.collapsed .sidebar-footer {
            font-size: 9px;       /* Made even smaller (was 10px) */
            padding: 5px 2px;     /* Reduced padding more */
            word-break: break-all; /* Ensure breaking works in collapsed state */
        }

        /* Additional fix for very narrow widths */
        @media (max-width: 300px) {
            .sidebar-footer {
                font-size: 10px;
                padding: 8px 3px;
            }
            
            .sidebar.collapsed .sidebar-footer {
                font-size: 8px;
                padding: 4px 1px;
            }
        }

        .logo {
            font-size: 22px;
            font-weight: 600;
            color: #7e57c2;
            opacity: 1;
            transition: opacity 0.3s ease;
        }

        .sidebar.collapsed .logo {
            opacity: 0;
            pointer-events: none;
        }

        .toggle-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: #7e57c2;
            font-size: 20px;
            position: absolute; /* Changed from relative */
            right: 20px; /* Position from right */
            top: 50%; /* Center vertically */
            transform: translateY(-50%); /* Center vertically */
            z-index: 10; /* Ensure it stays on top */
            transition: all 0.3s ease; /* Smooth transition */
        }

        /* Keep button properly positioned when sidebar is collapsed */
        .sidebar.collapsed .toggle-btn {
            right: 20px; /* Maintain position */
            transform: translateY(-50%); /* Keep centered */
        }

        .menu-items {
            padding: 20px 0;
            flex: 1;
        }

        .menu-item {
            padding: 14px 20px;
            display: flex;
            align-items: center;
            color: #5a5a5a;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .menu-item:hover {
            background-color: rgba(126, 87, 194, 0.1);
            color: #7e57c2;
        }

        .menu-item i {
            margin-right: 15px;
            font-size: 18px;
            min-width: 20px;
            text-align: center;
        }

        .sidebar.collapsed .menu-item span {
            opacity: 0;
            pointer-events: none;
        }

        .sidebar-footer {
            padding: 10px 5px;
            border-top: 1px solid rgba(147, 112, 219, 0.2);
            font-size: 12px;
            color: #7e57c2;
            text-align: center;
            word-break: break-all;  /* Changed from break-word to break-all */
            white-space: normal;
            line-height: 1.3;
            overflow-wrap: break-word;  /* Added for better wrapping */
            hyphens: auto;  /* Added for hyphenation */
        }

        .sidebar-footer span {
            display: block;
            width: 100%;
            overflow: hidden;  /* Added to prevent overflow */
        }
</style>
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
</div>
    <h1>📝Co-Curricular Activities</h1>
    <p class="subtitle">Manage your co curricular activities here</p>

    <!-- Display messages -->
    <?php if (!empty($success_message)): ?>
        <div class="message success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)): ?>
        <div class="message error">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <!-- Add Activity Form -->
    <div class="form-container">
        <h2><i class="fas fa-plus-circle"></i> Add New Activity</h2>
        <form method="post">
            <div class="form-row">
                <input type="text" name="name" placeholder="Activity Name" required>
                <input type="text" name="type" placeholder="Type (e.g., Sports, Arts, Club)" required>
            </div>
            <div class="form-row">
                <input type="text" name="achievements" placeholder="Achievements/Role" required>
                <textarea name="description" placeholder="Detailed Description" required></textarea>
            </div>
            <button type="submit" name="add" class="btn">
                <i class="fas fa-plus"></i> Add Activity
            </button>
        </form>
    </div>

    <!-- Activities Table -->
    <div class="form-container">
        <h2><i class="fas fa-list"></i> Your Activities</h2>
        <?php if ($result && $result->num_rows > 0): ?>
        <table>
            <tr>
                <th>SL.No</th>
                <th>Name</th>
                <th>Type</th>
                <th>Achievements</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
            <?php 
            $sl = 1;
            while ($row = $result->fetch_assoc()): 
                $show_desc = $show_activity_id == $row['id'];
            ?>
                <tr>
                    <td><?= $sl++ ?></td>
                    <td>
                        <a href="activity.php?show=<?= $row['id'] ?>" class="activity-name">
                            <?= htmlspecialchars($row['name']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($row['type']) ?></td>
                    <td><?= htmlspecialchars($row['achievements']) ?></td>
                    <td><?= $row['date'] ?></td>
                    <td class="action-buttons">
                        <a href="activity.php?edit=<?= $row['id'] ?>" class="btn-icon update-btn" title="Update">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="activity.php?delete=<?= $row['id'] ?>" class="btn-icon delete-btn" title="Delete" onclick="return confirm('Are you sure you want to delete this activity?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php if ($show_desc): ?>
                    <tr class="desc-box">
                        <td colspan="6">
                            <strong>Description:</strong><br>
                            <?= nl2br(htmlspecialchars($row['description'])) ?>
                            <div class="close-desc">
                                <a href="activity.php" class="btn-icon close-btn" title="Close">
                                    <i class="fas fa-times"></i> Close
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endwhile; ?>
        </table>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-clipboard-list"></i>
                <p>No activities found. Add your first activity above!</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Update Form -->
    <?php if (isset($_GET['edit'])):
        $id = intval($_GET['edit']);
        $editResult = $conn->query("SELECT * FROM activities WHERE id=$id AND user_id=$user_id");
        if ($editResult && $editResult->num_rows > 0):
            $editRow = $editResult->fetch_assoc();
    ?>
    <div class="form-container">
        <h2><i class="fas fa-edit"></i> Update Activity</h2>
        <form method="post">
            <input type="hidden" name="id" value="<?= $editRow['id'] ?>">
            <div class="form-row">
                <input type="text" name="name" value="<?= htmlspecialchars($editRow['name']) ?>" required>
                <input type="text" name="type" value="<?= htmlspecialchars($editRow['type']) ?>" required>
            </div>
            <div class="form-row">
                <input type="text" name="achievements" value="<?= htmlspecialchars($editRow['achievements']) ?>" required>
                <textarea name="description" required><?= htmlspecialchars($editRow['description']) ?></textarea>
            </div>
            <button type="submit" name="update" class="btn">
                <i class="fas fa-save"></i> Update Activity
            </button>
            <a href="activity.php" class="btn-icon close-btn" style="margin-left: 10px;">
                <i class="fas fa-times"></i> Cancel
            </a>
        </form>
    </div>
    <?php 
        else:
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        endif;
    endif; 
    ?>

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


