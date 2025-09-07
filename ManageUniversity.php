<?php
// session_start();
require_once("connect.php");


$servername = 'localhost';
$username = "root";
$password = "";
$dbname = "project_database";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8");

$message = "";
$universities = [];
$editMode = false;
$editData = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action == 'add') {
            $name = trim($_POST['name']);
            $country = trim($_POST['country']);
            $department = trim($_POST['department']);
            $funding = floatval($_POST['funding']);
            $last_date = $_POST['last_date'];
            $email = trim($_POST['email']);
            
            $sql = "INSERT INTO University (Name, Department, Funding, admission_Email, Last_Date, Country) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssdsss", $name, $department, $funding, $email, $last_date, $country);
            
            if ($stmt->execute()) {
                $message = "University added successfully!";
                header("Location: ManageUniversity.php?success=added");
                exit();
            } else {
                $message = "Error adding university: " . $conn->error;
            }
            $stmt->close();
        }
        
        if ($action == 'update') {
            $id = intval($_POST['id']);
            $name = trim($_POST['name']);
            $country = trim($_POST['country']);
            $department = trim($_POST['department']);
            $funding = floatval($_POST['funding']);
            $last_date = $_POST['last_date'];
            $email = trim($_POST['email']);
            
            $sql = "UPDATE University SET Name=?, Department=?, Funding=?, admission_Email=?, Last_Date=?, Country=? WHERE ID=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssdsssi", $name, $department, $funding, $email, $last_date, $country, $id);
            
            if ($stmt->execute()) {
                $message = "University updated successfully!";
                header("Location: ManageUniversity.php?success=updated");
                exit();
            } else {
                $message = "Error updating university: " . $conn->error;
            }
            $stmt->close();
        }
        
        if ($action == 'delete') {
            $id = intval($_POST['id']);
            
            $sql = "DELETE FROM University WHERE ID=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $message = "University deleted successfully!";
                header("Location: ManageUniversity.php?success=deleted");
                exit();
            } else {
                $message = "Error deleting university: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

// Handle edit request
if (isset($_GET['edit'])) {
    $editMode = true;
    $id = intval($_GET['edit']);
    
    $sql = "SELECT * FROM University WHERE ID=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $editData = $result->fetch_assoc();
    }
    $stmt->close();
}

// Get all universities
$sql = "SELECT * FROM University ORDER BY Name";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $universities[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Universities | CareerHigh</title>
    <link rel="icon" type="image/png" href="img/Landing_logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/University Filteration.css">
</head>
<body>
    <!-- Sidebar -->
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
            <a href="ManageUniversity.php" class="menu-item">
                <i class="fa fa-university" aria-hidden="true"></i>
                <span>Manage Universities</span>
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

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <header>
                <h1><?php echo $editMode ? 'Edit University' : 'Manage Universities'; ?></h1>
                <p class="subtitle">Add, edit, or delete university information</p>
            </header>
            
            <?php if ($message): ?>
                <div class="message" style="padding: 15px; margin: 20px 0; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; color: #155724;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Add/Edit Form -->
            <form class="filter-form" method="POST" action="ManageUniversity.php">
                <input type="hidden" name="action" value="<?php echo $editMode ? 'update' : 'add'; ?>">
                <?php if ($editMode): ?>
                    <input type="hidden" name="id" value="<?php echo $editData['ID']; ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name"><i class="fas fa-university"></i> University Name</label>
                        <input type="text" id="name" name="name" required 
                               value="<?php echo $editMode ? htmlspecialchars($editData['Name']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="country"><i class="fas fa-globe"></i> Country</label>
                        <input type="text" id="country" name="country" required 
                               value="<?php echo $editMode ? htmlspecialchars($editData['Country']) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="department"><i class="fas fa-graduation-cap"></i> Department</label>
                        <input type="text" id="department" name="department" required 
                               value="<?php echo $editMode ? htmlspecialchars($editData['Department']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="funding"><i class="fas fa-money-bill-wave"></i> Funding Amount</label>
                        <input type="number" id="funding" name="funding" step="0.01" required 
                               value="<?php echo $editMode ? $editData['Funding'] : ''; ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="last_date"><i class="fas fa-calendar-alt"></i> Application Deadline</label>
                        <input type="date" id="last_date" name="last_date" required 
                               value="<?php echo $editMode ? $editData['Last_Date'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> Admission Email</label>
                        <input type="email" id="email" name="email" required 
                               value="<?php echo $editMode ? htmlspecialchars($editData['admission_Email']) : ''; ?>">
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn-submit">
                        <?php echo $editMode ? 'Update University' : 'Add University'; ?>
                    </button>
                    <?php if ($editMode): ?>
                        <a href="ManageUniversity.php" class="btn-submit" style="background: #6c757d; text-decoration: none; text-align: center;">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
            
            <!-- Universities List -->
            <div class="results-section visible">
                <div class="results-header">
                    <h2 class="results-title">All Universities</h2>
                    <div class="results-count">Total: <?php echo count($universities); ?></div>
                </div>
                
                <div class="results-grid">
                    <?php if (!empty($universities)): ?>
                        <?php foreach ($universities as $university): ?>
                            <div class="result-card">
                                <h3 class="scholarship-name"><?php echo htmlspecialchars($university['Name']); ?></h3>
                                <div class="scholarship-details">
                                    <div class="scholarship-detail">
                                        <i class="fas fa-globe"></i>
                                        <span><?php echo htmlspecialchars($university['Country']); ?></span>
                                    </div>
                                    <div class="scholarship-detail">
                                        <i class="fas fa-hashtag"></i>
                                        <span>ID: <?php echo $university['ID']; ?></span>
                                    </div>
                                    <div class="scholarship-detail">
                                        <i class="fas fa-graduation-cap"></i>
                                        <span><?php echo htmlspecialchars($university['Department']); ?></span>
                                    </div>
                                    <div class="scholarship-detail">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <span>$<?php echo number_format($university['Funding'], 2); ?></span>
                                    </div>
                                    <div class="scholarship-detail">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span><?php echo date('F j, Y', strtotime($university['Last_Date'])); ?></span>
                                    </div>
                                    <div class="scholarship-detail">
                                        <i class="fas fa-envelope"></i>
                                        <span><?php echo htmlspecialchars($university['admission_Email']); ?></span>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 10px; margin-top: 15px;">
                                    <a href="ManageUniversity.php?edit=<?php echo $university['ID']; ?>" class="apply-btn" style="background: #28a745;">Edit</a>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this university?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $university['ID']; ?>">
                                        <button type="submit" class="apply-btn" style="background: #dc3545;">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-results">
                            <i class="fas fa-university"></i>
                            <h3>No Universities Found</h3>
                            <p>Start by adding your first university above.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="copyright">
            © 2025 CareerHigh. All rights reserved.
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