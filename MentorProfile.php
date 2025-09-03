<?php
require_once("connect.php");
// session_start();

// Ensure mentor is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Mentor') {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['user_id'];
$msg = "";

// ---------- Handle Form Submission ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $availability = $_POST['Availability_Schedule'] ?? '';
    $remuneration = $_POST['Remuneration'] ?? 0;
    $rating       = $_POST['Rating'] ?? 0;
    $expertiseArr = $_POST['Expertise'] ?? [];
    $langArr      = $_POST['Languages'] ?? [];

    // Check if mentor profile exists
    $check = $conn->prepare("SELECT UserID FROM mentor WHERE UserID=?");
    $check->bind_param("i", $userID);
    $check->execute();
    $res = $check->get_result();
    $mentorExists = $res->num_rows > 0;
    $check->close();

    if ($mentorExists) {
        $update = $conn->prepare("UPDATE mentor 
            SET Availability_Schedule=?, Remuneration=?, Rating=? 
            WHERE UserID=?");
        $update->bind_param("sddi", $availability, $remuneration, $rating, $userID);
        $update->execute();
        $update->close();
    } else {
        $insert = $conn->prepare("INSERT INTO mentor (UserID, Availability_Schedule, Remuneration, Rating) 
            VALUES (?, ?, ?, ?)");
        $insert->bind_param("isdd", $userID, $availability, $remuneration, $rating);
        $insert->execute();
        $insert->close();
    }

    // Update Expertise
    $delExp = $conn->prepare("DELETE FROM mentor_expertise WHERE MentorID=?");
    $delExp->bind_param("i", $userID);
    $delExp->execute();
    $delExp->close();

    if (!empty($expertiseArr)) {
        $insExp = $conn->prepare("INSERT INTO mentor_expertise (MentorID, ExpertiseArea) VALUES (?, ?)");
        foreach ($expertiseArr as $exp) {
            if (!empty(trim($exp))) {
                $insExp->bind_param("is", $userID, $exp);
                $insExp->execute();
            }
        }
        $insExp->close();
    }

    // Update Languages
    $delLang = $conn->prepare("DELETE FROM mentor_languages WHERE MentorID=?");
    $delLang->bind_param("i", $userID);
    $delLang->execute();
    $delLang->close();

    if (!empty($langArr)) {
        $insLang = $conn->prepare("INSERT INTO mentor_languages (MentorID, Language) VALUES (?, ?)");
        foreach ($langArr as $lang) {
            if (!empty(trim($lang))) {
                $insLang->bind_param("is", $userID, $lang);
                $insLang->execute();
            }
        }
        $insLang->close();
    }

    // Redirect to dashboard after save
    header("Location: mentordash.php");
    exit();
}

// ---------- Fetch Current Mentor Data ----------
$mentor = [
    "Availability_Schedule" => "",
    "Remuneration" => "",
    "Rating" => ""
];

$stmt = $conn->prepare("SELECT Availability_Schedule, Remuneration, Rating FROM mentor WHERE UserID=?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $mentor = $result->fetch_assoc();
}
$stmt->close();

// Fetch Expertise
$expertise = [];
$res = $conn->prepare("SELECT ExpertiseArea FROM mentor_expertise WHERE MentorID=?");
$res->bind_param("i", $userID);
$res->execute();
$res->bind_result($exp);
while ($res->fetch()) {
    $expertise[] = $exp;
}
$res->close();

// Fetch Languages
$languages = [];
$res = $conn->prepare("SELECT Language FROM mentor_languages WHERE MentorID=?");
$res->bind_param("i", $userID);
$res->execute();
$res->bind_result($lang);
while ($res->fetch()) {
    $languages[] = $lang;
}
$res->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mentor Profile</title>
    <link rel="stylesheet" href="css/MentorProfile.css">
    <script>
        function addExpertise() {
            const container = document.getElementById('expertise-container');
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'Expertise[]';
            input.placeholder = 'Enter expertise';
            container.appendChild(input);
        }
        function addLanguage() {
            const container = document.getElementById('languages-container');
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'Languages[]';
            input.placeholder = 'Enter language';
            container.appendChild(input);
        }
    </script>
</head>
<body>
<div class="container">
    <h2>Mentor Profile Editing</h2>

    <form method="post" class="mentor-form">
        <label>Availability Schedule</label>
        <textarea name="Availability_Schedule" rows="3"><?php echo htmlspecialchars($mentor['Availability_Schedule']); ?></textarea>

        <label>Remuneration</label>
        <input type="number" step="0.01" name="Remuneration" value="<?php echo htmlspecialchars($mentor['Remuneration']); ?>">

        <label>Rating</label>
        <input type="number" step="0.1" max="5" name="Rating" value="<?php echo htmlspecialchars($mentor['Rating']); ?>">

        <label>Expertise Areas</label>
        <div id="expertise-container" class="expertise-box">
            <?php if (!empty($expertise)): ?>
                <?php foreach ($expertise as $exp): ?>
                    <input type="text" name="Expertise[]" value="<?php echo htmlspecialchars($exp); ?>">
                <?php endforeach; ?>
            <?php else: ?>
                <input type="text" name="Expertise[]" placeholder="Enter expertise">
            <?php endif; ?>
        </div>
        <button type="button" class="btn-secondary" onclick="addExpertise()">+ Add Expertise</button>

        <label>Languages</label>
        <div id="languages-container" class="languages-box">
            <?php if (!empty($languages)): ?>
                <?php foreach ($languages as $lang): ?>
                    <input type="text" name="Languages[]" value="<?php echo htmlspecialchars($lang); ?>">
                <?php endforeach; ?>
            <?php else: ?>
                <input type="text" name="Languages[]" placeholder="Enter language">
            <?php endif; ?>
        </div>
        <button type="button" class="btn-secondary" onclick="addLanguage()">+ Add Language</button>

        <button type="submit" id="save-btn" class="btn-primary">Save</button>
    </form>
</div>
</body>
</html>
