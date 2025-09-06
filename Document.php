<?php
require_once("connect.php");
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";
$errorMessage = "";

// Handle document creation
$message = "";
if (isset($_GET['added']) && $_GET['added'] == 1) { 
    $message = "Document added successfully!";
} 
if (isset($_GET['deleted']) && $_GET['deleted'] == 1) {
    $message = "Document deleted successfully!";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_document'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $status = trim($_POST['status']);
    $due_date = !empty($_POST['due_date']) ? trim($_POST['due_date']) : null;

    if (!empty($title) && !empty($category)) {
        // Prepare and execute the INSERT query
        $sql = "INSERT INTO documents (user_id, title, description, category, status, due_date) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "isssss", $_SESSION['user_id'], $title, $description, $category, $status, $due_date);
            
            if (mysqli_stmt_execute($stmt)) {
                $message = "Document added successfully!";
                header("Location: document.php?added=1");
                exit();
            } else {
                $errorMessage = "Error adding document: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        } else {
            $errorMessage = "Database error: " . mysqli_error($conn);
        }
    } else {
        $errorMessage = "Title and category are required!";
    }
}

// Handle document deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $sql = "DELETE FROM documents WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ii", $delete_id, $_SESSION['user_id']);
        
        if (mysqli_stmt_execute($stmt)) {
            $message = "Document deleted successfully!";
            header("Location: document.php?deleted=1");
                exit();
        } else {
            $errorMessage = "Error deleting document: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        $errorMessage = "Database error: " . mysqli_error($conn);
    }
}

// Handle status toggle
if (isset($_GET['toggle_id'])) {
    $toggle_id = $_GET['toggle_id'];
    $sql = "UPDATE documents 
            SET status = IF(status = 'completed', 'pending', 'completed') 
            WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ii", $toggle_id, $_SESSION['user_id']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header("Location: document.php");
        exit();
    }
}

// Fetch all documents
$documents = [];
$sql = "SELECT * FROM documents 
        WHERE user_id = ?
        ORDER BY 
        FIELD(category, 'academic', 'grade_sheet', 'exam_result', 'competition', 'co_curricular', 'achievement', 'research', 'birth_certificate', 'passport', 'bank', 'other'),
        status, created_at DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $documents[] = $row;
    }
}

