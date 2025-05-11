<?php
session_start();

// Access control
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: signin.php");
    exit;
}

include 'connect.php';

$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_attendance'])) {
    $studentId = $_SESSION['user_id'];
    $classId = $_POST['class_id'];

    $stmt = $conn->prepare("INSERT INTO attendance (student_id, class_session_id, status) VALUES (?, ?, 'present')");
    $stmt->bind_param("ii", $studentId, $classId);

    if ($stmt->execute()) {
        $successMessage = "Attendance marked successfully.";
    } else {
        $successMessage = "Error marking attendance: " . $conn->error;
    }

    $stmt->close();
}

// Fetch the most recent active class
$sql = "SELECT * FROM class_sessions ORDER BY start_time DESC LIMIT 1";
$result = mysqli_query($conn, $sql);

$activeClass = null;

if (mysqli_num_rows($result) > 0) {
    $activeClass = mysqli_fetch_assoc($result);
}

// Fetch user information including reference image
$userId = $_SESSION['user_id'];
$userQuery = $conn->prepare("SELECT reference_image, name, email, role FROM users WHERE id = ?");
$userQuery->bind_param("i", $userId);
$userQuery->execute();
$userQuery->bind_result($referenceImage, $userName, $userEmail, $userRole);
$userQuery->fetch();
$userQuery->close();

// Encode reference image for display and face recognition
$imageData = null;
if ($referenceImage) {
    $imageData = base64_encode($referenceImage);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        nav {
            background-color: #c0392b;
            padding: 1rem;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 40px auto 0;
            padding: 2rem;
        }

        .dashboard-header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            margin-bottom: 3rem;
        }

        @media (max-width: 992px) {
            .dashboard-cards {
                grid-template-columns: 1fr;
            }
        }

        .profile-card {
            grid-column: 1 / -1;
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .profile-picture {
            margin-bottom: 0;
            flex-shrink: 0;
        }

        .profile-info {
            flex-grow: 1;
        }
        .profile-card:hover {
            transform: translateY(-5px);
        }

        .profile-picture {
            margin-bottom: 1.5rem;
        }

        .profile-picture img {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #e74c3c;
            padding: 4px;
            background: white;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.2);
        }

        .profile-info {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 12px;
            margin-top: 1rem;
        }

        .profile-info p {
            margin: 0.75rem 0;
            color: #2c3e50;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .profile-info strong {
            color: #e74c3c;
            font-weight: 600;
        }

        .dashboard-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: none;
            transition: transform 0.3s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
        }

        .dashboard-card h3 {
            color: #2c3e50;
            margin-bottom: 1.5rem;
            font-size: 1.4rem;
            border-bottom: 2px solid #e74c3c;
            padding-bottom: 0.5rem;
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
        

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .nav-links a:hover {
            background: rgba(255,255,255,0.1);
        }

        #attendance-section video {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        #attendance-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
            width: 100%;
        }

        #attendance-btn:hover {
            background: #c0392b;
            transform: translateY(-6px) scale(1.04);
            box-shadow: 0 12px 24px rgba(231,76,60,0.25), 0 2px 8px rgba(0,0,0,0.15);
            transition: background 0.3s, transform 0.25s cubic-bezier(0.4,0.2,0.2,1), box-shadow 0.25s cubic-bezier(0.4,0.2,0.2,1);
        }

        #verification-status {
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1rem;
            }
            
            .dashboard-header {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }
            
            .nav-links {
                display: none;
            }
            
            .profile-picture img {
                width: 150px;
                height: 150px;
            }

            .dashboard-cards {
                grid-template-columns: 1fr;
            }
        }

        .request-form {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-top: 2rem;
        }

        .request-form h2 {
            color: #2c3e50;
            margin-bottom: 1.5rem;
            font-size: 1.4rem;
            border-bottom: 2px solid #e74c3c;
            padding-bottom: 0.5rem;
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

        .form-group select,
        .form-group input[type="date"],
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
        }

        .form-submit {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
            width: 100%;
        }

        #mark-attendance-btn:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }

        #verification-status {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            color: #2c3e50;
            font-weight: 500;
            margin-top: 1.5rem;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
        }

        #video {
            margin: 1.5rem 0;
            border-radius: 8px;
            background-color: #000;
            width: 100%;
        }

        .form-submit:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }

        footer {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            padding: 2rem;
            margin-top: 4rem;
        }

        .success-message {
            background: #4CAF50;
            color: white;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            text-align: center;
        }
        </style>
