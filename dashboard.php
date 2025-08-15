<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Student Portal</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="bg-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <!-- Header Section -->
    <header class="dashboard-header">
        <div class="header-content">
            <div class="logo-section">
                <a href="index.php" class="logo-link">
                    <img src="img/logo.png" alt="Company Logo" class="logo">
                </a>
            </div>
            <div class="user-menu">
                <div class="user-info">
                    <span>Welcome, John Doe</span>
                </div>
                <a href="login.php" class="cta-button">⏻Logout</a>
            </div>
        </div>
    </header>

    <!-- Main Dashboard Content -->
    <main class="dashboard-main">
        
        <!-- User Profile & Quick Access Section -->
        <section class="profile-quick-access">
            <div class="user-profile-card">
                <div class="profile-image">
                    <img src="img/anonymous.jpg" alt="User Profile" class="profile-img">
                    <!-- <button class="edit-profile-img">
                        <i class="fas fa-camera"></i>
                    </button> -->
                </div>
                <div class="profile-info">
                    <h2>John Doe</h2>
                    <p class="user-email">john.doe@example.com</p>
                    <p class="user-program">Computer Science - Fall 2024</p>
                </div>
                <button class="edit-btn">
                    <i class="fas fa-edit"></i>
                    Edit Profile
                </button>
            </div>

            <div class="quick-access-cards">
                <div class="quick-card">
                    <div class="quick-icon">
                        <i class="fas fa-route"></i>
                    </div>
                    <h3>Predefined Roadmap</h3>
                    <p>Follow your personalized study plan</p>
                    <button class="quick-btn">View Roadmap</button>
                </div>

                <div class="quick-card">
                    <div class="quick-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Find Mentors</h3>
                    <p>Connect with experienced mentors</p>
                    <button class="quick-btn">Browse Mentors</button>
                </div>
            </div>
        </section>

        <!-- User Details & Mentor Details Section -->
        <section class="details-section">
            <div class="user-details-card blurred-container">
                <div class="card-header">
                    <h3><i class="fas fa-user"></i> Personal Details</h3>
                    <button class="edit-btn">
                        <i class="fas fa-edit"></i>
                        Edit
                    </button>
                </div>
                <div class="details-grid">
                    <div class="detail-item">
                        <label>Full Name:</label>
                        <span>John Doe</span>
                    </div>
                    <div class="detail-item">
                        <label>Date of Birth:</label>
                        <span>March 15, 1998</span>
                    </div>
                    <div class="detail-item">
                        <label>Phone Number:</label>
                        <span>+1 (555) 123-4567</span>
                    </div>
                    <div class="detail-item">
                        <label>Address:</label>
                        <span>123 Student Street, University City</span>
                    </div>
                    <div class="detail-item">
                        <label>Emergency Contact:</label>
                        <span>Jane Doe - +1 (555) 987-6543</span>
                    </div>
                    <div class="detail-item">
                        <label>Intended Major:</label>
                        <span>Computer Science</span>
                    </div>
                </div>
            </div>

            <div class="mentor-details-card blurred-container">
                <div class="card-header">
                    <h3><i class="fas fa-chalkboard-teacher"></i> Assigned Mentor</h3>
                    <button class="edit-btn">
                        <i class="fas fa-edit"></i>
                        Change
                    </button>
                </div>
                <div class="mentor-info">
                    <div class="mentor-avatar">
                        <img src="img/Sheikh-Hasina.jpg" alt="Mentor" class="mentor-img">
                    </div>
                    <div class="mentor-details">
                        <h4>Dr. Sarah Wilson</h4>
                        <p class="mentor-title">Senior Academic Advisor</p>
                        <p class="mentor-specialization">Computer Science & Engineering</p>
                        <div class="mentor-stats">
                            <span class="stat">
                                <i class="fas fa-star"></i>
                                4.9 Rating
                            </span>
                            <span class="stat">
                                <i class="fas fa-users"></i>
                                150+ Students
                            </span>
                        </div>
                        <div class="mentor-actions">
                            <button class="contact-btn">
                                <i class="fas fa-message"></i>
                                Message
                            </button>
                            <button class="schedule-btn">
                                <i class="fas fa-calendar"></i>
                                Schedule Call
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Roadmap Section -->
        <section class="roadmap-section">
            <div class="roadmap-card blurred-container">
                <div class="card-header">
                    <h3><i class="fas fa-map-marked-alt"></i> Your Study Roadmap</h3>
                    <button class="edit-btn">
                        <i class="fas fa-edit"></i>
                        Customize
                    </button>
                </div>
                <div class="roadmap-tasks">
                    <div class="task-category">
                        <h4>Application Preparation</h4>
                        <div class="tasks-list">
                            <label class="task-item">
                                <input type="checkbox">
                                <span class="checkmark"></span>
                                Task name
                            </label>

                            <label class="task-item completed">
                                <input type="checkbox" checked>
                                <span class="checkmark"></span>
                                Research Universities
                            </label>
                            <label class="task-item completed">
                                <input type="checkbox" checked>
                                <span class="checkmark"></span>
                                Prepare Personal Statement
                            </label>
                            <label class="task-item">
                                <input type="checkbox">
                                <span class="checkmark"></span>
                                Request Letters of Recommendation
                            </label>
                            <label class="task-item">
                                <input type="checkbox">
                                <span class="checkmark"></span>
                                Complete Application Forms
                            </label>
                        </div>
                    </div>
                    
                    <div class="task-category">
                        <h4>Test Preparation</h4>
                        <div class="tasks-list">
                            <label class="task-item completed">
                                <input type="checkbox" checked>
                                <span class="checkmark"></span>
                                IELTS Preparation
                            </label>
                            <label class="task-item">
                                <input type="checkbox">
                                <span class="checkmark"></span>
                                GRE Preparation
                            </label>
                            <label class="task-item">
                                <input type="checkbox">
                                <span class="checkmark"></span>
                                Practice Tests
                            </label>
                        </div>
                    </div>

                    <div class="task-category">
                        <h4>Financial Planning</h4>
                        <div class="tasks-list">
                            <label class="task-item">
                                <input type="checkbox">
                                <span class="checkmark"></span>
                                Research Scholarships
                            </label>
                            <label class="task-item">
                                <input type="checkbox">
                                <span class="checkmark"></span>
                                Prepare Financial Documents
                            </label>
                            <label class="task-item">
                                <input type="checkbox">
                                <span class="checkmark"></span>
                                Apply for Student Loans
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Deadlines, Documents & Activities Section -->
        <section class="bottom-section">
            <!-- Exam Deadlines -->
            <div class="deadlines-card blurred-container">
                <div class="card-header">
                    <h3><i class="fas fa-clock"></i> Upcoming Deadlines</h3>
                    <button class="edit-btn">
                        <i class="fas fa-edit"></i>
                        Edit
                    </button>
                </div>
                <div class="deadlines-list">
                    <div class="deadline-item urgent">
                        <div class="deadline-info">
                            <h4>GRE Exam</h4>
                            <p>General Test</p>
                        </div>
                        <div class="deadline-date">
                            <span class="date">Dec 15</span>
                            <span class="days">5 days</span>
                        </div>
                    </div>
                    <div class="deadline-item warning">
                        <div class="deadline-info">
                            <h4>University Application</h4>
                            <p>Stanford University</p>
                        </div>
                        <div class="deadline-date">
                            <span class="date">Jan 1</span>
                            <span class="days">22 days</span>
                        </div>
                    </div>
                    <div class="deadline-item normal">
                        <div class="deadline-info">
                            <h4>FAFSA Submission</h4>
                            <p>Financial Aid</p>
                        </div>
                        <div class="deadline-date">
                            <span class="date">Feb 15</span>
                            <span class="days">67 days</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Co-curricular Activities -->
            <div class="activities-card blurred-container">
                <div class="card-header">
                    <h3><i class="fas fa-trophy"></i> Co-curricular Activities</h3>
                    <button class="edit-btn">
                        <i class="fas fa-edit"></i>
                        Edit
                    </button>
                </div>
                
                <div class="activities-section">
                    <h4>Suggested Activities</h4>
                    <div class="activities-list">
                        <label class="activity-item">
                            <input type="checkbox">
                            <span class="checkmark"></span>
                            Join Computer Science Club
                        </label>
                        <label class="activity-item">
                            <input type="checkbox">
                            <span class="checkmark"></span>
                            Participate in Hackathons
                        </label>
                        <label class="activity-item">
                            <input type="checkbox">
                            <span class="checkmark"></span>
                            Volunteer at Tech Events
                        </label>
                    </div>
                </div>

                <div class="activities-section">
                    <h4>Your Activities</h4>
                    <div class="activities-list">
                        <label class="activity-item completed">
                            <input type="checkbox" checked>
                            <span class="checkmark"></span>
                            Programming Club Member
                        </label>
                        <label class="activity-item completed">
                            <input type="checkbox" checked>
                            <span class="checkmark"></span>
                            Math Tutor (Volunteer)
                        </label>
                        <label class="activity-item">
                            <input type="checkbox">
                            <span class="checkmark"></span>
                            Research Assistant
                        </label>
                    </div>
                </div>
            </div>

            <!-- Document Checklist -->
            <div class="documents-card blurred-container">
                <div class="card-header">
                    <h3><i class="fas fa-file-check"></i> Document Status</h3>
                    <button class="edit-btn">
                        <i class="fas fa-edit"></i>
                        Update
                    </button>
                </div>
                <div class="documents-list">
                    <div class="document-item completed">
                        <i class="fas fa-check-circle"></i>
                        <span>Transcripts</span>
                        <div class="doc-status verified">Verified</div>
                    </div>
                    <div class="document-item completed">
                        <i class="fas fa-check-circle"></i>
                        <span>Personal Statement</span>
                        <div class="doc-status verified">Verified</div>
                    </div>
                    <div class="document-item pending">
                        <i class="fas fa-clock"></i>
                        <span>Letters of Recommendation</span>
                        <div class="doc-status pending">Pending</div>
                    </div>
                    <div class="document-item missing">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>Financial Documents</span>
                        <div class="doc-status missing">Missing</div>
                    </div>
                    <div class="document-item completed">
                        <i class="fas fa-check-circle"></i>
                        <span>Test Scores</span>
                        <div class="doc-status verified">Verified</div>
                    </div>
                    <div class="document-item pending">
                        <i class="fas fa-clock"></i>
                        <span>Resume/CV</span>
                        <div class="doc-status pending">Under Review</div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="dashboard-footer">
        <p>&copy; 2025 CarrerHigh Student Portal. All rights reserved.</p>
    </footer>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.task-item').forEach(function(task) {
        task.addEventListener('click', function() {
            task.classList.toggle('completed');
        });
    });
});
</script>

</body>
</html>

