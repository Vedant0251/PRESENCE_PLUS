<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presence Plus - About Us</title>
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
            padding-top: 60px;
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

        .about-section {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
        }

        .about-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .about-header h1 {
            color: #2c3e50;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .about-header p {
            color: #666;
            font-size: 1.2rem;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .about-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .about-card {
            background-color: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .about-card:hover {
            transform: translateY(-5px);
        }

        .about-card h2 {
            color: #e74c3c;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .about-card p {
            color: #666;
            line-height: 1.6;
        }

        .team-section {
            margin-top: 4rem;
            text-align: center;
        }

        .team-section h2 {
            color: #2c3e50;
            margin-bottom: 2rem;
            font-size: 2rem;
        }

        .team-members {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .team-member {
            background-color: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .team-member h3 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .team-member p {
            color: #666;
        }

        .mentor-section {
            margin-top: 3rem;
            text-align: center;
        }

        .mentor-section h2 {
            color: #2c3e50;
            margin-bottom: 2rem;
            font-size: 2rem;
        }

        .mentor-card {
            max-width: 400px;
            margin: 0 auto;
            background-color: white;
            border: 2px solid #e74c3c;
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

            .about-header h1 {
                font-size: 2rem;
            }

            .about-header p {
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
                <a href="about.php">About</a>
                <a href="contact.php">Contact</a>
            </div>
            <div class="auth-buttons">
                <a href="signin.php" class="auth-button signin-button">Sign In</a>
                <a href="signup.php" class="auth-button signup-button">Sign Up</a>
            </div>
        </div>
    </nav>

    <section class="about-section">
        <div class="about-header">
            <h1>About Presence+</h1>
            <p>We are dedicated to revolutionizing attendance management in educational institutions through innovative technology and user-friendly solutions.</p>
        </div>

        <div class="about-content">
            <div class="about-card">
                <h2>Our Mission</h2>
                <p>To simplify attendance tracking and management for educational institutions, making it more efficient, accurate, and accessible for teachers and administrators.</p>
            </div>
            <div class="about-card">
                <h2>Our Vision</h2>
                <p>To become the leading attendance management solution in educational institutions worldwide, helping schools and universities focus more on education and less on administrative tasks.</p>
            </div>
            <div class="about-card">
                <h2>Our Values</h2>
                <p>We believe in innovation, reliability, and user-centric design. Our commitment to these values drives us to continuously improve and deliver the best possible experience for our users.</p>
            </div>
        </div>

        <div class="team-section">
            <h2>Meet Our Team</h2>
            <div class="team-members">
                <div class="team-member">
                    <h3>Mahek Raigala</h3>
                    <p>Developer</p>
                </div>
                <div class="team-member">
                    <h3>Khushi Chandak</h3>
                    <p>Developer</p>
                </div>
                <div class="team-member">
                    <h3>Shrusti Gada</h3>
                    <p>Developer</p>
                </div>
                <div class="team-member">
                    <h3>Vedant Bhoir</h3>
                    <p>Developer</p>
                </div>
            </div>

            <div class="mentor-section">
                <h2>Our Mentor</h2>
                <div class="team-member mentor-card">
                    <h3>Prof.Nilesh Yadav</h3>
                    <p>Project Guide & Mentor</p>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <p>&copy; 2024 Presence Plus. All rights reserved.</p>
    </footer>
</body>
</html>