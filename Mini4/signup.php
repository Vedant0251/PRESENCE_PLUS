<?php
include 'connect.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $passwordRaw = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? '';

    if (empty($name) || empty($email) || empty($passwordRaw) || empty($confirmPassword) || empty($role)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($passwordRaw !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        // Check if email already exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "This email is already registered.";
        } else {
            $password = password_hash($passwordRaw, PASSWORD_BCRYPT);

            // Proceed to insert user
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $password, $role);

            if ($stmt->execute()) {
                $student_id = $stmt->insert_id;

                if (!empty($_POST['face_image'])) {
                    $imageData = $_POST['face_image'];
                    $imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
                    $imageData = base64_decode($imageData);

                    $imgStmt = $conn->prepare("UPDATE users SET reference_image = ? WHERE id = ?");
                    $imgStmt->send_long_data(0, $imageData);
                    $imgStmt->bind_param("si", $imageData, $student_id);
                    $imgStmt->execute();
                    $imgStmt->close();
                }

                header("Location: signin.php");
                exit;
            } else {
                $error = "Error: " . $stmt->error;
            }

            $stmt->close();
        }

        $check->close();
    }

    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presence Plus - Sign Up</title>
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
            background-color: #e74c3c;
            color: white;
            border: 1px solid white;
        }

        .signup-button:hover {
            background-color: #f5b7b1;
            color: #e74c3c;
        }

        .container {
            width: 100%;
            max-width: 400px;
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin: 0 auto;
        }

        .container {
            width: 100%;
            max-width: 400px;
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            background-color: white;
            color: #2c3e50;
            cursor: pointer;
        }

        .form-group select:focus {
            outline: none;
            border-color: #3498db;
        }

        .form-submit {
            width: 100%;
            padding: 0.75rem;
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            margin-top: 1rem;
        }

        .form-submit:hover {
            background-color: #c0392b;
        }

        .error-message {
            color: #e74c3c;
            margin-bottom: 1rem;
            text-align: center;
        }

        .success-message {
            color: #2ecc71;
            margin-bottom: 1rem;
            text-align: center;
        }

        .signin-link {
            text-align: center;
            margin-top: 1rem;
        }

        .signin-link a {
            color: #3498db;
            text-decoration: none;
        }

        .signin-link a:hover {
            text-decoration: underline;
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
    <form method="POST" action="" onsubmit="return validateCapture();">
        <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && $error): ?>
    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>


<?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && $success): ?>
    <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role">
                    <option value="student">Student</option>
                    <option value="teacher">Teacher</option>
                </select>
            </div>
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <div class="video-capture-container" style="margin: 1rem 0; text-align: center;">
                <video id="video" width="320" height="240" autoplay style="border-radius: 8px; margin-bottom: 1rem;"></video>
                <br>
                <button type="button" onclick="captureImage()" class="capture-button" style="background-color: #e74c3c; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; transition: background-color 0.3s ease;">Capture Face</button>
                <div id="capture-message" style="margin-top: 10px; color: green; font-weight: 500;"></div>
                <canvas id="canvas" width="320" height="240" style="display:none;"></canvas>
                <input type="hidden" name="face_image" id="face_image">
            </div>

<script>
navigator.mediaDevices.getUserMedia({ video: true })
  .then(function(stream) {
    document.getElementById('video').srcObject = stream;
  });

  function captureImage() {
  const canvas = document.getElementById('canvas');
  const video = document.getElementById('video');
  canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
  const dataURL = canvas.toDataURL('image/jpeg');
  document.getElementById('face_image').value = dataURL;

  document.getElementById('capture-message').innerText = "Face Captured Successfully!";

  // Optional: disable button to avoid re-capturing
  document.querySelector('.capture-button').disabled = true;
}

</script>


            <button type="submit" class="form-submit">Sign Up</button>
            <div class="signin-link">
                Already have an account? <a href="signin.php">Sign in here</a>
            </div>
        </form>
    </div>
</body>
</html>