<?php
require 'connect.php';

header("Content-Type: text/html"); // Change back to HTML because it's followed by HTML output

$attendanceData = [];

try {
    $stmt = $conn->prepare("SELECT student_id, subject, date, status, remarks FROM attendance ORDER BY date DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    $attendanceData = $result->fetch_all(MYSQLI_ASSOC);
} catch (mysqli_sql_exception $e) {
    echo "<p style='color:red;'>Error fetching attendance: " . $e->getMessage() . "</p>";
}

session_start();
require 'connect.php';

$pendingRequests = [];
$stmt = $conn->prepare("
    SELECT ar.*, u.name AS student_name 
    FROM attendance_requests ar
    JOIN users u ON ar.student_id = u.id
    WHERE ar.status = 'pending'
    ORDER BY ar.created_at DESC
");
$stmt->execute();
$result = $stmt->get_result();
$pendingRequests = $result->fetch_all(MYSQLI_ASSOC);

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
            margin: 80px auto 0;
            padding: 2rem;
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
            gap: 0.5rem;
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
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .dashboard-card {
            background-color: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }



        .dashboard-card h3 {
            color: #2c3e50;
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
            background-color: #2c3e50;
            color: white;
        }

        .attendance-table tr:last-child td {
            border-bottom: none;
        }

        .requests-section {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .request-card {
            border: 1px solid #eee;
            border-radius: 4px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .request-card:last-child {
            margin-bottom: 0;
        }

        .request-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .request-actions {
            display: flex;
            gap: 0.5rem;
        }

        .request-button {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .approve-button {
            background-color: #2ecc71;
            color: white;
        }

        .reject-button {
            background-color: #e74c3c;
            color: white;
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

            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .action-buttons {
                width: 100%;
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
                <a href="teacher-dashboard.php">Dashboard</a>
                <a href="#">Reports</a>
                <a href="#">Settings</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Teacher Dashboard</h1>
            <div class="action-buttons">
                <a href="start-class.php" class="action-button">Take Attendance</a>
                <a href="#" class="action-button">Generate Report</a>
            </div>
        </div>

        <div class="dashboard-cards">
            <div class="dashboard-card">
                <h3>Today's Overview</h3>
                <p>Total Classes: 4</p>
                <p>Students Present: 45</p>
                <p>Students Absent: 5</p>
            </div>
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
        </div>

        <h2>Recent Attendance Records</h2>
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Class</th>
                    <th>Subject</th>
                    <th>Present</th>
                    <th>Absent</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
<?php if (!empty($attendanceData)): ?>
    <?php foreach ($attendanceData as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['date']) ?></td>
            <td><?= htmlspecialchars($row['student_id']) ?></td> <!-- Replace with class if available -->
            <td><?= htmlspecialchars($row['subject']) ?></td>
            <td><?= $row['status'] === 'Present' ? '1' : '0' ?></td> <!-- Placeholder -->
            <td><?= $row['status'] === 'Absent' ? '1' : '0' ?></td>  <!-- Placeholder -->
            <td><a href="#" class="action-button">View Details</a></td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="6">No attendance records found.</td>
    </tr>
<?php endif; ?>
</tbody>

        </table>

        <div class="requests-section">
    <h2>Pending Requests</h2>
    <?php if (!empty($pendingRequests)): ?>
        <?php foreach ($pendingRequests as $req): ?>
            <div class="request-card">
                <div class="request-header">
                    <h3><?= ucfirst($req['type']) ?> Request</h3>
                    <div class="request-actions">
                        <button class="request-button approve-button">Approve</button>
                        <button class="request-button reject-button">Reject</button>
                    </div>
                </div>
                <p><strong>Student:</strong> <?= htmlspecialchars($req['student_name']) ?></p>
                <p><strong>Date:</strong> <?= $req['date'] ?></p>
                <p><strong>Reason:</strong> <?= htmlspecialchars($req['reason']) ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No pending requests.</p>
    <?php endif; ?>
</div>

<footer>
        <p>&copy; 2024 Presence Plus. All rights reserved.</p>
    </footer>
</body>
</html>