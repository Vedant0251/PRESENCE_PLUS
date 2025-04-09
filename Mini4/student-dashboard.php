<?php
session_start();
require 'connect.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'student') {
    header('Location: signin.php');
    exit;
}

// Handle attendance history request via POST (JSON response)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['student_id'])) {
    header("Content-Type: application/json");

    $student_id = $_POST['student_id'];

    if (!$student_id) {
        echo json_encode(["status" => "error", "message" => "Student ID required"]);
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT date, subject, status, remarks FROM attendance WHERE student_id = ? ORDER BY date DESC");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $records = $result->fetch_all(MYSQLI_ASSOC);

        echo json_encode(["status" => "success", "attendance" => $records]);
        exit;
    } catch (mysqli_sql_exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        exit;
    }
}
?>
<?php
// Sumit Request button php code
require 'connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student_id = $_POST['student_id'];
    $type = $_POST['type'];
    $date = $_POST['date'];
    $reason = $_POST['reason'];

    if (!$student_id || !$type || !$date || !$reason) {
        die("All fields are required.");
    }

    $stmt = $conn->prepare("INSERT INTO attendance_requests (student_id, type, date, reason) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $student_id, $type, $date, $reason);
    
    if ($stmt->execute()) {
        header("Location: student-dashboard.php?success=1");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presence Plus - Student Dashboard</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
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

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1rem;
            background-color: #e74c3c;
            border-radius: 8px;
            color: white;
        }

        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .dashboard-card {
            background-color: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-left: 4px solid #e74c3c;
        }

        .dashboard-card h3 {
            color: #c0392b;
            margin-bottom: 1rem;
        }

        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .attendance-table th,
        .attendance-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .attendance-table th {
            background-color: #e74c3c;
            color: white;
        }

        .request-form {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2c3e50;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .form-submit {
            background-color: #e74c3c;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }

        .form-submit:hover {
            background-color: #c0392b;
        }

        .search-container {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .search-input,
        .filter-select {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .search-input {
            flex: 1;
        }

        .success-message {
            background-color: #2ecc71;
            color: white;
            padding: 1rem;
            border-radius: 4px;
            margin-top: 1rem;
        }

        .qr-container {
            text-align: center;
            margin: 2rem 0;
            padding: 1rem;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        footer {
            background-color: #c0392b;
            color: white;
            text-align: center;
            padding: 2rem;
            margin-top: 4rem;
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .search-container {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <nav>
        <div class="nav-container">
            <a href="index.php" class="nav-logo">Presence Plus</a>
            <div class="nav-links">
                <a href="student-dashboard.php">Dashboard</a>
                <a href="attendance.php">Mark Attendance</a>
                <a href="#">History</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Student Dashboard</h1>
            <p>Welcome back, <?php echo htmlspecialchars($_SESSION['name'] ?? 'Student'); ?></p>
        </div>
        

        <?php
// Fetch the stored image
$stmt = $conn->prepare("SELECT reference_image FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$stmt->store_result();

$img = '';
if ($stmt->num_rows > 0) {
    $stmt->bind_result($reference_image);
    $stmt->fetch();
    if ($reference_image) {
        $img = 'data:image/jpeg;base64,' . base64_encode($reference_image);
    }
}
?>

<?php if ($img): ?>
    <div style="text-align: center; margin-bottom: 1rem;">
        <h3>Your Registered Face</h3>
        <img src="<?php echo $img; ?>" alt="Reference Face" style="width: 150px; height: auto; border-radius: 8px; border: 2px solid #2c3e50;">
    </div>
<?php endif; ?>

<div class="face-enroll-section" style="text-align: center; max-width: 400px; margin: 2rem auto;">
    <h2>Enroll Your Face</h2>
    
    <video id="video" width="320" height="240" autoplay muted style="display: none; border-radius: 8px; border: 2px solid #e74c3c;"></video>

    <div style="margin-top: 1rem;">
        <button id="start-camera-btn" class="form-submit">📷 Start Camera</button>
        <button id="capture-btn" class="form-submit">📸 Capture</button>
    </div>

    <canvas id="canvas" width="320" height="240" style="display: none;"></canvas>

    <div id="preview-container" style="margin-top: 1rem; display: none;">
        <p><strong>Preview:</strong></p>
        <img id="preview" style="border: 1px solid #ccc; border-radius: 4px;" />
    </div>

    <p id="enroll-status" style="margin-top: 1rem; color: green;"></p>
</div>


<script>
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const preview = document.getElementById('preview');
    const previewContainer = document.getElementById('preview-container');
    const statusText = document.getElementById('enroll-status');
    const startCameraBtn = document.getElementById('start-camera-btn');

// Only start the camera when Start Camera is clicked
startCameraBtn.addEventListener('click', () => {
    video.style.display = 'block';
    startCamera();
});

    let stream = null;

    // Start camera
    async function startCamera() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: true });
            video.srcObject = stream;
        } catch (error) {
            statusText.innerText = "Unable to access camera.";
            console.error(error);
        }
    }

    // Stop camera
    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
    }

    // Handle capture
    const captureBtn = document.getElementById('capture-btn');

    captureBtn.addEventListener('click', async function () {
    if (!video.srcObject || video.readyState < 2) {
        statusText.innerText = "⏳ Waiting for camera to be ready...";
        return;
    }

    const ctx = canvas.getContext('2d');
    await new Promise(resolve => setTimeout(resolve, 300)); // brief delay
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

    stopCamera();
    video.style.display = 'none';

    const dataUrl = canvas.toDataURL('image/jpeg');
    preview.src = dataUrl;
    previewContainer.style.display = 'block';

    // Upload the reference image
    statusText.innerText = "Uploading...";
    fetch('upload-reference.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'image=' + encodeURIComponent(dataUrl)
    })
    .then(res => res.text())
    .then(response => {
        statusText.innerText = response;
    })
    .catch(err => {
        statusText.innerText = "❌ Error uploading image.";
        console.error(err);
    });
});


        // Stop video
        stopCamera();
        video.style.display = 'none';

        // Show preview
        const dataUrl = canvas.toDataURL('image/jpeg');
        preview.src = dataUrl;
        previewContainer.style.display = 'block';

</script>


        <div class="dashboard-cards">
            <div class="dashboard-card">
                <h3>Attendance Overview</h3>
                <p>Present: -</p>
                <p>Absent: -</p>
                <p>Late: -</p>
            </div>
            <div class="dashboard-card">
                <h3>Today's Schedule</h3>
                <p>Mathematics - 9:00 AM</p>
                <p>Physics - 11:00 AM</p>
                <p>Computer Science - 2:00 PM</p>
            </div>
            <div class="dashboard-card">
                <h3>Upcoming Events</h3>
                <p>Mid-term Exam - Next Week</p>
                <p>Project Submission - 15th May</p>
            </div>
        </div>


<!-- ✅ Load face-api.js -->
<!-- ✅ Correct browser version of face-api.js -->

  <title>Face Recognition</title>
  <script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
</head>
<body>

  <script>
   async function loadModels() {
  console.log("Loading face-api models...");
  await faceapi.nets.tinyFaceDetector.loadFromUri('models');
  await faceapi.nets.faceLandmark68Net.loadFromUri('models');
  await faceapi.nets.faceRecognitionNet.loadFromUri('models');
  console.log("✅ All models loaded");
}


async function startFaceRecognition() {
  const video = document.getElementById('liveVideo');
  const status = document.getElementById('match-status');

  await loadModels();

  const stream = await navigator.mediaDevices.getUserMedia({ video: true });
  video.srcObject = stream;

  const displaySize = { width: video.width, height: video.height };
  faceapi.matchDimensions(video, displaySize);

  const referenceImg = new Image();
referenceImg.src = "get-reference-image.php";

await new Promise(resolve => {
    referenceImg.onload = resolve;
    referenceImg.onerror = () => {
        status.innerText = "❌ Could not load reference image.";
    };
});

const referenceDetection = await faceapi.detectSingleFace(referenceImg, new faceapi.TinyFaceDetectorOptions())
    .withFaceLandmarks()
    .withFaceDescriptor();


  if (!referenceDetection) {
    status.innerText = "❌ Reference face not detected.";
    return;
  }

  const faceMatcher = new faceapi.FaceMatcher(
    [new faceapi.LabeledFaceDescriptors("student", [referenceDetection.descriptor])],
    0.6
  );

  status.innerText = "📷 Matching your face...";

  const interval = setInterval(async () => {
    const detection = await faceapi
      .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({inputSize : 512}))
      .withFaceLandmarks()
      .withFaceDescriptor();

    if (detection) {
      const bestMatch = faceMatcher.findBestMatch(detection.descriptor);
      if (bestMatch.label === "student") {
        clearInterval(interval);
        stream.getTracks().forEach(track => track.stop());
        status.innerText = "✅ Face verified! Marking attendance...";

        // 🔥 Call backend to store attendance
        fetch("attendance.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `student_id=<?php echo $_SESSION['id']; ?>&status=present`
})
.then(res => res.json())
.then(data => {
    if (data.status === "success") {
        document.getElementById("match-status").innerText = data.message;
        appendAttendanceRow(data.date, data.subject, data.statusText, data.remarks);
    } else {
        document.getElementById("match-status").innerText = data.message;
    }
})
.catch(err => {
    console.error("Attendance error:", err);
    document.getElementById("match-status").innerText = "❌ Failed to mark attendance.";
});

      } else {
        status.innerText = "❌ Face doesn't match. Try again.";
      }
    }
  }, 1000);
}

  </script>



<div class="mark-attendance-section" style="text-align: center;">
    <h2>Mark Attendance with Face Recognition</h2>
    <video id="liveVideo" width="320" height="240" autoplay muted style="border: 2px solid #333; border-radius: 8px;"></video>
    <p id="match-status"></p>
    <button onclick="startFaceRecognition()" class="form-submit">Start Verification</button>
</div>



<script>
    async function startCamera() {
        try {
            const video = document.getElementById('liveVideo');
            const stream = await navigator.mediaDevices.getUserMedia({ video: true });
            video.srcObject = stream;
        } catch (e) {
            console.error("Camera error:", e);
        }
    }
</script>
<script>
function appendAttendanceRow(date, subject, statusText, remarks) {
    const tbody = document.getElementById('attendance-body');
    const row = document.createElement('tr');

    row.innerHTML = `
        <td>${date}</td>
        <td>${subject}</td>
        <td>${statusText}</td>
        <td>${remarks}</td>
    `;

    tbody.prepend(row);
}
</script>



<h2>Recent Attendance</h2>
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody id="attendance-body">

<?php if (!empty($attendanceRecords)): ?>
    <?php foreach ($attendanceRecords as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['date']) ?></td>
            <td><?= htmlspecialchars($row['subject']) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td><?= htmlspecialchars($row['remarks'] ?? '-') ?></td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="4">No attendance records found.</td>
    </tr>
<?php endif; ?>

</tbody>

        </table>

        <div class="request-form">
    <h2>Submit Attendance Request</h2>
    <form method="POST" action="submit-request.php">
        <!-- Hidden input to pass the logged-in student's ID -->
        <input type="hidden" name="student_id" value="<?php echo $_SESSION['id']; ?>">

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

<script>
    // Set default date to today
    document.getElementById('request-date').valueAsDate = new Date();
</script>

    <footer>
        <p>&copy; 2024 Presence Plus. All rights reserved.</p>
    </footer>
    <script>
    window.addEventListener('DOMContentLoaded', () => {
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const preview = document.getElementById('preview');
        const previewContainer = document.getElementById('preview-container');
        const statusText = document.getElementById('enroll-status');
        const captureBtn = document.getElementById('capture-btn');
        const startCameraBtn = document.getElementById('start-camera-btn');

        let stream = null;

        // Start camera
        async function startCamera() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: true });
                video.srcObject = stream;
                video.style.display = 'block';
                captureBtn.disabled = false;
                statusText.innerText = "Camera is live.";
            } catch (error) {
                statusText.innerText = "❌ Unable to access camera.";
                console.error(error);
            }
        }

        // Stop camera
        function stopCamera() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                video.srcObject = null;
            }
            captureBtn.disabled = true;
        }

        // Start camera button
        startCameraBtn.addEventListener('click', () => {
            startCamera();
        });

        // Capture and upload image
        captureBtn.addEventListener('click', async () => {
            if (!video.srcObject || video.readyState < 2) {
                statusText.innerText = "⏳ Waiting for camera...";
                return;
            }

            const ctx = canvas.getContext('2d');

            // Ensure frame is ready
            await new Promise(resolve => setTimeout(resolve, 300));
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            stopCamera();
            video.style.display = 'none';

            const dataUrl = canvas.toDataURL('image/jpeg');
            preview.src = dataUrl;
            previewContainer.style.display = 'block';

            statusText.innerText = "Uploading...";

            fetch('upload-reference.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'image=' + encodeURIComponent(dataUrl)
            })
            .then(res => res.text())
            .then(response => {
                statusText.innerText = response;
            })
            .catch(err => {
                statusText.innerText = "❌ Error uploading image.";
                console.error(err);
            });
        });
    });
</script>

</body>
</html>