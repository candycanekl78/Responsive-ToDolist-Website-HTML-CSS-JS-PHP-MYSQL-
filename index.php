<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TodoList - Organize Your Life</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: rgba(7, 187, 223, 0.65);
            --secondary-color: rgba(8, 56, 65, 0.65);
            --accent-color: #ff5511cc;
            --text-dark: rgb(34, 29, 29);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-image: url('bgmain2.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            color: var(--text-dark);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        header {
            background-color: rgba(255, 255, 255, 0.9);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
            border: 3px solid rgb(240, 243, 240);
        }

        .logo-text {
            font-size: 24px;
            font-weight: bold;
            color: var(--secondary-color);
        }

        .nav-links {
            display: flex;
            gap: 20px;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: var(--accent-color);
        }

        .auth-buttons .btn {
            padding: 8px 20px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-login {
            background-color:white;
            color:rgba(17, 168, 255, 0.71);
            border: 1px solid var(--primary-color);
            border: 2px solid rgba(17, 168, 255, 0.69);
        }

        .btn-login:hover {
            background-color:rgba(17, 168, 255, 0.74);
            color: white;
            
        }

        .btn-signup {
            background-color: var(--accent-color);
            color: white;
            border: 1px solid var(--accent-color);
            margin-left: 10px;
        }

        .btn-signup:hover {
            background-color: transparent;
            color: var(--accent-color);
        }

        /* Hero Section */
        .hero {
            height: 100vh;
            display: flex;
            align-items: center;
            padding-top: 80px;
        
        }

        .hero-content {
            display: flex;
            align-items: center;
            gap: 50px;
        }

        .hero-text {
            flex: 1;
        }

        .hero-image {
            flex: 1;
            text-align: center;
        }

        .hero-image img {
            max-width: 100%;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: 4px solid white;
        }

        h1 {
            font-size: 48px;
            margin-bottom: 20px;
            color: rgba(17, 168, 255, 0.71);
        }

        .hero p {
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 30px;
            color: var(--text-dark);
        }

        .btn-primary {
            display: inline-block;
            padding: 12px 30px;
            background-color: transparent;
            color: #ff5511cc;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
            border: 2px solid var(--accent-color);
        }

        .btn-primary:hover {
            background-color: var(--accent-color);
            color:white;
            border: 2px solid #ff5511cc;
        }

        /* Features Section */
        .features {
            padding: 100px 0;
            background-color: rgba(255, 255, 255, 0.95);
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title h2 {
            font-size: 36px;
            color: var(--secondary-color);
            margin-bottom: 15px;
        }

        .section-title p {
            color: var(--text-dark);
            max-width: 700px;
            margin: 0 auto;
            font-size: 18px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .feature-card {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background-color: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            color: white;
            font-size: 24px;
        }

        .feature-card h3 {
            font-size: 22px;
            margin-bottom: 15px;
            color: var(--secondary-color);
        }

        .feature-card p {
            color: var(--text-dark);
            line-height: 1.6;
        }

        /* Testimonials */
        .testimonials {
            padding: 100px 0;
            background-color: rgba(240, 243, 240, 0.8);
        }

        .testimonial-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .testimonial-card {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .testimonial-text {
            font-style: italic;
            margin-bottom: 20px;
            color: var(--text-dark);
            line-height: 1.6;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
        }

        .author-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 15px;
        }

        .author-info h4 {
            color: var(--secondary-color);
            margin-bottom: 5px;
        }

        .author-info p {
            color: var(--text-dark);
            opacity: 0.8;
            font-size: 14px;
        }

        /* CTA Section */
        .cta {
            padding: 100px 0;
            text-align: center;
            background-color: rgba(255, 255, 255, 0.95);
        }

        .cta h2 {
            font-size: 36px;
            color: var(--secondary-color);
            margin-bottom: 20px;
        }

        .cta p {
            color: var(--text-dark);
            max-width: 700px;
            margin: 0 auto 30px;
            font-size: 18px;
        }

        /* Footer */
        footer {
            background-color: var(--secondary-color);
            color: var(--text-light);
            padding: 50px 0 20px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
            color: white;
        }

        .footer-column h3 {
            font-size: 20px;
            margin-bottom: 20px;
            color: white;
        }

        .footer-column ul {
            list-style: none;
            color: white;
        }

        .footer-column ul li {
            margin-bottom: 10px;
            color: white;
        }

        .footer-column ul li a {
            color: white;
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-column ul li a:hover {
            color: var(--primary-color);
        }

        .social-links {
            
            display: flex;
            gap: 15px;
        }

        .social-links a {
            color: white;
            font-size: 20px;
            transition: color 0.3s;
        }

        .social-links a:hover {
            color: var(--primary-color);
        }

        .copyright {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            color: white;
            font-size: 14px;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 15px;
            }

            .hero-content {
                flex-direction: column;
                text-align: center;
            }

            h1 {
                font-size: 36px;
            }

            .hero-image {
                margin-top: 30px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <nav class="navbar">
                <div class="logo">
                    <div class="logo-icon">T</div>
                    <div class="logo-text">TodoList</div>
                </div>
                <div class="nav-links">
                    <a href="#features">Features</a>
                    <a href="#testimonials">Testimonials</a>
                    <a href="#about">About</a>
                    <a href="#contact">Contact</a>
                </div>
                <div class="auth-buttons">
                    <a href="signin/login.php" class="btn btn-login">Login</a>
                
                </div>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1>Organize Your Life with TodoList</h1>
                    <p>Manage your tasks efficiently with our intuitive todo list application. Stay productive, meet deadlines, and achieve your goals with our powerful yet simple task management tool.</p>
                    <a href="signup/register.php" class="btn-primary">Get Started for Free</a>
                </div>
                <div class="hero-image">
                    <img src="todolist.jpg" alt="TodoList App Preview">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-title">
                <h2>Powerful Features</h2>
                <p>TodoList comes packed with features to help you stay organized and productive</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <h3>Task Management</h3>
                    <p>Easily create, organize, and prioritize your tasks with our intuitive interface. Set due dates and never miss a deadline.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3>Calendar Integration</h3>
                    <p>View your tasks on a calendar to better plan your schedule and visualize your workload.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h3>Reminders</h3>
                    <p>Get timely notifications so you never forget important tasks or deadlines.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-tags"></i>
                    </div>
                    <h3>Categories & Tags</h3>
                    <p>Organize your tasks by categories and tags for better organization and quick filtering.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Mobile Friendly</h3>
                    <p>Access your tasks from anywhere with our responsive design that works on all devices.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h3>Secure & Private</h3>
                    <p>Your data is encrypted and protected. We respect your privacy and keep your information safe.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials" id="testimonials">
        <div class="container">
            <div class="section-title">
                <h2>What Our Users Say</h2>
                <p>Don't just take our word for it. Here's what our users have to say about TodoList</p>
            </div>
            <div class="testimonial-grid">
                <div class="testimonial-card">
                    <p class="testimonial-text">"TodoList has completely transformed how I organize my work. I'm more productive and less stressed since I started using it."</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">S</div>
                        <div class="author-info">
                            <h4>Sarah Johnson</h4>
                            <p>Project Manager</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <p class="testimonial-text">"As a student, TodoList helps me keep track of all my assignments and deadlines. I don't know how I managed without it!"</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">M</div>
                        <div class="author-info">
                            <h4>Michael Chen</h4>
                            <p>University Student</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <p class="testimonial-text">"The clean interface and powerful features make this the best todo app I've used. The calendar view is particularly helpful."</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">A</div>
                        <div class="author-info">
                            <h4>Amanda Rodriguez</h4>
                            <p>Freelance Designer</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <h2>Ready to Get Organized?</h2>
            <p>Join thousands of productive people who use TodoList to manage their tasks and achieve their goals.</p>
            <a href="signin/register.php" class="btn-primary">Start Your Free Trial</a>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact">
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>TodoList</h3>
                    <p>Your ultimate task management solution to stay organized and productive.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="footer-column">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="#">Home</a></li>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#testimonials">Testimonials</a></li>
                        <li><a href="#about">About Us</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Support</h3>
                    <ul>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Contact Us</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Contact</h3>
                    <ul>
                        <li><i class="fas fa-map-marker-alt"></i> 123 Productivity St, Work City</li>
                        <li><i class="fas fa-phone"></i> (123) 456-7890</li>
                        <li><i class="fas fa-envelope"></i> support@todolist.com</li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> TodoList. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>