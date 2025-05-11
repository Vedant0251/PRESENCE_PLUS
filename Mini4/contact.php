<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presence Plus - Contact Us</title>
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

        .contact-section {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
        }

        .contact-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .contact-header h1 {
            color: #2c3e50;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .contact-header p {
            color: #666;
            font-size: 1.2rem;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .contact-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .contact-form {
            background-color: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .form-group textarea {
            height: 150px;
            resize: vertical;
        }

        .submit-button {
            background-color: #e74c3c;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }

        .submit-button:hover {
            background-color: #c0392b;
        }

        .contact-info {
            background-color: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .contact-info h2 {
            color: #2c3e50;
            margin-bottom: 1.5rem;
        }

        .contact-info-item {
            margin-bottom: 1rem;
        }

        .contact-info-item h3 {
            color: #e74c3c;
            margin-bottom: 0.5rem;
        }

        .contact-info-item p {
            color: #666;
            line-height: 1.6;
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

            .contact-header h1 {
                font-size: 2rem;
            }

            .contact-header p {
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

    <section class="contact-section">
        <div class="contact-header">
            <h1>Contact Us</h1>
            <p>Have questions or need assistance? We're here to help! Reach out to us using the form below or through our contact information.</p>
        </div>

        <div class="contact-content">
            <div class="contact-form">
                <h2>Send us a Message</h2>
                <form action="#" method="POST">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" required></textarea>
                    </div>
                    <button type="submit" class="submit-button">Send Message</button>
                </form>
            </div>

            <div class="contact-info">
                <h2>Contact Information</h2>
                <div class="contact-info-item">
                    <h3>Address</h3>
                    <p>XYZ<br>KJSIT<br>XYZ, 12345</p>
                </div>
                <div class="contact-info-item">
                    <h3>Email</h3>
                    <p>info@presenceplus.com<br>support@presenceplus.com</p>
                </div>
                <div class="contact-info-item">
                    <h3>Phone</h3>
                    <p>+91 1234567890<br>+91 9087654321</p>
                </div>
                <div class="contact-info-item">
                    <h3>Contact Hours</h3>
                    <p>Monday - Friday: 9:00 AM - 6:00 PM<br>Saturday: 9:00 AM - 1:00 PM<br>Sunday: Closed</p>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <p>&copy; 2024 Presence Plus. All rights reserved.</p>
    </footer>
</body>
</html>