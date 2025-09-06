<?php
require_once("connect.php");

// ===== DATABASE CONNECTION =====
$host = "localhost";
$db   = "Project_database";
$user = "root";
$pass = "";

try {
    $db = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("DB Connection failed: " . $e->getMessage());
}

// ===== USER AUTHENTICATION =====
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
}

$user_id = $_SESSION['user_id'];

// ===== ADD EXAM =====
if (isset($_POST['add_exam'])) {
    $stmt = $db->prepare("INSERT INTO exams (name, date, status, user_id) VALUES (?, ?, 'Upcoming', ?)");
    $stmt->execute([$_POST['exam_name'], $_POST['exam_date'], $user_id]);
    
    // Redirect to prevent form resubmission
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// ===== UPDATE RESULT =====
if (isset($_POST['update_exam'])) {
    $id = $_POST['id'];
    $obtained = (int) $_POST['obtained'];
    $total = (int) $_POST['total'];

    if ($total > 0) {
        $percentage = round(($obtained / $total) * 100);
        $score = $percentage . "%";
        $result = $obtained . " / " . $total;

        $stmt = $db->prepare("UPDATE exams SET status='Completed', score=?, result=? WHERE id=? AND user_id=?");
        $stmt->execute([$score, $result, $id, $user_id]);
    }
    
    // Redirect to prevent form resubmission
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// ===== DELETE EXAM =====
if (isset($_POST['delete_exam'])) {
    $id = $_POST['id'];
    $stmt = $db->prepare("DELETE FROM exams WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    
    // Redirect to prevent form resubmission
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// ===== SHOW QUOTE =====
if (isset($_POST['show_quote'])) {
    $exam_id = $_POST['exam_id'];
    $stmt = $db->prepare("SELECT * FROM exams WHERE id = ? AND user_id = ?");
    $stmt->execute([$exam_id, $user_id]);
    $exam = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($exam) {
        if ($exam['status'] === "Completed") {
            $score = intval(rtrim($exam['score'], '%'));
            if ($score < 50) {
                $quote = "💡 <strong>Don't give up!</strong> Every failure is a step closer to success. Review your mistakes and try again!";
            } else if ($score >= 50 && $score < 80) {
                $quote = "👍 <strong>Good effort!</strong> You passed! With a little more practice, you can achieve even better results.";
            } else if ($score >= 80 && $score < 90) {
                $quote = "🎉 <strong>Well done!</strong> That's a great score! Keep up the good work.";
            } else {
                $quote = "🌟 <strong>Excellent work!</strong> You're mastering this subject! Keep aiming high!";
            }
        } else {
            $formatted_date = date('F j, Y', strtotime($exam['date']));
            $quote = "📚 <strong>Upcoming exam:</strong> " . $exam['name'] . " on " . $formatted_date . ". Good luck with your preparation!";
        }
    } else {
        $quote = "❌ <strong>Error:</strong> Exam not found or you don't have permission to view it.";
    }
}

// ===== FETCH EXAMS FOR CURRENT USER =====
$stmt = $db->prepare("SELECT * FROM exams WHERE user_id = ? ORDER BY date ASC");
$stmt->execute([$user_id]);
$exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exam Tracking</title>
    <link rel="stylesheet" href="css/Exam Tracker.css">
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
        <div class="header">
            <h2>📋 Exam Tracking</h2>
            <div class="user-info">
                <span>Welcome, User #<?= $user_id ?></span>
            </div>
        </div>
        
        <div class="content">
            <h3>Add Exam</h3>
            <div class="form-container">
                <form method="post">
                    <div class="form-row">
                        <div class="input-group">
                            <input type="text" name="exam_name" placeholder="Exam Name" required>
                        </div>
                        <div class="input-group">
                            <input type="date" name="exam_date" required>
                        </div>
                        <button type="submit" name="add_exam" class="btn">Add Exam</button>
                    </div>
                </form>
            </div>

            <h3>Your Exams</h3>
            <div class="table-container">
                <table>
                    <tr>
                        <th>Exam</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Score</th>
                        <th>Result</th>
                        <th>Actions</th>
                    </tr>
                    <?php if (count($exams) > 0): ?>
                        <?php foreach ($exams as $exam): ?>
                        <tr>
                            <td>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="exam_id" value="<?= $exam['id'] ?>">
                                    <button type="submit" name="show_quote" class="exam-name-btn">
                                        <?= htmlspecialchars($exam['name']) ?>
                                    </button>
                                </form>
                            </td>
                            <td><?= $exam['date'] ?></td>
                            <td>
                                <?php if ($exam['status'] == "Upcoming"): ?>
                                    <span class="status-upcoming">⏳ <?= $exam['status'] ?></span>
                                <?php else: ?>
                                    <span class="status-completed">✅ <?= $exam['status'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="score-cell"><?= $exam['score'] ?? '-' ?></td>
                            <td><?= $exam['result'] ?? '-' ?></td>
                            <td>
                                <div class="actions-container">
                                    <?php if ($exam['status'] == "Upcoming"): ?>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= $exam['id'] ?>">
                                        <div class="inline-form">
                                            <input type="number" name="obtained" placeholder="Score" required min="0" class="input-small">
                                            <input type="number" name="total" placeholder="Total" required min="1" class="input-small">
                                            <button type="submit" name="update_exam" class="icon-btn" title="Update Exam">📝</button>
                                        </div>
                                    </form>
                                    <?php endif; ?>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this exam?');">
                                        <input type="hidden" name="id" value="<?= $exam['id'] ?>">
                                        <button type="submit" name="delete_exam" class="icon-btn delete-btn" title="Delete Exam">❌</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 20px; color: #8a7ba5;">
                                No exams found. Add your first exam above.
                            </td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>

            <?php if (isset($quote)): ?>
            <div class="quote-container">
                <div class="quote-box">
                    <?= $quote ?>
                </div>
            </div>
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