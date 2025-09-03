<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="img/Landing_logo.png">
    <title>CareerHigh - Database Management Excellence</title>
    <link rel="stylesheet" href="css/landing.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Your HTML content here (unchanged) -->
    <!-- Just copy all the HTML body content you already have -->
    <!-- Navigation Header -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-left">
                <!-- EDIT: Replace with your logo image -->
                <img src="img/Landing_logo.png" alt="CareerHigh Logo" class="logo">
                <h2 class="brand-name">CareerHigh</h2>
            </div>
            <div class="nav-right">
                <a href="features.php" class="nav-link">Features</a>
                <a href="#community" class="nav-link">Community</a>
                <a href="login.php" class="cta-button">Login/Register Now</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <h1 class="hero-title">Elevate Your Career with <span class="highlight">CareerHigh</span></h1>
                <p class="hero-subtitle">The ultimate database management platform connecting students, mentors, and alumni for unprecedented career growth</p>
                <div class="hero-stats">
                    <div class="stat-item">
                        <div class="stat-icon">📊</div>
                        <div class="stat-text">Advanced Analytics</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">🎯</div>
                        <div class="stat-text">Career Focused</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">🚀</div>
                        <div class="stat-text">Future Ready</div>
                    </div>
                </div>
                <a href="signup.php" class="hero-cta">Get Started Today</a>
            </div>
        </div>
        <div class="hero-background"></div>
    </section>

    <!-- Client Logos Scrolling Section -->
    <section class="clients">
        <div class="container">
            <h3 class="section-subtitle">Trusted by Leading Organizations</h3>
            <div class="clients-scroll">
                <div class="clients-track">
                    <!-- EDIT: Replace these placeholder images with actual client logos -->
                    <img src="img/facebook.png" alt="Facebook" class="client-logo">
                    <img src="img/Microsoft.png" alt="Microsoft" class="client-logo">
                    <img src="img/BRACU.png" alt="BRAC University" class="client-logo">
                    <img src="img/Bomazon.png" alt="Amazon" class="client-logo">
                    <img src="img/Samsung.png" alt="Samsung" class="client-logo">
                    <img src="img/Voogle.png" alt="Nasa" class="client-logo">
                    <!-- Duplicate for seamless loop -->
                    <img src="img/facebook.png" alt="Facebook" class="client-logo">
                    <img src="img/Microsoft.png" alt="Microsoft" class="client-logo">
                    <img src="img/BRACU.png" alt="BRAC University" class="client-logo">
                    <img src="img/Bomazon.png" alt="Amazom" class="client-logo">
                </div>
            </div>
        </div>
    </section>

    <!-- Animated Counter Section -->
    <section class="counters">
        <div class="container">
            <div class="counters-grid">
                <div class="counter-item">
                    <div class="counter-number" data-target="2500">0</div>
                    <div class="counter-label">Expert Mentors</div>
                </div>
                <div class="counter-item">
                    <div class="counter-number" data-target="15000">0</div>
                    <div class="counter-label">Active Students</div>
                </div>
                <div class="counter-item">
                    <div class="counter-number" data-target="8500">0</div>
                    <div class="counter-label">Successful Alumni</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Alumni Testimonials Slider -->
    <section class="testimonials">
        <div class="container">
            <h2 class="section-title">What Our Alumni Say</h2>
            <div class="testimonials-slider">
                <div class="testimonial-track">
                    <div class="testimonial-card active">
                        <!-- EDIT: Replace with actual alumni photo -->
                        <img src="img/Women 1.jpg" alt="Sarah Johnson" class="alumni-photo">
                        <div class="testimonial-content">
                            <p>"CareerHigh transformed my database management skills and connected me with incredible mentors. Landing my dream job at a top tech company was possible because of this platform."</p>
                            <div class="alumni-info">
                                <h4>Wom Nwom</h4>
                                <span>Senior Data Engineer at Google</span>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-card">
                        <!-- EDIT: Replace with actual alumni photo -->
                        <img src="img/Adolf Hitler.jpg" alt="Michael Chen" class="alumni-photo">
                        <div class="testimonial-content">
                            <p>"Mazid gave me the idea of killing Jews. Thanks to Mazid and CarrerHigh team"</p>
                            <div class="alumni-info">
                                <h4>Adolf Hitler</h4>
                                <span>Valedictorian of Germany</span>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-card">
                        <!-- EDIT: Replace with actual alumni photo -->
                        <img src="img/African Man in Suite.png" alt="Kneegrow Thieves" class="alumni-photo">
                        <div class="testimonial-content">
                            <p>"CareerHigh's network opened doors I never knew existed. The platform's focus on practical skills and industry connections is game-changing."</p>
                            <div class="alumni-info">
                                <h4>Kneegrow Thieves</h4>
                                <span>Database Leaker at Amazon</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="slider-dots">
                    <button class="dot active" data-slide="0"></button>
                    <button class="dot" data-slide="1"></button>
                    <button class="dot" data-slide="2"></button>
                </div>
            </div>
        </div>
    </section>

    <!-- Social Media Footer -->
   <footer class="footer">
        <div class="container">
            <div class="social-links">
                <a href="http://www.facebook.com" class="social-link" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook"></i></a>
                <a href="http://www.instagram.com" class="social-link" target="_blank" rel="noopener noreferrer"><i class="fab fa-instagram"></i></a>
                <a href="http://www.twitter.com" class="social-link" target="_blank" rel="noopener noreferrer"><i class="fab fa-twitter"></i></a>
                <a href="https://github.com/mazidzomader" class="social-link" target="_blank" rel="noopener noreferrer"><i class="fab fa-github"></i></a>
                <a href="https://www.linkedin.com" class="social-link" target="_blank" rel="noopener noreferrer"><i class="fab fa-linkedin"></i></a>
            </div>
            <p class="copyright">© 2025 CareerHigh. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Animated Counter
        function animateCounters() {
            const counters = document.querySelectorAll('.counter-number');
            
            counters.forEach(counter => {
                const target = parseInt(counter.getAttribute('data-target'));
                const duration = 2000;
                const increment = target / (duration / 16);
                let current = 0;
                
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        counter.textContent = target.toLocaleString();
                        clearInterval(timer);
                    } else {
                        counter.textContent = Math.floor(current).toLocaleString();
                    }
                }, 16);
            });
        }

        // Intersection Observer for counter animation
        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    counterObserver.unobserve(entry.target);
                }
            });
        });

        counterObserver.observe(document.querySelector('.counters'));

        // Testimonial Slider
        let currentSlide = 0;
        const slides = document.querySelectorAll('.testimonial-card');
        const dots = document.querySelectorAll('.dot');

        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.classList.toggle('active', i === index);
            });
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === index);
            });
        }

        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                currentSlide = index;
                showSlide(currentSlide);
            });
        });

        // Auto-slide testimonials
        setInterval(() => {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }, 5000);

        // Smooth scrolling for navigation
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add scroll effect to navbar
        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    </script>
</body>
</html>
