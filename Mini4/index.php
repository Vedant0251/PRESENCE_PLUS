<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presence Plus - Attendance Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f5f5;
            min-height: 100vh;
            padding-top: 80px;
        }

        nav {
            background-color: #e74c3c;
            padding: 1rem;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-logo {
            color: white;
            text-decoration: none;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: #f5b7b1;
        }

        .auth-buttons {
            display: flex;
            gap: 1rem;
        }

        .auth-button {
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
            transition: all 0.3s;
        }

        .signin-button {
            background-color: transparent;
            color: white;
            border: 1px solid white;
        }

        .signin-button:hover {
            background-color: white;
            color: #e74c3c;
        }

        .signup-button {
            background-color: white;
            color: #e74c3c;
        }

        .signup-button:hover {
            background-color: #f5b7b1;
            color: #e74c3c;
        }

        .hero {
            background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%);
            padding: 4rem 2rem;
            text-align: center;
            color: white;
        }

        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .hero p {
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto 2rem;
        }

        .cta-button {
            display: inline-block;
            background-color: white;
            color: #e74c3c;
            padding: 1rem 2rem;
            border-radius: 4px;
            text-decoration: none;
            transition: all 0.3s;
        }

        .cta-button:hover {
            background-color: #f5b7b1;
        }

        .features {
            max-width: 1200px;
            margin: 4rem auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }

        .feature-card h3 {
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .feature-card p {
            color: #666;
            line-height: 1.6;
        }

        .quick-access {
            background-color: #ecf0f1;
            padding: 4rem 2rem;
            text-align: center;
        }

        .quick-access h2 {
            color: #2c3e50;
            margin-bottom: 2rem;
        }

        .quick-links {
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
            max-width: 1200px;
            margin: 0 auto;
        }

        .quick-link {
            background-color: white;
            padding: 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            color: #2c3e50;
            min-width: 200px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .quick-link:hover {
            transform: translateY(-5px);
            color: #e74c3c;
        }

        footer {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            padding: 2rem;
            margin-top: 4rem;
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .hero p {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <nav>
        <div class="nav-container">
            <a href="index.php" class="nav-logo">Presence+</a>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="#">About</a>
                <a href="#">Contact</a>
            </div>
            <div class="auth-buttons">
                <a href="signin.php" class="auth-button signin-button">Sign In</a>
                <a href="signup.php" class="auth-button signup-button">Sign Up</a>
            </div>
        </div>
    </nav>

    <section class="hero">
        <h1>Welcome to Presence+</h1>
        <p>A modern solution for tracking and managing student attendance efficiently and effectively.</p>
        <a href="signin.php" class="cta-button">Get Started</a>
    </section>

    <section class="features">
        <div class="feature-card">
            <h3>Easy Tracking</h3>
            <p>Mark and monitor attendance with just a few clicks. Real-time updates and intuitive interface make attendance tracking effortless.</p>
        </div>
        <div class="feature-card">
            <h3>Detailed Reports</h3>
            <p>Generate comprehensive attendance reports. Get insights into attendance patterns and student participation.</p>
        </div>
        <div class="feature-card">
            <h3>Secure & Reliable</h3>
            <p>Your data is safe with us. Our system ensures accurate record-keeping and data security at all times.</p>
        </div>
    </section>

    <section class="quick-access">
        <h2>Quick Access</h2>
        <div class="quick-links">
            <a href="signin.php" class="quick-link">
                <h3>Mark Attendance</h3>
                <p>Record daily attendance</p>
            </a>
            <a href="signin.php" class="quick-link">
                <h3>View Reports</h3>
                <p>Check attendance records</p>
            </a>
            <a href="signin.php" class="quick-link">
                <h3>Manage Students</h3>
                <p>Update student information</p>
            </a>
        </div>
    </section>

    <footer>
        <p>&copy; 2024 Presence Plus. All rights reserved.</p>
    </footer>
</body>
</html>