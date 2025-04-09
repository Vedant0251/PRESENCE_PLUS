<?php
session_start();
require 'connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student_id = $_POST['student_id'] ?? '';
    $status = $_POST['status'] ?? 'present';
    $subject = "Face Verification";  // Or dynamically set this based on the class

    if (!$student_id) {
        echo json_encode(["status" => "error", "message" => "Student ID required."]);
        exit;
    }

    $date = date("Y-m-d");
    $remarks = "-";

    // Prevent duplicate attendance for the same date and subject
    $stmt = $conn->prepare("SELECT id FROM attendance WHERE student_id = ? AND date = ? AND subject = ?");
    $stmt->bind_param("iss", $student_id, $date, $subject);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Attendance already marked for today."]);
        exit;
    }

    // Insert attendance
    $insert = $conn->prepare("INSERT INTO attendance (student_id, date, subject, status, remarks) VALUES (?, ?, ?, ?, ?)");
    $insert->bind_param("issss", $student_id, $date, $subject, $status, $remarks);

    if ($insert->execute()) {
        echo json_encode([
            "status" => "success",
            "message" => "✅ Attendance marked successfully!",
            "date" => $date,
            "subject" => $subject,
            "statusText" => ucfirst($status),
            "remarks" => $remarks
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to insert attendance."]);
    }
}
?>