<div class="dashboard-container">
    <div class="dashboard-header">
        <h1>Welcome, <?php echo htmlspecialchars($userName); ?>!</h1>
    </div>

    <div class="dashboard-cards">
        <div class="profile-card">
            <div class="profile-picture">
                <img id="reference-image" src="data:image/jpeg;base64,<?php echo $imageData; ?>" 
                     data-image="<?php echo $imageData; ?>" alt="Profile Picture">
            </div>
            <div class="profile-info">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($userName); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($userEmail); ?></p>
                <p><strong>Role:</strong> <?php echo htmlspecialchars($userRole); ?></p>
            </div>
        </div>

        <div class="dashboard-card" id="attendance-section" style="height: fit-content;">
            <h3>Active Class</h3>
            <?php if ($activeClass): ?>
                <div class="active-class-info">
                    <p><strong>Subject:</strong> <?php echo htmlspecialchars($activeClass['subject']); ?></p>
                    <p><strong>Class:</strong> <?php echo htmlspecialchars($activeClass['class_name']); ?></p>
                    <p><strong>Started:</strong> <?php echo date('h:i A', strtotime($activeClass['start_time'])); ?></p>
                    <button onclick="startCamera()" class="form-submit" style="margin-top: 1rem;">Start Camera</button>
                    <div id="camera-container" style="display: none; margin-top: 1rem;">
                        <video id="camera-preview" autoplay playsinline style="width: 100%; border-radius: 8px;"></video>
                        <canvas id="overlay" style="position: absolute; top: 0; left: 0;"></canvas>
                        <div id="verification-status" style="margin-top: 1rem; text-align: center;">Initializing...</div>
                    </div>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: #7f8c8d;">No active class session at the moment.</p>
            <?php endif; ?>
        </div>

        <div class="dashboard-card" style="height: fit-content;">
            <h3>Notices</h3>
            <div id="notices-list" style="max-height: 300px; overflow-y: auto;">
                <p style='text-align: center; color: #7f8c8d;'>No notices available.</p>
            </div>

            <script>
            // Function to fetch and display notices
            function fetchNotices() {
                fetch('fetch-notices.php')
                    .then(response => response.json())
                    .then(data => {
                        const noticesList = document.getElementById('notices-list');
                        if (data.success && data.notices.length > 0) {
                            noticesList.innerHTML = data.notices.map(notice => `
                                <div class="notice" style="background: white; padding: 1rem; margin-bottom: 1rem; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                    <h4 style="color: #2c3e50; margin-bottom: 0.5rem;">${notice.title}</h4>
                                    <p style="color: #666; margin-bottom: 0.5rem;">${notice.content}</p>
                                    <small style="color: #999;">${new Date(notice.created_at).toLocaleString()}</small>
                                </div>
                            `).join('');
                        } else {
                            noticesList.innerHTML = '<p style="text-align: center; color: #7f8c8d;">No notices available.</p>';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('notices-list').innerHTML = '<p style="text-align: center; color: #e74c3c;">Error loading notices.</p>';
                    });
            }

            // Initial fetch of notices
            fetchNotices();

            // Refresh notices every minute
            setInterval(fetchNotices, 60000);
            </script>
        </div>

        <div class="request-form">
            <h2>Submit Attendance Request</h2>
            <form method="POST" action="submit-request.php">
                <input type="hidden" name="student_id" value="<?php echo $_SESSION['user_id']; ?>">

                <div class="form-group">
                    <label for="request-type">Request Type</label>
                    <select id="request-type" name="type" required>
                        <option value="">Select Request Type</option>
                        <option value="leave">Leave Application</option>
                        <option value="correction">Attendance Correction</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="request-date">Date</label>
                    <input type="date" id="request-date" name="date" required>
                </div>

                <div class="form-group">
                    <label for="request-reason">Reason</label>
                    <textarea id="request-reason" name="reason" rows="4" required placeholder="Explain your request briefly..."></textarea>
                </div>

                <button type="submit" class="form-submit">Submit Request</button>
            </form>

            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                <div class="success-message">Request submitted successfully!</div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php if (!empty($successMessage)): ?>
    <div style="color: green; font-weight: bold;"><?= htmlspecialchars($successMessage) ?></div>
<?php endif; ?>


<script>
let video = document.getElementById("camera-preview");
let referenceImage = document.getElementById("reference-image");
let statusDiv = document.getElementById("verification-status");
let canvas = document.createElement('canvas');
let stream = null;

async function startCamera() {
    try {
        document.getElementById("camera-container").style.display = "block";
        statusDiv.textContent = "Loading face detection models...";

        // Load models
        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri('models'),
            faceapi.nets.faceRecognitionNet.loadFromUri('models'),
            faceapi.nets.faceLandmark68Net.loadFromUri('models')
        ]);

        statusDiv.textContent = "Starting camera...";

        // Setup video stream
        stream = await navigator.mediaDevices.getUserMedia({ 
            video: { 
                facingMode: 'user',
                width: { ideal: 640 },
                height: { ideal: 480 }
            } 
        });
        video.srcObject = stream;

        // Setup canvas
        canvas.style.position = 'absolute';
        canvas.style.top = '0';
        canvas.style.left = '0';
        video.parentElement.appendChild(canvas);

        // Wait for video to be ready
        await new Promise(resolve => video.onloadedmetadata = resolve);
        video.play();

        // Set canvas dimensions to match video
        const { videoWidth, videoHeight } = video;
        canvas.width = videoWidth;
        canvas.height = videoHeight;

        // Start face recognition
        recognizeFace();
    } catch (err) {
        statusDiv.textContent = `Camera error: ${err.message}`;
        console.error(err);
    }
}

