<?php
ob_start(); // start output buffering
include 'connect.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Email and password are required.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $name, $hashedPassword, $role);
            $stmt->fetch();

            if (password_verify($password, $hashedPassword)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['name'] = $name;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = $role;

                if ($role === 'student') {
                    header("Location: student-dashboard.php");
                } elseif ($role === 'teacher') {
                    header("Location: teacher-dashboard.php");
                }
                exit;
            } else {
                $error = "Invalid credentials.";
            }
        } else {
            $error = "Invalid credentials.";
        }

        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presence+ - Sign In</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            padding-top: 0;
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
            background-color: #e74c3c;
            color: white;
            border: 1px solid white;
        }

        .signup-button:hover {
            background-color: #f5b7b1;
            color: #e74c3c;
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 350px;
            margin-top: 80px;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
        }
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-submit {
            width: 100%;
            padding: 0.75rem;
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
        }
        .form-submit:hover {
            background-color: #c0392b;
        }
        .error-message {
            color: #e74c3c;
            margin-bottom: 1rem;
            text-align: center;
        }
        .register-link {
            text-align: center;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <nav>
        <div class="nav-container">
            <a href="index.php" class="nav-logo">Presence+</a>
            <div class="nav-links">
                <?php if (isset($_SESSION['role'])): ?>
                    <?php if ($_SESSION['role'] === 'student'): ?>
                        <a href="student-dashboard.php">Dashboard</a>
                    <?php elseif ($_SESSION['role'] === 'teacher'): ?>
                        <a href="teacher-dashboard.php">Dashboard</a>
                    <?php endif; ?>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <div class="auth-buttons">
                        <a href="signin.php" class="auth-button signin-button">Sign In</a>
                        <a href="signup.php" class="auth-button signup-button">Sign Up</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container">
    <h2 style="text-align:center;">Sign In</h2>

    <?php if ($error): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="signin.php">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" required/>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required/>
        </div>
        <button type="submit" class="form-submit">Login</button>
        <div class="register-link">
            Don't have an account? <a href="signup.php">Sign up here</a>
        </div>
    </form>
</div>
</body>
<?php ob_end_flush(); ?>

</html>
