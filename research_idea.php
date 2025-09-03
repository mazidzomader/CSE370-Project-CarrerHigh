<?php
// Start session and connect DB
session_start();
$conn = new mysqli("localhost", "root", "", "research_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ---------- ADD IDEA ----------
if (isset($_POST['add_idea'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $date = date("Y-m-d");
    $status = "In Progress";

    $sql = "INSERT INTO ideas (title, description, date, status) 
            VALUES ('$title', '$description', '$date', '$status')";
    $conn->query($sql);
    header("Location: research_idea.php");
    exit();
}

// ---------- UPDATE IDEA ----------
if (isset($_POST['update_idea'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $description = $_POST['description'];

    $sql = "UPDATE ideas SET title='$title', description='$description' WHERE id=$id";
    $conn->query($sql);
    header("Location: research_idea.php");
    exit();
}

// ---------- COMPLETE IDEA ----------
if (isset($_GET['complete'])) {
    $id = $_GET['complete'];
    $date = date("Y-m-d");
    $conn->query("INSERT INTO completed (title, completed_date) 
                  SELECT title, '$date' FROM ideas WHERE id=$id");
    $conn->query("DELETE FROM ideas WHERE id=$id");
    header("Location: research_idea.php");
    exit();
}

// ---------- DELETE IDEA ----------
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM ideas WHERE id=$id");
    header("Location: research_idea.php");
    exit();
}

// ---------- DELETE COMPLETED ----------
if (isset($_GET['delete_completed'])) {
    $id = $_GET['delete_completed'];
    $conn->query("DELETE FROM completed WHERE id=$id");
    header("Location: research_idea.php");
    exit();
}

// ---------- FETCH DATA ----------
$ideas = $conn->query("SELECT * FROM ideas ORDER BY date DESC");
$completed = $conn->query("SELECT * FROM completed ORDER BY completed_date DESC");

// If editing, fetch idea data
$editIdea = null;
if (isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    $editIdea = $conn->query("SELECT * FROM ideas WHERE id=$editId")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Research Idea</title>
    <link rel="stylesheet" href="css/research_idea.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
            <a href="#" class="menu-item">
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
            <a href="research_idea.php" class="menu-item">
                <i class="fa-regular fa-lightbulb"></i>
                <span>Research_idea</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-chart-line"></i>
                <span>Progress</span>
            </a>
            <a href="MentorFilter.php" class="menu-item">
                <i class="fa-solid fa-chalkboard-user"></i>
                <span>Mentors Available</span>
            </a>
        </div>
        <div class="sidebar-footer">
            <span>CareerHigh v1.0</span>
        </div>
    </div>  

    <div class="container">
        <header>
            <h1>Research Idea</h1>
            <p class="subtitle">Manage your research ideas</p>
        </header>

        <!-- Add / Edit Form -->
        <form method="POST" class="research-form">
            <h2><?= $editIdea ? "Edit Idea" : "Add New Idea" ?></h2>
            
            <input type="hidden" name="id" value="<?= $editIdea['id'] ?? '' ?>">

            <label>Idea Title:</label>
            <input type="text" name="title" id="idea-title" 
                   value="<?= $editIdea['title'] ?? '' ?>" required>

            <label>Description:</label>
            <textarea name="description" id="idea-description" required><?= $editIdea['description'] ?? '' ?></textarea>

            <?php if ($editIdea): ?>
                <button type="submit" name="update_idea" class="btn-submit">Update Idea</button>
                <a href="research_idea.php" class="btn-cancel">Cancel</a>
            <?php else: ?>
                <button type="submit" name="add_idea" class="btn-submit">Add Idea</button>
            <?php endif; ?>
        </form>

        <!-- My Ideas -->
        <div class="research-tables">
            <h2>My Ideas</h2>
            <table class="ideas-table">
                <thead>
                    <tr>
                        <th>SL.NO</th>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $i = 1;
                    while ($row = $ideas->fetch_assoc()) { ?>
                        <tr>
                            <td><?= $i++; ?></td>
                            <td><?= htmlspecialchars($row['title']); ?></td>
                            <td><?= $row['date']; ?></td>
                            <td><?= $row['status']; ?></td>
                            <td>
                                <a href="?edit=<?= $row['id']; ?>"><i class="fa-solid fa-pen action-icon"></i></a>
                                <a href="?complete=<?= $row['id']; ?>"><i class="fa-solid fa-check action-icon"></i></a>
                                <a href="?delete=<?= $row['id']; ?>"><i class="fa-solid fa-trash action-icon"></i></a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <!-- Completed Research -->
            <h2>Completed Research</h2>
            <table class="ideas-table">
                <thead>
                    <tr>
                        <th>SL.NO</th>
                        <th>Title</th>
                        <th>Completed Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $j = 1;
                    while ($row = $completed->fetch_assoc()) { ?>
                        <tr>
                            <td><?= $j++; ?></td>
                            <td><?= htmlspecialchars($row['title']); ?></td>
                            <td><?= $row['completed_date']; ?></td>
                            <td>
                                <a href="?delete_completed=<?= $row['id']; ?>"><i class="fa-solid fa-trash action-icon"></i></a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