// Count documents by status
$pending_count = 0;
$completed_count = 0;
foreach ($documents as $doc) {
    if ($doc['status'] == 'pending') {
        $pending_count++;
    } else {
        $completed_count++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Manager | CareerHigh</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/Documents.css"> <!-- the link should be changed if css file is not loaded -->
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
                <h1>Document Manager</h1>
                <p class="subtitle">Track all your important documents in one place</p>
            </header>
            
            <!-- Stats Overview -->
            <div class="stats-overview">
                <div class="stat-card">
                    <div class="stat-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $pending_count; ?></h3>
                        <p>Pending Documents</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon completed">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $completed_count; ?></h3>
                        <p>Completed Documents</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon total">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count($documents); ?></h3>
                        <p>Total Documents</p>
                    </div>
                </div>
            </div>
            
            <!-- Add Document Form -->
            <form class="document-form" method="POST" action="document.php">
                <div class="form-row">
                    <div class="form-group">
                        <label for="title"><i class="fas fa-heading"></i> Document Title</label>
                        <input type="text" id="title" name="title" placeholder="e.g., High School Diploma" required>
                    </div>
                </div>
                <div class="form-group">
                        <label for="category"><i class="fas fa-layer-group"></i> Category</label>
                        <select id="category" name="category" required>
                            <option value="">Select a category</option>
                            <option value="academic">Academic Certificate</option>
                            <option value="grade_sheet">Grade Sheet/Transcript</option>
                            <option value="exam_result">Exam Result</option>
                            <option value="competition">Competition Certificate</option>
                            <option value="co_curricular">Co-curricular Activity</option>
                            <option value="achievement">Achievement/Award</option>
                            <option value="research">Research Paper</option>
                            <option value="birth_certificate">Birth Certificate</option>
                            <option value="passport">Passport</option>
                            <option value="bank">Bank Documents</option>
                            <option value="other">Other Document</option>
                        </select>
                    </div>
                    <br>
                <div class="form-row">
                    <div class="form-group">
                        <label for="description"><i class="fas fa-align-left"></i> Description (Optional)</label>
                        <textarea id="description" name="description" placeholder="Add details about this document..."></textarea>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="status"><i class="fas fa-check-circle"></i> Status</label>
                        <select id="status" name="status" required>
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="due_date"><i class="fas fa-calendar-day"></i> Due Date (Optional)</label>
                        <input type="date" id="due_date" name="due_date">
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" name="add_document" class="btn-submit">Add Document</button>
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
            
            <!-- Documents List -->
            <div class="documents-section">
                <div class="documents-header">
                    <h2 class="documents-title">Your Documents</h2>
                    <div class="documents-filter">
                        <span>Filter:</span>
                        <select id="categoryFilter">
                            <option value="all">All Categories</option>
                            <option value="academic">Academic</option>
                            <option value="grade_sheet">Grade Sheets</option>
                            <option value="exam_result">Exam Results</option>
                            <option value="competition">Competitions</option>
                            <option value="co_curricular">Co-curricular</option>
                            <option value="achievement">Achievements</option>
                            <option value="research">Research</option>
                            <option value="birth_certificate">Birth Certificate</option>
                            <option value="passport">Passport</option>
                            <option value="bank">Bank</option>
                            <option value="other">Other</option>
                        </select>
                        <select id="statusFilter">
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>
                
                <?php if (count($documents) > 0): ?>
                    <div class="documents-list">
                        <?php foreach ($documents as $doc): ?>
                            <div class="document-card <?php echo $doc['status']; ?>" data-category="<?php echo $doc['category']; ?>">
                                <div class="document-status">
                                    <a href="document.php?toggle_id=<?php echo $doc['id']; ?>" class="status-btn <?php echo $doc['status']; ?>">
                                        <i class="fas fa-<?php echo $doc['status'] == 'completed' ? 'check-circle' : 'circle'; ?>"></i>
                                        <span><?php echo ucfirst($doc['status']); ?></span>
                                    </a>
                                </div>
                                <div class="document-content">
                                    <h3 class="document-title"><?php echo htmlspecialchars($doc['title']); ?></h3>
                                    <div class="document-category"><?php echo ucfirst(str_replace('_', ' ', $doc['category'])); ?></div>
                                    <?php if (!empty($doc['description'])): ?>
                                        <p class="document-description"><?php echo htmlspecialchars($doc['description']); ?></p>
                                    <?php endif; ?>
                                    <div class="document-meta">
                                        <?php if (!empty($doc['due_date'])): ?>
                                            <span class="document-due">
                                                <i class="fas fa-calendar"></i>
                                                Due: <?php echo date('M j, Y', strtotime($doc['due_date'])); ?>
                                            </span>
                                        <?php endif; ?>
                                        <span class="document-created">
                                            <i class="fas fa-plus-circle"></i>
                                            Added: <?php echo date('M j, Y', strtotime($doc['created_at'])); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="document-actions">
                                    <a href="document.php?delete_id=<?php echo $doc['id']; ?>" class="delete-btn" 
                                       onclick="return confirm('Are you sure you want to delete this document?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-documents">
                        <i class="fas fa-folder-open"></i>
                        <h3>No Documents Yet</h3>
                        <p>Start tracking your important documents by adding them above.</p>
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
        
        // Filter functionality
        const categoryFilter = document.getElementById('categoryFilter');
        const statusFilter = document.getElementById('statusFilter');
        const documentCards = document.querySelectorAll('.document-card');
        
        function filterDocuments() {
            const categoryValue = categoryFilter.value;
            const statusValue = statusFilter.value;
            
            documentCards.forEach(card => {
                const cardCategory = card.getAttribute('data-category');
                const cardStatus = card.classList.contains('completed') ? 'completed' : 'pending';
                
                const categoryMatch = categoryValue === 'all' || cardCategory === categoryValue;
                const statusMatch = statusValue === 'all' || cardStatus === statusValue;
                
                if (categoryMatch && statusMatch) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }
        
        categoryFilter.addEventListener('change', filterDocuments);
        statusFilter.addEventListener('change', filterDocuments);
        
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

