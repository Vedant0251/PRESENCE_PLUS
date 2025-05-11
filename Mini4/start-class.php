<?php
session_start();
require 'connect.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: signin.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacherId = $_SESSION['user_id'];
    $year = $_POST['year'] ?? '';
    $department = $_POST['department'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $duration = 60; // Default duration in minutes

    if (!$year || !$department || !$subject) {
        die("Missing required fields.");
    }

    $className = $year . '-' . $department;
    $sessionCode = strtoupper(bin2hex(random_bytes(4)));

    $stmt = $conn->prepare("INSERT INTO class_sessions (subject, teacher_id, duration_minutes, year, session_code, class_name) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("siisss", $subject, $teacherId, $duration, $year, $sessionCode, $className);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Class started successfully!";
header("Location: teacher-dashboard.php");
exit;

    } else {

        $_SESSION['error_message'] = 'Failed to start class: ' . $stmt->error;
        header("Location: teacher-dashboard.php");
        exit;
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}
$conn->close();
?>