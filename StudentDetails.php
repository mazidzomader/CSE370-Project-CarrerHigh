<?php
// student_profile.php
// Shows a student's profile, tasks, documents and progress.
// Place in same folder as connect.php and other pages.

//if (session_status() === PHP_SESSION_NONE) {
//    session_start();
//}

require_once("connect.php"); // must set $conn (mysqli)

// Require login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$viewer_id = (int) $_SESSION['user_id'];

// determine which student to show
$student_id = isset($_GET['id']) ? (int) $_GET['id'] : $viewer_id;
if ($student_id <= 0) {
    header("Location: Dashboard.php");
    exit();
}

// get viewer user type
$user_type = null;
$sql = "SELECT UserType FROM user WHERE UserID = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $viewer_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $user_type);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}

// permission check:
// - If viewer is Mentor, ensure they have a relationship with this student (or allow viewing if same user).
// - If viewer is Student, only allow viewing their own profile.
if ($viewer_id !== $student_id) {
    if ($user_type !== 'Mentor') {
        // not allowed to view other students
        header("Location: Dashboard.php");
        exit();
    }
    // viewer is mentor: verify relationship exists and status is not 'Terminated' (still allow Completed maybe)
    $rel_ok = false;
    $rel_sql = "SELECT COUNT(*) FROM mentor_student_relationship WHERE MentorID = ? AND StudentID = ?";
    if ($rel_stmt = mysqli_prepare($conn, $rel_sql)) {
        mysqli_stmt_bind_param($rel_stmt, "ii", $viewer_id, $student_id);
        mysqli_stmt_execute($rel_stmt);
        mysqli_stmt_bind_result($rel_stmt, $rel_count);
        mysqli_stmt_fetch($rel_stmt);
        mysqli_stmt_close($rel_stmt);
        if ($rel_count > 0) $rel_ok = true;
    }
    if (!$rel_ok) {
        // not assigned
        header("Location: Apprentice_status.php");
        exit();
    }
}

// Fetch student basic info
$student = null;
$sql = "SELECT u.UserID, u.Name, u.Email, u.Registration_Date, u.Status, s.Degree_Programme, s.MentorID
        FROM user u
        LEFT JOIN student s ON s.UserID = u.UserID
        WHERE u.UserID = ? LIMIT 1";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $student_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $student = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
}
if (!$student) {
    echo "Student not found.";
    exit();
}

// Optionally fetch mentor name
$mentor_name = null;
if (!empty($student['MentorID'])) {
    $m_sql = "SELECT Name FROM user WHERE UserID = ? LIMIT 1";
    if ($m_stmt = mysqli_prepare($conn, $m_sql)) {
        mysqli_stmt_bind_param($m_stmt, "i", $student['MentorID']);
        mysqli_stmt_execute($m_stmt);
        mysqli_stmt_bind_result($m_stmt, $mentor_name);
        mysqli_stmt_fetch($m_stmt);
        mysqli_stmt_close($m_stmt);
    }
}

// Fetch tasks for this student
$tasks = [];
$tasks_sql = "SELECT * FROM tasks WHERE user_id = ? ORDER BY FIELD(category, 'school','high-school','university','bachelor','master','phd','job','other'), due_date ASC, created_at ASC";
if ($t_stmt = mysqli_prepare($conn, $tasks_sql)) {
    mysqli_stmt_bind_param($t_stmt, "i", $student_id);
    mysqli_stmt_execute($t_stmt);
    $t_res = mysqli_stmt_get_result($t_stmt);
    while ($row = mysqli_fetch_assoc($t_res)) {
        $tasks[] = $row;
    }
    mysqli_stmt_close($t_stmt);
}

// Fetch documents for this student
$documents = [];
$docs_sql = "SELECT * FROM documents WHERE user_id = ? ORDER BY FIELD(category, 'academic','grade_sheet','exam_result','competition','co_curricular','achievement','research','birth_certificate','passport','bank','other'), due_date ASC, created_at ASC";
if ($d_stmt = mysqli_prepare($conn, $docs_sql)) {
    mysqli_stmt_bind_param($d_stmt, "i", $student_id);
    mysqli_stmt_execute($d_stmt);
    $d_res = mysqli_stmt_get_result($d_stmt);
    while ($row = mysqli_fetch_assoc($d_res)) {
        $documents[] = $row;
    }
    mysqli_stmt_close($d_stmt);
}

