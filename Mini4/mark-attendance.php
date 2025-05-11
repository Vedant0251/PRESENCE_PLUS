<?php
session_start();
require 'connect.php'; // Ensure this path is correct and connect.php establishes $conn
header('Content-Type: application/json');

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['error' => 'Unauthorized access. Please log in as a student.']);
    exit;
}

// Get data from the request body
$data = json_decode(file_get_contents("php://input"), true);

// Check if face verification was successful
if (!isset($data['verified']) || $data['verified'] !== true) {
    echo json_encode(['error' => 'Face verification failed or not provided.']);
    exit;
}

$student_id = $_SESSION['user_id'];
$class_session_id = isset($data['class_id']) ? (int)$data['class_id'] : null;
$date = date('Y-m-d');
$status = 'Present'; // Status for verified attendance
$subject = 'Face Scan Verification'; // Subject indicating how attendance was marked
$remarks = 'Auto-marked by face verification system.'; // Additional remarks

// Validate class_session_id
if ($class_session_id === null || $class_session_id <= 0) {
    echo json_encode(['error' => 'Valid Class Session ID not provided.']);
    exit;
}

// Check for existing attendance for this student, class session, and date to prevent duplicates
// This assumes the combination of student_id, class_session_id, and date should be unique.
$check_stmt = $conn->prepare("SELECT id FROM attendance WHERE student_id = ? AND class_session_id = ? AND date = ?");
if (!$check_stmt) {
    echo json_encode(['error' => 'Database error (prepare check): ' . $conn->error]);
    exit;
}
$check_stmt->bind_param("iis", $student_id, $class_session_id, $date);
if (!$check_stmt->execute()) {
    echo json_encode(['error' => 'Database error (execute check): ' . $check_stmt->error]);
    $check_stmt->close();
    exit;
}
$check_stmt->store_result();

if ($check_stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Attendance already marked for this class session today.']);
    $check_stmt->close();
    $conn->close();
    exit;
}
$check_stmt->close();

// Prepare statement for inserting attendance
// Assumes attendance table has columns: student_id, class_session_id, date, status, subject, remarks
$insert_stmt = $conn->prepare("INSERT INTO attendance (student_id, class_session_id, date, status, subject, remarks) VALUES (?, ?, ?, ?, ?, ?)");
if (!$insert_stmt) {
    echo json_encode(['error' => 'Database error (prepare insert): ' . $conn->error]);
    $conn->close();
    exit;
}
$insert_stmt->bind_param("iissss", $student_id, $class_session_id, $date, $status, $subject, $remarks);

if ($insert_stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Attendance marked successfully via face verification.']);
} else {
    // Check for specific errors like duplicate entry if the earlier check somehow missed it or unique constraint is different
    if ($conn->errno == 1062) { // MySQL error code for duplicate entry
        echo json_encode(['success' => false, 'error' => 'Attendance already marked (duplicate entry).']);
    } else {
        echo json_encode(['error' => 'Failed to mark attendance: ' . $insert_stmt->error]);
    }
}

$insert_stmt->close();
$conn->close();
?>
