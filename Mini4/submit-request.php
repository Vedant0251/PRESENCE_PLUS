<?php
session_start();
require 'connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student_id = $_POST['student_id'] ?? '';
    $type = $_POST['type'] ?? '';
    $date = $_POST['date'] ?? '';
    $reason = $_POST['reason'] ?? '';

    if (!$student_id || !$type || !$date || !$reason) {
        die("All fields are required.");
    }

    $stmt = $conn->prepare("INSERT INTO attendance_requests (student_id, type, date, reason) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $student_id, $type, $date, $reason);

    if ($stmt->execute()) {
        header("Location: student-dashboard.php?success=1");
        exit;
    } else {
        echo "Database error: " . $stmt->error;
    }
} else {
    echo "Invalid request method.";
}
?>
