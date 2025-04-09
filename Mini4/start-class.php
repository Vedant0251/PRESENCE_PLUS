<?php
session_start();
require 'connect.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: signin.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = $_POST['subject'] ?? '';
    $className = $_POST['class_name'] ?? '';
    
    if (!empty($subject) && !empty($className)) {
        try {
            // Create a new class session
            $stmt = $conn->prepare("INSERT INTO class_sessions (teacher_id, subject, class_name, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("iss", $_SESSION['id'], $subject, $className);
            
            if ($stmt->execute()) {
                header('Location: teacher-dashboard.php?success=1');
                exit;
            } else {
                $error = "Failed to start class session";
            }
        } catch (Exception $e) {
            $error = "Database error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all fields";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Start Class - Presence Plus</title>
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
            background-color: #c0392b;
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

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }

        .start-class-section {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .start-class-form {
            display: grid;
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-group label {
            font-weight: 500;
            color: #2c3e50;
        }

        .form-group input,
        .form-group select {
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .start-button {
            background-color: #2ecc71;
            color: white;
            padding: 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        .start-button:hover {
            background-color: #27ae60;
        }

        .error-message {
            color: #e74c3c;
            margin-top: 1rem;
            padding: 0.5rem;
            background-color: #fdf1f0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <nav>
        <div class="nav-container">
            <a href="index.php" class="nav-logo">Presence Plus</a>
            <div class="nav-links">
                <a href="teacher-dashboard.php">Dashboard</a>
                <a href="#">Reports</a>
                <a href="#">Settings</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="start-class-section">
            <h2>Start a New Class</h2>
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form class="start-class-form" method="POST" action="">
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" required>
                </div>
                <div class="form-group">
                    <label for="class-name">Class Name</label>
                    <input type="text" id="class-name" name="class_name" required>
                </div>
                <button type="submit" class="start-button">Start Class</button>
            </form>
        </div>
    </div>
</body>
</html>