async function recognizeFace() {
    try {
        // Get reference face descriptor
        const referenceDetection = await faceapi
            .detectSingleFace(referenceImage, new faceapi.TinyFaceDetectorOptions())
            .withFaceLandmarks()
            .withFaceDescriptor();

        if (!referenceDetection) {
            throw new Error("No face detected in reference image");
        }

        const faceMatcher = new faceapi.FaceMatcher(referenceDetection, 0.6);
        const context = canvas.getContext('2d');

        const processFrame = async () => {
            // Clear previous drawings
            context.clearRect(0, 0, canvas.width, canvas.height);

            // Detect face in current frame
            const detection = await faceapi
                .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
                .withFaceLandmarks()
                .withFaceDescriptor();

            if (detection) {
                // Draw detection box
                const dims = faceapi.matchDimensions(canvas, video, true);
                const resizedDetection = faceapi.resizeResults(detection, dims);
                faceapi.draw.drawDetections(canvas, [resizedDetection]);

                // Check for match
                const match = faceMatcher.findBestMatch(detection.descriptor);
                const matchConfidence = (1 - match.distance) * 100;

                // Draw match percentage
                context.font = '16px Arial';
                context.fillStyle = matchConfidence > 60 ? '#4CAF50' : '#f44336';
                context.fillText(`Match: ${matchConfidence.toFixed(1)}%`, 10, 25);

                if (match.label === 'person 1' && matchConfidence > 60) {
                    statusDiv.textContent = "Face matched. Marking attendance...";

                    // Stop video stream
                    stream.getTracks().forEach(track => track.stop());
                    video.srcObject = null;

                    // Submit attendance
                    const formData = new FormData();
                    formData.append("mark_attendance", true);
                    formData.append("class_id", "<?php echo $activeClass['id']; ?>");

                    const response = await fetch("", {
                        method: "POST",
                        body: formData
                    });

                    if (response.ok) {
                        statusDiv.textContent = "✅ Attendance marked successfully!";
                        return; // Stop processing frames
                    } else {
                        throw new Error("Failed to mark attendance");
                    }
                } else {
                    statusDiv.textContent = `Face detected (${matchConfidence.toFixed(1)}% match). Keep still...`;
                }
            } else {
                statusDiv.textContent = "No face detected. Please face the camera.";
            }

            // Continue processing frames
            requestAnimationFrame(processFrame);
        };

        // Start processing frames
        processFrame();

    } catch (err) {
        statusDiv.textContent = `Error: ${err.message}`;
        console.error(err);

        // Cleanup on error
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            video.srcObject = null;
        }
    }
}
</script>
<footer>
        <p>&copy; 2024 Presence Plus. All rights reserved.</p>
    </footer>
</body>
</html>


<nav>
    <div class="nav-container">
        <a href="student-dashboard.php" class="nav-logo">Presence+</a>
        <div class="nav-links">
            <a href="student-dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
</nav>

