<?php
// apprentices_status.php
// Mentor view: list apprentices with progress bars

// safe session start
//if (session_status() === PHP_SESSION_NONE) {
//    session_start();
//}

require_once("connect.php"); // your DB connection; must set $conn (mysqli)

// Ensure logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$mentor_id = (int) $_SESSION['user_id'];

// Verify user is a mentor (optional but recommended)
$check_sql = "SELECT UserType FROM user WHERE UserID = ?";
if ($stmt = mysqli_prepare($conn, $check_sql)) {
    mysqli_stmt_bind_param($stmt, "i", $mentor_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $user_type);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if ($user_type !== 'Mentor') {
        // Not a mentor → redirect or show message
        header("Location: dashboard.php");
        exit();
    }
} else {
    // If query failed, treat as unauthorized
    header("Location: dashboard.php");
    exit();
}

// Fetch apprentices assigned to this mentor (include relationship status & assignment date)
$apprentices = [];
$sql = "
    SELECT m.MentorID, m.StudentID, m.Assignment_Date, m.Status AS RelationshipStatus,
           u.Name AS student_name, u.Email AS student_email,
           s.Degree_Programme
    FROM mentor_student_relationship m
    JOIN user u ON m.StudentID = u.UserID
    LEFT JOIN student s ON s.UserID = m.StudentID
    WHERE m.MentorID = ?
    ORDER BY m.Assignment_Date DESC
";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $mentor_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) {
        $apprentices[] = $row;
    }
    mysqli_stmt_close($stmt);
}

// Helper: compute task/document stats for a given student id
function compute_progress_for_student($conn, $student_id) {
    $student_id = (int)$student_id;
    $result = [
        'tasks_total' => 0,
        'tasks_completed' => 0,
        'docs_total' => 0,
        'docs_completed' => 0,
        'progress_pct' => 0
    ];

    // Tasks
    $sql = "SELECT COUNT(*) AS total, SUM(completed) AS completed FROM tasks WHERE user_id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $student_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $t_total, $t_completed);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        $result['tasks_total'] = (int)$t_total;
        $result['tasks_completed'] = (int)$t_completed;
    }

    // Documents
    $sql = "SELECT COUNT(*) AS total, SUM(status = 'completed') AS completed FROM documents WHERE user_id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $student_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $d_total, $d_completed);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        $result['docs_total'] = (int)$d_total;
        $result['docs_completed'] = (int)$d_completed;
    }

    // Compute percentages
    $task_pct = 0;
    $doc_pct = 0;

    if ($result['tasks_total'] > 0) {
        $task_pct = round( ($result['tasks_completed'] / $result['tasks_total']) * 100 );
    }
    if ($result['docs_total'] > 0) {
        $doc_pct = round( ($result['docs_completed'] / $result['docs_total']) * 100 );
    }

    // Overall progress logic:
    // - If both tasks and docs exist, average them.
    // - If only one exists, use that percentage.
    // - If neither exists, progress = 0.
    if ($result['tasks_total'] > 0 && $result['docs_total'] > 0) {
        $result['progress_pct'] = round( ($task_pct + $doc_pct) / 2 );
    } elseif ($result['tasks_total'] > 0) {
        $result['progress_pct'] = $task_pct;
    } elseif ($result['docs_total'] > 0) {
        $result['progress_pct'] = $doc_pct;
    } else {
        $result['progress_pct'] = 0;
    }

    // store per-type pct for display
    $result['task_pct'] = $task_pct;
    $result['doc_pct'] = $doc_pct;

    return $result;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Apprentices Status | CareerHigh</title>
    <link rel="icon" type="image/png" href="img/Landing_logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Use existing roadmap CSS for consistent style -->
    <link rel="stylesheet" href="css/Roadmap.css">
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

    <div class="main-content">
        <div class="container">
            <header>
                <h1>Apprentices Status</h1>
                <p class="subtitle">Overview of your apprentices and their progress</p>
            </header>

            <div class="roadmap-container">
                <div class="progress-overview">
                    <h2>Your Apprentices</h2>

                    <?php if (empty($apprentices)): ?>
                        <div style="padding:20px;">
                            <p>No apprentices assigned yet.</p>
                        </div>
                    <?php else: ?>

                        <div class="progress-stats">
                            <?php foreach ($apprentices as $appr): 
                                $stats = compute_progress_for_student($conn, $appr['StudentID']);
                                $progress = max(0, min(100, (int)$stats['progress_pct']));
                                $task_text = $stats['tasks_completed'] . '/' . $stats['tasks_total'];
                                $doc_text  = $stats['docs_completed'] . '/' . $stats['docs_total'];
                            ?>
                                <div class="roadmap-item <?php echo ($progress >= 100 ? 'completed' : 'pending'); ?> animate" style="margin-bottom:18px;">
                                    <div class="item-icon">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <div class="item-content">
                                        <h3><?php echo htmlspecialchars($appr['student_name']); ?> 
                                            <small style="font-weight:400; color:#8a7ba5; font-size:13px; margin-left:8px;">
                                                (<?php echo htmlspecialchars($appr['Degree_Programme'] ?: '—'); ?>)
                                            </small>
                                        </h3>

                                        <div class="item-status"><?php echo htmlspecialchars($appr['RelationshipStatus']); ?></div>

                                        <div style="margin-top:8px; margin-bottom:8px;">
                                            <div style="font-size:13px; color:#8a7ba5;">
                                                Assigned: <?php echo date('M j, Y', strtotime($appr['Assignment_Date'])); ?> &nbsp;•&nbsp; Email: <?php echo htmlspecialchars($appr['student_email']); ?>
                                            </div>
                                        </div>

                                        <!-- Progress bar -->
                                        <div class="progress-bar" aria-label="Progress for <?php echo htmlspecialchars($appr['student_name']); ?>">
                                            <div class="progress-fill" style="width: <?php echo $progress; ?>%;"></div>
                                        </div>

                                        <div class="progress-text" style="margin-top:6px;">
                                            <span>Progress: <strong><?php echo $progress; ?>%</strong></span>
                                            <span style="opacity:0.85">Tasks: <?php echo $task_text; ?> | Docs: <?php echo $doc_text; ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="copyright">© 2025 CareerHigh. All rights reserved.</div>
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