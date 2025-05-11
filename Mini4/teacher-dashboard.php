<?php
require_once 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);

    $sql = "INSERT INTO notices (title, content) VALUES ('$title', '$content')";

    if (mysqli_query($conn, $sql)) {
        echo "Notice posted successfully.";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    echo "Invalid request.";
}
?>
<br><a href="teacher-dashboard.php">Back to Dashboard</a>

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'connect.php';

header("Content-Type: text/html"); // Change back to HTML because it's followed by HTML output

// Fetch teacher information
$userId = $_SESSION['user_id'];
$userQuery = $conn->prepare("SELECT reference_image, name, email, role FROM users WHERE id = ?");
$userQuery->bind_param("i", $userId);
$userQuery->execute();
$userQuery->bind_result($referenceImage, $userName, $userEmail, $userRole);
$userQuery->fetch();
$userQuery->close();

// Get profile image data
$imageData = null;
if ($referenceImage) {
    $imageData = base64_encode($referenceImage);
}

$attendanceData = [];
$activeClass = null;
$pendingRequests = [];

try {
    // Fetch attendance data
    $stmt = $conn->prepare("SELECT student_id, subject, date, status, remarks FROM attendance ORDER BY date DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    $attendanceData = $result->fetch_all(MYSQLI_ASSOC);

    // Fetch the most recent active class
    $classQuery = $conn->prepare("SELECT * FROM class_sessions ORDER BY start_time DESC LIMIT 1");
    $classQuery->execute();
    $activeClass = $classQuery->get_result()->fetch_assoc();

    // Fetch pending requests with student names
    $requestsQuery = $conn->prepare("SELECT ar.*, u.name as student_name 
                                   FROM attendance_requests ar 
                                   JOIN users u ON ar.student_id = u.id 
                                   WHERE ar.status = 'pending' 
                                   ORDER BY ar.created_at DESC");
    $requestsQuery->execute();
    $pendingRequests = $requestsQuery->get_result()->fetch_all(MYSQLI_ASSOC);
} catch (mysqli_sql_exception $e) {
    echo "<p style='color:red;'>Error fetching data: " . $e->getMessage() . "</p>";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presence Plus - Teacher Dashboard</title>
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
            margin: 40px auto 0;
            padding: 2rem;
        }

        .dashboard-title {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: left;
        }

        .dashboard-title h1 {
            color: #2c3e50;
            font-size: 2rem;
            margin: 0;
        }

        .start-class-section {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .start-class-section h2 {
            color: #2c3e50;
            margin-bottom: 1.5rem;
        }

        .start-class-form {
            display: grid;
            gap: 1rem;
            max-width: 500px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .form-group label {
            color: #2c3e50;
            font-weight: 500;
        }

        .form-group input, .form-group select {
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .start-class-button {
            background-color: #3498db;
            color: white;
            padding: 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        .start-class-button:hover {
            background-color: #2980b9;
        }

        .qr-container,
        .qr-info {
            display: none;
        }

        .qr-info {
            margin-top: 1rem;
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 4px;
        }

        .qr-info h3 {
            margin-bottom: 0.5rem;
            color: #2c3e50;
        }

        .qr-info p {
            margin: 0.5rem 0;
            color: #666;
        }
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .dashboard-title {
            color: #2c3e50;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
        }

        .action-button {
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            background-color: #3498db;
            transition: background-color 0.3s;
        }

        .action-button:hover {
            background-color: #2980b9;
        }

        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin-bottom: 3rem;
        }

        @media (max-width: 992px) {
            .dashboard-cards {
                grid-template-columns: 1fr;
            }
        }

        /* Profile Card Specific Styles */
        .profile-card {
            grid-column: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: left;
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(231, 76, 60, 0.1);
            border: 2px solid #e74c3c;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .profile-info p {
            margin: 0.75rem 0;
            color: #2c3e50;
            font-size: 1.1rem;
            line-height: 1.5;
        }

        .profile-info strong {
            color: #e74c3c;
            font-weight: 600;
        }

        /* Dashboard Card Specific Styles */
        .dashboard-card h3 {
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .profile-card {
                flex-direction: column;
                text-align: left;
            }
            .profile-image {
                margin-top: 1.5rem;
            }
        }

        /* General Dashboard Card Styles */
        .dashboard-cards {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .profile-card {
            grid-column: 1 / -1;
        }

        .active-class-card,
        .start-class-card,
        .dashboard-card:not(.profile-card) {
            grid-column: span 1;
        }

        @media (min-width: 992px) {
            .dashboard-cards {
                grid-template-columns: repeat(3, 1fr);
            }
            
            .profile-card {
                grid-column: 1 / -1;
            }
        }
        @media (max-width: 992px) {
            .dashboard-cards {
                grid-template-columns: 1fr;
            }
        }

        footer {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            padding: 2rem;
            margin-top: 4rem;
        }

        .dashboard-card {
            background-color: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
<?php if (isset($_SESSION['message'])): ?>
    <div id="alert-box" style="position: fixed; top: 80px; left: 50%; transform: translateX(-50%);
        background-color: #4CAF50; color: white; padding: 15px; border-radius: 5px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2); z-index: 9999; text-align: center;
        transition: opacity 0.5s ease;">
        <?php 
            echo $_SESSION['message']; 
            unset($_SESSION['message']);
        ?>
    </div>
<?php endif; ?>



    <nav>
        <div class="nav-container">
            <a href="index.php" class="nav-logo">Presence+</a>
            <div class="nav-links">
                <a href="teacher-dashboard.php">Dashboard</a>
                <a href="#">Settings</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="dashboard-title">
            <h1>Teacher Dashboard</h1>
        </div>

        <div class="dashboard-cards">
            <div class="profile-card">
                <h2 style="color: #2c3e50; margin-bottom: 1.5rem;">Profile Information</h2>
                <?php if ($imageData): ?>
                    <img src="data:image/jpeg;base64,<?= $imageData ?>" alt="Profile Picture" class="profile-image">
                <?php else: ?>
                    <img src="https://via.placeholder.com/150" alt="Default Profile Picture" class="profile-image">
                <?php endif; ?>
                <div class="profile-info">
                    <p><strong>Name:</strong> <?= htmlspecialchars($userName ?? 'Not Available') ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($userEmail ?? 'Not Available') ?></p>
                    <p><strong>Role:</strong> <?= ucfirst(htmlspecialchars($userRole ?? 'Not Available')) ?></p>
                </div>
            </div>
        </div>

        <div class="dashboard-cards">
            <div class="dashboard-card active-class-card" style="background: white; color: #2c3e50; box-shadow: 0 2px 5px rgba(0,0,0,0.1); border-radius: 8px; padding: 1.5rem; position: relative;">
                <div style="position: absolute; top: 10px; right: 10px; width: 10px; height: 10px; background-color: #2ecc71; border-radius: 50%; animation: pulse 2s infinite;"></div>
                <h3 style="margin-bottom: 1.5rem; color: #2c3e50;">Active Class</h3>
                <div style="border: 1px solid #eee; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    <?php if ($activeClass): ?>
                        <p style="margin: 0.5rem 0;"><strong>Class Name:</strong> <?= htmlspecialchars($activeClass['class_name']) ?></p>
                        <p style="margin: 0.5rem 0;"><strong>Subject:</strong> <?= htmlspecialchars($activeClass['subject']) ?></p>
                        <p style="margin: 0.5rem 0;"><strong>Year:</strong> <?= htmlspecialchars($activeClass['year']) ?></p>
                        <p style="margin: 0.5rem 0;"><strong>Started at:</strong> <?= date('h:i A', strtotime($activeClass['start_time'])) ?></p>
                    <?php else: ?>
                        <p style="margin: 0.5rem 0;">No active class session.</p>
                    <?php endif; ?>
                </div>
                <?php if ($activeClass): ?>
                    <button onclick="endClass(<?= $activeClass['id'] ?>)" style="background: #e74c3c; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 4px; cursor: pointer; font-weight: 500; transition: all 0.3s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">End Class</button>
                    <script>
                    function endClass(classId) {
                        if (confirm('Are you sure you want to end this class?')) {
                            fetch('end-class.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({ class_id: classId })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert('Class ended successfully');
                                    // Remove the active class card from the UI
                                    const activeClassCard = document.querySelector('.active-class-card');
                                    if (activeClassCard) {
                                        activeClassCard.innerHTML = '<h3 style="margin-bottom: 1.5rem; color: #2c3e50;">Active Class</h3><div style="border: 1px solid #eee; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;"><p style="margin: 0.5rem 0;">No active class session.</p></div>';
                                    }
                                } else {
                                    alert('Error: ' + data.message);
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('An error occurred while ending the class');
                            });
                        }
                    }
                    </script>
                <?php endif; ?>
            </div>

            <div class="dashboard-card start-class-card">
                <h3>Start a New Class</h3>
                <form action="start-class.php" method="POST">
                    <div class="form-group">
                        <label for="year">Year:</label>
                        <select id="year" name="year" required>
                            <option value="">Select Year</option>
                            <option value="SY">SY</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="department">Department:</label>
                        <select id="department" name="department" required>
                            <option value="">Select Department</option>
                            <option value="COMPS">Computer Science</option>
                            <option value="IT">Information Technology</option>
                            <option value="EXTC">Electronics & Telecom</option>
                            <option value="AIDS">AI & Data Science</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject:</label>
                        <select id="subject" name="subject" required>
                            <option value="">Select Subject</option>
                            <option value="DSA">DSA</option>
                            <option value="DBMS">DBMS</option>
                            <option value="OS">OS</option>
                            <option value="CN">CN</option>
                            <option value="MATHS">MATHS</option>
                        </select>
                    </div>
                    <button type="submit" class="start-class-button">Start Class</button>
                </form>
            </div>

            <div class="dashboard-card notices-card">
                <h3>Notices</h3>
                <form id="notice-form" class="notice-form" style="margin-bottom: 1rem;">
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <input type="text" id="notice-title" name="title" placeholder="Notice Title" required style="width: 100%; padding: 0.5rem; margin-bottom: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                        <textarea id="notice-content" name="content" placeholder="Notice Content" required style="width: 100%; padding: 0.5rem; height: 100px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
                    </div>
                    <button type="submit" style="background: #3498db; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer;">Add Notice</button>
                </form>
                <div id="notices-list" style="max-height: 300px; overflow-y: auto;">
                    <p style='text-align: center; color: #7f8c8d;'>Loading notices...</p>
                </div>

                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    loadNotices();

                    document.getElementById('notice-form').addEventListener('submit', function(e) {
                        e.preventDefault();
                        const title = document.getElementById('notice-title').value;
                        const content = document.getElementById('notice-content').value;

                        fetch('handle-notice.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                title: title,
                                content: content
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('notice-title').value = '';
                                document.getElementById('notice-content').value = '';
                                loadNotices();
                            } else {
                                alert('Error: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while adding the notice');
                        });
                    });
                });

                function loadNotices() {
                    fetch('handle-notice.php')
                        .then(response => response.json())
                        .then(data => {
                            const noticesList = document.getElementById('notices-list');
                            if (data.success && data.notices.length > 0) {
                                noticesList.innerHTML = data.notices.map(notice => `
                                    <div class="notice-item" style="border-bottom: 1px solid #eee; padding: 1rem; margin-bottom: 0.5rem;">
                                        <div style="display: flex; justify-content: space-between; align-items: start;">
                                            <h4 style="margin: 0; color: #2c3e50;">${notice.title}</h4>
                                            <button onclick="deleteNotice(${notice.id})" style="background: none; border: none; color: #e74c3c; cursor: pointer;">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </div>
                                        <p style="margin: 0.5rem 0; color: #34495e;">${notice.content}</p>
                                        <small style="color: #7f8c8d;">${new Date(notice.created_at).toLocaleString()}</small>
                                    </div>
                                `).join('');
                            } else {
                                noticesList.innerHTML = "<p style='text-align: center; color: #7f8c8d;'>No notices available.</p>";
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            document.getElementById('notices-list').innerHTML = "<p style='text-align: center; color: #e74c3c;'>Error loading notices.</p>";
                        });
                }

                function deleteNotice(noticeId) {
                    if (confirm('Are you sure you want to delete this notice?')) {
                        fetch('handle-notice.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                action: 'delete',
                                notice_id: noticeId
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                loadNotices();
                            } else {
                                alert('Error: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while deleting the notice');
                        });
                    }
                }
                </script>
            </div>
        </div>

         <!-- <div class="dashboard-cards">
            <div class="dashboard-card">
                <h3>Upcoming Classes</h3>
                <p>Class 10A - Mathematics (10:00 AM)</p>
                <p>Class 11B - Physics (1:00 PM)</p>
                <p>Class 12A - Computer Science (3:00 PM)</p>
            </div>
            <div class="dashboard-card">
                <h3>Pending Actions</h3>
                <p>Attendance Requests: 2</p>
                <p>Leave Applications: 2</p>    
                <p>Reports Due: 1</p>
            </div> 
        </div>  -->
 
        <div class="requests-section" style="margin-top: 2rem;">
    <h2 style="color: #2c3e50; margin-bottom: 1.5rem;">Pending Requests</h2>
    <?php if (!empty($pendingRequests)): ?>
        <div style="display: grid; gap: 1.5rem;">
        <?php foreach ($pendingRequests as $req): ?>
            <div class="request-card" data-request-id="<?= $req['id'] ?>" style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                <div class="request-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h3 style="color: #2c3e50; margin: 0;"><?= ucfirst($req['type']) ?> Request</h3>
                    <div class="request-actions" style="display: flex; gap: 1rem;">
                        <button class="request-button approve-button" style="background: #2ecc71; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">Approve</button>
                        <button class="request-button reject-button" style="background: #e74c3c; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">Reject</button>
                    </div>
                </div>
                <div style="display: grid; gap: 0.5rem;">
                    <p style="margin: 0;"><strong>Student:</strong> <?= htmlspecialchars($req['student_name']) ?></p>
                    <p style="margin: 0;"><strong>Date:</strong> <?= date('F j, Y', strtotime($req['date'])) ?></p>
                    <p style="margin: 0;"><strong>Reason:</strong> <?= htmlspecialchars($req['reason']) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="text-align: center; color: #7f8c8d; padding: 2rem;">No pending requests.</p>
    <?php endif; ?>
</div>

<footer style="background-color: #c0392b; color: white; padding: 1.5rem 0; text-align: center; width: 100%; margin-top: 3rem;">
        <div style="max-width: 1200px; margin: 0 auto;">
            <p>&copy; 2024 Presence Plus. All rights reserved.</p>
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Function to fetch and display notices
        function fetchNotices() {
            fetch('fetch-notices.php')
                .then(response => response.json())
                .then(data => {
                    const noticesList = document.getElementById('notices-list');
                    if (data.success && data.notices.length > 0) {
                        noticesList.innerHTML = data.notices.map(notice => `
                            <div class="notice-item" style="background: white; padding: 1rem; margin-bottom: 1rem; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); position: relative;">
                                <button onclick="deleteNotice(${notice.id})" style="position: absolute; top: 10px; right: 10px; background: #e74c3c; border: none; color: white; cursor: pointer; font-size: 12px; padding: 4px 8px; border-radius: 4px;">Remove</button>
                                <h4 style="color: #2c3e50; margin-bottom: 0.5rem; padding-right: 20px;">${notice.title}</h4>
                                <p style="color: #7f8c8d; margin-bottom: 0.5rem;">${notice.content}</p>
                                <small style="color: #95a5a6;">${new Date(notice.created_at).toLocaleString()}</small>
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

        // Function to delete a notice
        function deleteNotice(noticeId) {
            fetch('delete-notice.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ notice_id: noticeId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    fetchNotices();
                } else {
                    alert('Error deleting notice: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting notice');
            });
        }

        // Handle notice form submission
        document.getElementById('notice-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('teacher-dashboard.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                if (data.includes('Notice posted successfully')) {
                    this.reset();
                    fetchNotices();
                } else {
                    alert('Error posting notice');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error posting notice');
            });
        });

        // Handle request actions
        document.querySelectorAll('.request-button').forEach(button => {
            button.addEventListener('click', function() {
                const requestCard = this.closest('.request-card');
                const requestId = requestCard.dataset.requestId;
                const action = this.classList.contains('approve-button') ? 'approve' : 'reject';

                const formData = new FormData();
                formData.append('request_id', requestId);
                formData.append('action', action);

                fetch('handle-request.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        requestCard.style.opacity = '0';
                        setTimeout(() => {
                            requestCard.remove();
                            // Check if there are no more requests
                            const remainingRequests = document.querySelectorAll('.request-card');
                            if (remainingRequests.length === 0) {
                                document.querySelector('.requests-section').innerHTML = 
                                    '<h2 style="color: #2c3e50; margin-bottom: 1.5rem;">Pending Requests</h2>' +
                                    '<p style="text-align: center; color: #7f8c8d; padding: 2rem;">No pending requests.</p>';
                            }
                        }, 300);
                    } else {
                        alert('Failed to process request: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while processing the request.');
                });
            });
        });

        // Handle request actions
        document.querySelectorAll('.request-button').forEach(button => {
            button.addEventListener('click', function() {
                const requestCard = this.closest('.request-card');
                const requestId = requestCard.dataset.requestId;
                const action = this.classList.contains('approve-button') ? 'approve' : 'reject';

                const formData = new FormData();
                formData.append('request_id', requestId);
                formData.append('action', action);

                fetch('handle-request.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        requestCard.style.opacity = '0';
                        setTimeout(() => {
                            requestCard.remove();
                            // Check if there are no more requests
                            const remainingRequests = document.querySelectorAll('.request-card');
                            if (remainingRequests.length === 0) {
                                const requestsSection = document.querySelector('.requests-section');
                                requestsSection.innerHTML = '<p style="text-align: center; color: #7f8c8d; padding: 2rem;">No pending requests.</p>';
                            }
                        }, 300);
                    } else {
                        alert('Failed to process request: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while processing the request');
                });
            });
        });

        // Function to delete a notice
        function deleteNotice(noticeId) {
            if (confirm('Are you sure you want to delete this notice?')) {
                fetch('delete-notice.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ notice_id: noticeId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        fetchNotices();
                    } else {
                        alert('Error deleting notice: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting notice');
                });
            }
        }

        // Handle notice form submission
        document.getElementById('notice-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('title', document.getElementById('notice-title').value);
            formData.append('content', document.getElementById('notice-content').value);

            fetch('handle-notice.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const noticesList = document.getElementById('notices-list');
                    const noNoticesMsg = noticesList.querySelector('p');
                    if (noNoticesMsg && noNoticesMsg.textContent === 'No notices available.') {
                        noticesList.innerHTML = '';
                    }

                    const noticeHtml = `
                        <div class='notice-item' style='background: #f8f9fa; padding: 1rem; margin-bottom: 0.5rem; border-radius: 4px;'>
                            <h4 style='margin: 0 0 0.5rem 0; color: #2c3e50;'>${data.notice.title}</h4>
                            <p style='margin: 0 0 0.5rem 0;'>${data.notice.content}</p>
                            <div style='font-size: 0.8rem; color: #7f8c8d;'>
                                Posted by ${document.querySelector('.profile-info p:first-child').textContent.split(':')[1].trim()} on ${new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                                <button onclick='deleteNotice(${data.notice.id})' style='float: right; background: #e74c3c; color: white; border: none; padding: 0.25rem 0.5rem; border-radius: 4px; cursor: pointer;'>Delete</button>
                            </div>
                        </div>
                    `;
                    noticesList.insertAdjacentHTML('afterbegin', noticeHtml);
                    document.getElementById('notice-form').reset();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        });

        // Handle approve/reject buttons
        document.querySelectorAll('.request-button').forEach(button => {
            button.addEventListener('click', function() {
                const requestCard = this.closest('.request-card');
                const requestId = requestCard.dataset.requestId;
                const action = this.classList.contains('approve-button') ? 'approve' : 'reject';

                // Send AJAX request
                const formData = new FormData();
                formData.append('request_id', requestId);
                formData.append('action', action);

                fetch('handle-request.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the request card with animation
                        requestCard.style.opacity = '0';
                        setTimeout(() => {
                            requestCard.remove();
                            // Check if there are no more requests
                            const remainingRequests = document.querySelectorAll('.request-card');
                            if (remainingRequests.length === 0) {
                                document.querySelector('.requests-section').innerHTML = 
                                    '<p style="text-align: center; color: #7f8c8d; padding: 2rem;">No pending requests.</p>';
                            }
                        }, 300);
                    } else {
                        throw new Error(data.error || 'Failed to process request');
                    }
                })
                .catch(error => {
                    alert('Error: ' + error.message);
                });
            });
        });
    });
    </script>
 <footer>
        <p>&copy; 2024 Presence Plus. All rights reserved.</p>
    </footer>
</body>
</html>





<script>
function deleteNotice(noticeId) {
    if (confirm('Are you sure you want to delete this notice?')) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('notice_id', noticeId);

        fetch('handle-notice.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const noticeElement = document.querySelector(`[onclick="deleteNotice(${noticeId})"]`).closest('.notice-item');
                noticeElement.style.opacity = '0';
                setTimeout(() => {
                    noticeElement.remove();
                    const noticesList = document.getElementById('notices-list');
                    if (noticesList.children.length === 0) {
                        noticesList.innerHTML = '<p style="text-align: center; color: #7f8c8d;">No notices available.</p>';
                    }
                }, 300);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    }
}
</script>

<style>
@keyframes pulse {
    0% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.2); }
    100% { opacity: 1; transform: scale(1); }
}

.dashboard-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.dashboard-card {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    min-height: 400px;
}

.active-class-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
}

.active-class-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(46, 204, 113, 0.2);
}

.notice-item {
    transition: opacity 0.3s ease;
}

.notices-card {
    transition: all 0.3s ease;
}

.notices-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(52, 152, 219, 0.2);
}

#notices-list {
    flex: 1;
    overflow-y: auto;
    max-height: 300px;
    padding-right: 0.5rem;
}

.notice-form {
    margin-bottom: 1rem;
}

.notice-form button:hover {
    background: #2980b9;
    transform: translateY(-2px);
    transition: all 0.3s ease;
}
</style>

</html>