// Compute progress (same logic as apprentices_status)
// tasks % and docs % -> overall average if both exist
$tasks_total = count($tasks);
$tasks_completed = 0;
foreach ($tasks as $t) if (!empty($t['completed'])) $tasks_completed++;

$docs_total = count($documents);
$docs_completed = 0;
foreach ($documents as $d) if (isset($d['status']) && $d['status'] === 'completed') $docs_completed++;

$task_pct = $tasks_total > 0 ? round(($tasks_completed / $tasks_total) * 100) : 0;
$doc_pct  = $docs_total > 0 ? round(($docs_completed / $docs_total) * 100) : 0;

if ($tasks_total > 0 && $docs_total > 0) {
    $progress_pct = round(($task_pct + $doc_pct) / 2);
} elseif ($tasks_total > 0) {
    $progress_pct = $task_pct;
} elseif ($docs_total > 0) {
    $progress_pct = $doc_pct;
} else {
    $progress_pct = 0;
}

// Helper to print safe text
function h($s){ return htmlspecialchars($s); }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title><?php echo h($student['Name']); ?> — Profile | CareerHigh</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/Roadmap.css">
    <style>
        /* small page-specific tweaks so profile header fits the roadmap look */
        .profile-header { display:flex; align-items:center; gap:16px; padding:20px; }
        .avatar { width:72px; height:72px; border-radius:50%; background:linear-gradient(135deg,#8a2be2,#9370db); color:white; display:flex; align-items:center; justify-content:center; font-size:28px; font-weight:700; }
        .profile-meta h2 { margin:0; font-size:20px; color:#6b4e9d; }
        .profile-meta p { margin:4px 0; color:#8a7ba5; font-size:14px; }
        .small-action { font-size:14px; color:#7e57c2; text-decoration:none; }
        .section-title { margin-bottom:10px; color:#6b4e9d; font-weight:600; font-size:18px; }
        .list-empty { padding:14px; color:#8a7ba5; background:#faf8ff; border-radius:10px; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="main-content">
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo">CareerHigh</div>
            <button class="toggle-btn">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        <div class="menu-items">
            <a href="Mentordash.php" class="menu-item">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="MentorProfile.php" class="menu-item">
                <i class="fas fa-user"></i>
                <span>Edit Profile</span>
            </a>
            <a href="Apprentice_status.php" class="menu-item active">
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
                <h1>Student Profile</h1>
                <p class="subtitle">View student's details, roadmap and progress</p>
            </header>

            <div style="padding:20px 25px;">
                <div class="profile-header">
                    <div class="avatar"><?php
                        // initials
                        $parts = preg_split('/\s+/', $student['Name']);
                        $initials = '';
                        foreach ($parts as $p) if ($p) $initials .= strtoupper($p[0]);
                        echo h(substr($initials,0,2));
                    ?></div>

                    <div class="profile-meta">
                        <h2><?php echo h($student['Name']); ?> <?php if(!empty($student['Status'])) echo '<span style="font-weight:500;font-size:13px;color:#8a7ba5">('.h($student['Status']).')</span>'; ?></h2>
                        <p><?php echo h($student['Degree_Programme'] ?: 'Degree program not set'); ?></p>
                        <p style="font-size:13px;color:#8a7ba5;">Email: <?php echo h($student['Email']); ?></p>
                        <p style="font-size:13px;color:#8a7ba5;">Registered: <?php echo date('M j, Y', strtotime($student['Registration_Date'])); ?></p>
                        <?php if ($mentor_name): ?>
                            <p style="font-size:13px;color:#8a7ba5;">Mentor: <?php echo h($mentor_name); ?></p>
                        <?php endif; ?>
                    </div>

                    <div style="margin-left:auto; text-align:right;">
                        <a href="Apprentice_status.php" class="small-action"><i class="fas fa-arrow-left"></i> Back to Apprentices</a>
                        <div style="height:8px;"></div>
                        <?php if ($viewer_id === $student_id): ?>
                            <a href="studentProfile.php" class="small-action"><i class="fas fa-edit"></i> Edit Profile</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Progress -->
                <div style="margin-top:12px;">
                    <div class="section-title">Progress Overview</div>
                    <div class="progress-stats">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo (int)$progress_pct; ?>%;"></div>
                        </div>
                        <div class="progress-text" style="margin-top:8px;">
                            <span><?php echo (int)$progress_pct; ?>% Complete</span>
                            <span><?php echo $tasks_completed; ?> / <?php echo $tasks_total; ?> tasks &nbsp;|&nbsp; <?php echo $docs_completed; ?> / <?php echo $docs_total; ?> documents</span>
                        </div>
                    </div>
                </div>

                <div style="height:18px;"></div>

                <!-- Roadmap style lists: Tasks -->
                <div class="timeline-phase" id="student-tasks" style="margin-left:0;">
                    <div class="phase-header" style="position:relative; margin-bottom:10px;">
                        <div class="phase-icon" style="left:0;"><i class="fas fa-tasks"></i></div>
                        <h2 style="margin-left:84px;">Tasks</h2>
                    </div>
                    <div class="phase-items" style="margin-left:84px;">
                        <?php if (empty($tasks)): ?>
                            <div class="list-empty">No tasks for this student.</div>
                        <?php else: ?>
                            <?php foreach ($tasks as $task): ?>
                                <div class="roadmap-item <?php echo !empty($task['completed']) ? 'completed' : 'pending'; ?>">
                                    <div class="item-icon"><i class="fas fa-tasks"></i></div>
                                    <div class="item-content">
                                        <h3><?php echo h($task['title']); ?></h3>
                                        <div class="item-status"><?php echo !empty($task['completed']) ? 'Completed' : 'Pending'; ?></div>
                                        <?php if (!empty($task['due_date'])): ?>
                                            <div class="item-due"><i class="fas fa-calendar"></i> <?php echo date('M j, Y', strtotime($task['due_date'])); ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($task['description'])): ?>
                                            <p style="margin-top:8px;color:#8a7ba5;"><?php echo h($task['description']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="height:20px;"></div>

                <!-- Documents -->
                <div class="timeline-phase" id="student-docs" style="margin-left:0;">
                    <div class="phase-header" style="position:relative; margin-bottom:10px;">
                        <div class="phase-icon" style="left:0;"><i class="fas fa-file-alt"></i></div>
                        <h2 style="margin-left:84px;">Documents</h2>
                    </div>
                    <div class="phase-items" style="margin-left:84px;">
                        <?php if (empty($documents)): ?>
                            <div class="list-empty">No documents for this student.</div>
                        <?php else: ?>
                            <?php foreach ($documents as $doc): ?>
                                <div class="roadmap-item <?php echo ($doc['status'] === 'completed') ? 'completed' : 'pending'; ?>">
                                    <div class="item-icon"><i class="fas fa-file-alt"></i></div>
                                    <div class="item-content">
                                        <h3><?php echo h($doc['title']); ?></h3>
                                        <div class="item-status"><?php echo ucfirst($doc['status']); ?></div>
                                        <?php if (!empty($doc['due_date'])): ?>
                                            <div class="item-due"><i class="fas fa-calendar"></i> <?php echo date('M j, Y', strtotime($doc['due_date'])); ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($doc['description'])): ?>
                                            <p style="margin-top:8px;color:#8a7ba5;"><?php echo h($doc['description']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($doc['upload_path'])): ?>
                                        <div style="width:120px; text-align:right;">
                                            <a href="<?php echo h($doc['upload_path']); ?>" target="_blank" class="small-action"><i class="fas fa-download"></i> Download</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div> <!-- inner padding -->
        </div> <!-- container -->

        <div class="copyright">© 2025 CareerHigh. All rights reserved.</div>
    </div>

    <script>
        // Sidebar toggle
        const toggleBtn = document.querySelector('.toggle-btn');
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
            });
        }

        // animate items on view (same behaviour as roadmap)
        document.addEventListener('DOMContentLoaded', function() {
            const roadmapItems = document.querySelectorAll('.roadmap-item');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate');
                    }
                });
            }, { threshold: 0.1 });
            roadmapItems.forEach(item => observer.observe(item));
        });
    </script>
</body>
</html>
