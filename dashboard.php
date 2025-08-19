<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CareerHigh Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard1.css">
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
            <a href="#" class="menu-item">
                <i class="fas fa-graduation-cap"></i>
                <span>Courses</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-book"></i>
                <span>Exam</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-chart-line"></i>
                <span>Progress</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </div>
        <div class="sidebar-footer">
            <span>CareerHigh v1.0</span>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <header>
            <div class="notification-wrapper">
                <button class="notification-btn">
                    <i class="far fa-bell"></i>
                    <div class="notification-alert"></div>
                </button>
                <div class="notification-box">
                    <h3>Notifications</h3>
                    <div class="notification-item">
                        <p>Your exam schedule has been updated.</p>
                        <small>10 minutes ago</small>
                    </div>
                    <div class="notification-item">
                        <p>New mentor message received.</p>
                        <small>45 minutes ago</small>
                    </div>
                    <div class="notification-item">
                        <p>Your profile is 80% complete.</p>
                        <small>2 hours ago</small>
                    </div>
                </div>
            </div>
        </header>

        <!-- Dashboard Content -->
        <div class="dashboard">
            <div class="welcome-banner">
                <h1>Welcome back, Sarah!</h1>
                <p>Here's what's happening with your career development today.</p>
            </div>

            <div class="dashboard-grid">
                <!-- Personal Details Card -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-user-circle"></i>
                        <h2>Personal Details</h2>
                    </div>
                    <div class="detail-item">
                        <label>Full Name</label>
                        <p>Sarah Johnson</p>
                    </div>
                    <div class="detail-item">
                        <label>Email</label>
                        <p>sarah.j@example.com</p>
                    </div>
                    <div class="detail-item">
                        <label>Phone</label>
                        <p>+1 (234) 567-8901</p>
                    </div>
                    <div class="detail-item">
                        <label>Location</label>
                        <p>New York, USA</p>
                    </div>
                </div>

                <!-- Mentor Details Card -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <h2>Mentor Details</h2>
                    </div>
                    <div class="detail-item">
                        <label>Mentor Name</label>
                        <p>Dr. Michael Chen</p>
                    </div>
                    <div class="detail-item">
                        <label>Specialization</label>
                        <p>Data Science & AI</p>
                    </div>
                    <div class="detail-item">
                        <label>Email</label>
                        <p>michael.chen@example.com</p>
                    </div>
                    <div class="detail-item">
                        <label>Next Session</label>
                        <p>June 15, 2025 at 3:00 PM</p>
                    </div>
                </div>
            </div>

            <!-- Calendar -->
            <div class="calendar-container">
                <div class="calendar-header">
                    <h2 id="calTitle">June 2025</h2>
                    <div class="month-nav">
                        <button id="prev"><i class="fas fa-chevron-left"></i></button>
                        <button id="next"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
                <div id="calendar">
                    <!-- Calendar will be rendered by JavaScript -->
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer>
            <p>© 2025 CareerHigh. All rights reserved.</p>
        </footer>
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
        
        // Notification functionality
        const notificationBtn = document.querySelector('.notification-btn');
        const notificationBox = document.querySelector('.notification-box');
        
        notificationBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            notificationBox.classList.toggle('active');
        });
        
        // Close notification when clicking outside
        document.addEventListener('click', (e) => {
            if (!notificationBox.contains(e.target) && !notificationBtn.contains(e.target)) {
                notificationBox.classList.remove('active');
            }
        });
        
        // Calendar functionality
        (function(){
            const calRoot = document.getElementById('calendar');
            const title = document.getElementById('calTitle');
            const prevBtn = document.getElementById('prev');
            const nextBtn = document.getElementById('next');

            let dt = new Date(); // user's local date
            let year = dt.getFullYear();
            let month = dt.getMonth(); // 0-indexed

            function renderCalendar(y, m){
                calRoot.innerHTML = '';
                const firstDay = new Date(y, m, 1).getDay(); // 0 = Sun
                const daysInMonth = new Date(y, m+1, 0).getDate();

                // Update title
                const monthNames = ["January", "February", "March", "April", "May", "June",
                    "July", "August", "September", "October", "November", "December"];
                title.textContent = `${monthNames[m]} ${y}`;

                // header: weekdays (Mon..Sun)
                const weekdays = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
                const wkRow = document.createElement('div');
                wkRow.className = 'wk-row';
                weekdays.forEach(w => {
                    const el = document.createElement('div');
                    el.className = 'wkcell';
                    el.textContent = w;
                    wkRow.appendChild(el);
                });
                calRoot.appendChild(wkRow);

                // grid
                const grid = document.createElement('div');
                grid.className = 'cal-grid';

                // Add empty cells for days before the first day of month
                // Adjusting for Monday as first day (firstDay=1 is Monday)
                let startOffset = firstDay === 0 ? 6 : firstDay - 1; // If Sunday, offset is 6
                for(let i = 0; i < startOffset; i++){
                    const emptyCell = document.createElement('div');
                    emptyCell.className = 'cal-cell other-month';
                    // Show previous month's dates
                    const prevMonth = new Date(y, m, 0);
                    const prevMonthDays = prevMonth.getDate();
                    emptyCell.textContent = prevMonthDays - (startOffset - i - 1);
                    grid.appendChild(emptyCell);
                }

                // Add current month days
                const today = new Date();
                for(let i = 1; i <= daysInMonth; i++){
                    const cell = document.createElement('div');
                    cell.className = 'cal-cell';
                    cell.textContent = i;
                    
                    // Highlight today
                    if(i === today.getDate() && m === today.getMonth() && y === today.getFullYear()){
                        cell.classList.add('today');
                    }
                    
                    grid.appendChild(cell);
                }

                // Add empty cells for days after the last day of month
                const totalCells = 42; // 6 rows x 7 days
                const remainingCells = totalCells - (startOffset + daysInMonth);
                for(let i = 1; i <= remainingCells; i++){
                    const emptyCell = document.createElement('div');
                    emptyCell.className = 'cal-cell other-month';
                    emptyCell.textContent = i;
                    grid.appendChild(emptyCell);
                }

                calRoot.appendChild(grid);
            }

            // Event listeners for navigation
            prevBtn.addEventListener('click', () => {
                month--;
                if(month < 0){
                    month = 11;
                    year--;
                }
                renderCalendar(year, month);
            });

            nextBtn.addEventListener('click', () => {
                month++;
                if(month > 11){
                    month = 0;
                    year++;
                }
                renderCalendar(year, month);
            });

            // Initial render
            renderCalendar(year, month);
        })();
    </script>
</body>
</html>