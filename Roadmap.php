<?php
require_once("connect.php");
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch only the logged-in user’s tasks
$tasks = [];
$tasks_sql = "SELECT * FROM tasks 
              WHERE user_id = ? 
              ORDER BY 
              FIELD(category, 'school', 'high-school', 'university', 'bachelor', 'master', 'phd', 'job', 'other'),
              due_date ASC, created_at ASC";
$task_stmt = mysqli_prepare($conn, $tasks_sql);
mysqli_stmt_bind_param($task_stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($task_stmt);
$tasks_result = mysqli_stmt_get_result($task_stmt);
mysqli_stmt_close($task_stmt);


if ($tasks_result && mysqli_num_rows($tasks_result) > 0) {
    while ($row = mysqli_fetch_assoc($tasks_result)) {
        $tasks[] = $row;
    }
}

// Fetch only the logged-in user’s documents
$documents = [];
$documents_sql = "SELECT * FROM documents 
                  WHERE user_id = ? 
                  ORDER BY 
                  FIELD(category, 'academic', 'grade_sheet', 'exam_result', 'competition', 'co_curricular', 'achievement', 'research', 'birth_certificate', 'passport', 'bank', 'other'),
                  due_date ASC, created_at ASC";
$document_stmt = mysqli_prepare($conn, $documents_sql);
mysqli_stmt_bind_param($document_stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($document_stmt);
$documents_result = mysqli_stmt_get_result($document_stmt);
mysqli_stmt_close($document_stmt);

if ($documents_result && mysqli_num_rows($documents_result) > 0) {
    while ($row = mysqli_fetch_assoc($documents_result)) {
        $documents[] = $row;
    }
}

// Combine tasks and documents for the roadmap
$roadmap_items = [];

// Process tasks
foreach ($tasks as $task) {
    $roadmap_items[] = [
        'id' => 'task_' . $task['id'],
        'type' => 'task',
        'title' => $task['title'],
        'category' => $task['category'],
        'status' => $task['completed'] ? 'completed' : 'pending',
        'due_date' => $task['due_date'],
        'created_at' => $task['created_at'],
        'description' => $task['description']
    ];
}

// Process documents
foreach ($documents as $document) {
    $roadmap_items[] = [
        'id' => 'doc_' . $document['id'],
        'type' => 'document',
        'title' => $document['title'],
        'category' => $document['category'],
        'status' => $document['status'],
        'due_date' => $document['due_date'],
        'created_at' => $document['created_at'],
        'description' => $document['description']
    ];
}

// Sort roadmap items by due date (or created date if no due date)
usort($roadmap_items, function($a, $b) {
    $dateA = !empty($a['due_date']) ? $a['due_date'] : $a['created_at'];
    $dateB = !empty($b['due_date']) ? $b['due_date'] : $b['created_at'];
    return strtotime($dateA) - strtotime($dateB);
});

// Group items by category for the roadmap
$grouped_items = [
    'school' => [],
    'high-school' => [],
    'university' => [],
    'bachelor' => [],
    'master' => [],
    'phd' => [],
    'job' => [],  
    'other' => [], 
    'documents' => [] // For all document types
];

foreach ($roadmap_items as $item) {
    if ($item['type'] === 'document') {
        $grouped_items['documents'][] = $item;
    } else {
        if (isset($grouped_items[$item['category']])) {
            $grouped_items[$item['category']][] = $item;
        } else {
            $grouped_items['other'][] = $item;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Career Roadmap | CareerHigh</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/Roadmap.css"> <!-- the link should be changed if css file is not loaded -->
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
            <a href="#" class="menu-item">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
            <a href="UniversityFilter.php" class="menu-item">
                <i class="fa fa-university" aria-hidden="true"></i>
                <span>Search University</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-book"></i>
                <span>Exam</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-chart-line"></i>
                <span>Progress</span>
            </a>
            <a href="MentorFilter.php" class="menu-item">
                <i class="fa-solid fa-chalkboard-user"></i>
                <span>Mentors Available</span>
            </a>
            <a href="task.php" class="menu-item">
                <i class="fas fa-tasks"></i>
                <span>Career Tasks</span>
            </a>
            <a href="document.php" class="menu-item">
                <i class="fas fa-file-alt"></i>
                <span>Documents</span>
            </a>
            <a href="roadmap.php" class="menu-item active">
                <i class="fas fa-road"></i>
                <span>Roadmap</span>
            </a>
        </div>
        <div class="sidebar-footer">
            <span>CareerHigh v1.0</span>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <header>
                <h1>Your Career Roadmap</h1>
                <p class="subtitle">Visualize your journey from school to career</p>
            </header>
            
            <!-- Roadmap Visualization -->
            <div class="roadmap-container">
                <div class="roadmap-timeline">
                    <!-- School Phase -->
                    <div class="timeline-phase" id="school">
                        <div class="phase-header">
                            <div class="phase-icon">
                                <i class="fas fa-pencil-alt"></i>
                            </div>
                            <h2>School</h2>
                        </div>
                        <div class="phase-items">
                            <?php foreach ($grouped_items['school'] as $item): ?>
                                <div class="roadmap-item <?php echo $item['status']; ?>">
                                    <div class="item-icon">
                                        <i class="fas fa-<?php echo $item['type'] === 'task' ? 'tasks' : 'file-alt'; ?>"></i>
                                    </div>
                                    <div class="item-content">
                                        <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                        <div class="item-status"><?php echo ucfirst($item['status']); ?></div>
                                        <?php if (!empty($item['due_date'])): ?>
                                            <div class="item-due">
                                                <i class="fas fa-calendar"></i>
                                                <?php echo date('M j, Y', strtotime($item['due_date'])); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- High School Phase -->
                    <div class="timeline-phase" id="high-school">
                        <div class="phase-header">
                            <div class="phase-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <h2>High School</h2>
                        </div>
                        <div class="phase-items">
                            <?php foreach ($grouped_items['high-school'] as $item): ?>
                                <div class="roadmap-item <?php echo $item['status']; ?>">
                                    <div class="item-icon">
                                        <i class="fas fa-<?php echo $item['type'] === 'task' ? 'tasks' : 'file-alt'; ?>"></i>
                                    </div>
                                    <div class="item-content">
                                        <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                        <div class="item-status"><?php echo ucfirst($item['status']); ?></div>
                                        <?php if (!empty($item['due_date'])): ?>
                                            <div class="item-due">
                                                <i class="fas fa-calendar"></i>
                                                <?php echo date('M j, Y', strtotime($item['due_date'])); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- University Phase -->
                    <div class="timeline-phase" id="university">
                        <div class="phase-header">
                            <div class="phase-icon">
                                <i class="fas fa-university"></i>
                            </div>
                            <h2>University</h2>
                        </div>
                        <div class="phase-items">
                            <?php foreach ($grouped_items['university'] as $item): ?>
                                <div class="roadmap-item <?php echo $item['status']; ?>">
                                    <div class="item-icon">
                                        <i class="fas fa-<?php echo $item['type'] === 'task' ? 'tasks' : 'file-alt'; ?>"></i>
                                    </div>
                                    <div class="item-content">
                                        <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                        <div class="item-status"><?php echo ucfirst($item['status']); ?></div>
                                        <?php if (!empty($item['due_date'])): ?>
                                            <div class="item-due">
                                                <i class="fas fa-calendar"></i>
                                                <?php echo date('M j, Y', strtotime($item['due_date'])); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Bachelor Phase -->
                    <div class="timeline-phase" id="bachelor">
                        <div class="phase-header">
                            <div class="phase-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <h2>Bachelor's Degree</h2>
                        </div>
                        <div class="phase-items">
                            <?php foreach ($grouped_items['bachelor'] as $item): ?>
                                <div class="roadmap-item <?php echo $item['status']; ?>">
                                    <div class="item-icon">
                                        <i class="fas fa-<?php echo $item['type'] === 'task' ? 'tasks' : 'file-alt'; ?>"></i>
                                    </div>
                                    <div class="item-content">
                                        <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                        <div class="item-status"><?php echo ucfirst($item['status']); ?></div>
                                        <?php if (!empty($item['due_date'])): ?>
                                            <div class="item-due">
                                                <i class="fas fa-calendar"></i>
                                                <?php echo date('M j, Y', strtotime($item['due_date'])); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Master Phase -->
                    <div class="timeline-phase" id="master">
                        <div class="phase-header">
                            <div class="phase-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <h2>Master's Degree</h2>
                        </div>
                        <div class="phase-items">
                            <?php foreach ($grouped_items['master'] as $item): ?>
                                <div class="roadmap-item <?php echo $item['status']; ?>">
                                    <div class="item-icon">
                                        <i class="fas fa-<?php echo $item['type'] === 'task' ? 'tasks' : 'file-alt'; ?>"></i>
                                    </div>
                                    <div class="item-content">
                                        <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                        <div class="item-status"><?php echo ucfirst($item['status']); ?></div>
                                        <?php if (!empty($item['due_date'])): ?>
                                            <div class="item-due">
                                                <i class="fas fa-calendar"></i>
                                                <?php echo date('M j, Y', strtotime($item['due_date'])); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- PhD Phase -->
                    <div class="timeline-phase" id="phd">
                        <div class="phase-header">
                            <div class="phase-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <h2>PhD</h2>
                        </div>
                        <div class="phase-items">
                            <?php foreach ($grouped_items['phd'] as $item): ?>
                                <div class="roadmap-item <?php echo $item['status']; ?>">
                                    <div class="item-icon">
                                        <i class="fas fa-<?php echo $item['type'] === 'task' ? 'tasks' : 'file-alt'; ?>"></i>
                                    </div>
                                    <div class="item-content">
                                        <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                        <div class="item-status"><?php echo ucfirst($item['status']); ?></div>
                                        <?php if (!empty($item['due_date'])): ?>
                                            <div class="item-due">
                                                <i class="fas fa-calendar"></i>
                                                <?php echo date('M j, Y', strtotime($item['due_date'])); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Job Phase -->
                    <div class="timeline-phase" id="job">
                        <div class="phase-header">
                            <div class="phase-icon">
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <h2>Job/Career</h2>
                        </div>
                        <div class="phase-items">
                            <?php foreach ($grouped_items['job'] as $item): ?>
                                <div class="roadmap-item <?php echo $item['status']; ?>">
                                    <div class="item-icon">
                                        <i class="fas fa-<?php echo $item['type'] === 'task' ? 'tasks' : 'file-alt'; ?>"></i>
                                    </div>
                                    <div class="item-content">
                                        <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                        <div class="item-status"><?php echo ucfirst($item['status']); ?></div>
                                        <?php if (!empty($item['due_date'])): ?>
                                            <div class="item-due">
                                                <i class="fas fa-calendar"></i>
                                                <?php echo date('M j, Y', strtotime($item['due_date'])); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Documents Section -->
                    <div class="timeline-phase" id="documents">
                        <div class="phase-header">
                            <div class="phase-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <h2>Important Documents</h2>
                        </div>
                        <div class="phase-items">
                            <?php foreach ($grouped_items['documents'] as $item): ?>
                                <div class="roadmap-item <?php echo $item['status']; ?>">
                                    <div class="item-icon">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                    <div class="item-content">
                                        <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                        <div class="item-status"><?php echo ucfirst($item['status']); ?></div>
                                        <?php if (!empty($item['due_date'])): ?>
                                            <div class="item-due">
                                                <i class="fas fa-calendar"></i>
                                                <?php echo date('M j, Y', strtotime($item['due_date'])); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Progress Overview -->
            <div class="progress-overview">
                <h2>Progress Overview</h2>
                <div class="progress-stats">
                    <?php
                    $total_items = count($roadmap_items);
                    $completed_items = 0;
                    
                    foreach ($roadmap_items as $item) {
                        if ($item['status'] === 'completed') {
                            $completed_items++;
                        }
                    }
                    
                    $completion_percentage = $total_items > 0 ? round(($completed_items / $total_items) * 100) : 0;
                    ?>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $completion_percentage; ?>%"></div>
                    </div>
                    <div class="progress-text">
                        <span><?php echo $completion_percentage; ?>% Complete</span>
                        <span><?php echo $completed_items; ?> of <?php echo $total_items; ?> items completed</span>
                    </div>
                </div>
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
        
        // Add animation to roadmap items when they come into view
        document.addEventListener('DOMContentLoaded', function() {
            const roadmapItems = document.querySelectorAll('.roadmap-item');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate');
                    }
                });
            }, { threshold: 0.1 });
            
            roadmapItems.forEach(item => {
                observer.observe(item);
            });
        });
    </script>
</body>
</html>