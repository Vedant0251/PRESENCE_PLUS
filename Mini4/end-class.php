<?php
session_start();
require 'connect.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure only teachers can end classes
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get and validate the POST data
$postData = file_get_contents('php://input');
if (!$postData) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit;
}

$data = json_decode($postData, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

$classId = $data['class_id'] ?? null;
if (!$classId || !is_numeric($classId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Valid class ID is required']);
    exit;
}

try {
    // Validate connection
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception('Database connection failed: ' . ($conn->connect_error ?? 'Connection not established'));
    }

    // Update the class status to 'ended'
    $stmt = $conn->prepare("UPDATE class_sessions SET status = 'ended', end_time = NOW() WHERE id = ? AND status = 'active'");
    if (!$stmt) {
        throw new Exception('Failed to prepare statement');
    }

    $stmt->bind_param('i', $classId);
    if (!$stmt->execute()) {
        throw new Exception('Failed to execute statement: ' . $stmt->error);
    }

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Class ended successfully']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'No active class found with the given ID']);
    }

    $stmt->close();
} catch (Exception $e) {
    error_log('Error in end-class.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}