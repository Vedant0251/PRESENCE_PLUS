<?php
session_start();
require 'connect.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get request data
$requestId = $_POST['request_id'] ?? null;
$action = $_POST['action'] ?? null;

if (!$requestId || !$action || !in_array($action, ['approve', 'reject'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request parameters']);
    exit;
}

try {
    // Update request status
    $stmt = $conn->prepare("UPDATE attendance_requests SET status = ? WHERE id = ?");
    $status = $action === 'approve' ? 'approved' : 'rejected';
    $stmt->bind_param('si', $status, $requestId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Request ' . $action . 'd successfully']);
    } else {
        throw new Exception('Failed to update request status');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}