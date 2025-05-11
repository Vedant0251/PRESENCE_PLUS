<?php
session_start();
header('Content-Type: application/json');
require 'connect.php';



if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
        echo json_encode(["status" => "error", "message" => "Unauthorized"]);
        exit;
    }

    $student_id = $_SESSION['user_id'];
    $status = $_POST['status'] ?? 'Present';
    $subject = "Face Verification";
    $date = date("Y-m-d");
    $remarks = "-";

    // Check for existing attendance
    $check = $conn->prepare("SELECT id FROM attendance WHERE student_id = ? AND date = ? AND subject = ?");
    $check->bind_param("iss", $student_id, $date, $subject);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Attendance already marked."]);
        exit;
    }

    $insert = $conn->prepare("INSERT INTO attendance (student_id, subject, date, status, remarks) VALUES (?, ?, ?, ?, ?)");
    $insert->bind_param("issss", $student_id, $subject, $date, $status, $remarks);

    if ($insert->execute()) {
        echo json_encode(["status" => "success", "message" => "✅ Attendance marked successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Insert failed."]);
    }
}

?>
