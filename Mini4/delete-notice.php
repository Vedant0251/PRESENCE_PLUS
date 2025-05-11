<?php
require_once 'connect.php';

// Get the notice ID from the POST request
$data = json_decode(file_get_contents('php://input'), true);
$noticeId = isset($data['notice_id']) ? intval($data['notice_id']) : 0;

if ($noticeId > 0) {
    // Prepare and execute the delete query
    $stmt = $conn->prepare("DELETE FROM notices WHERE id = ?");
    $stmt->bind_param("i", $noticeId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Notice deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting notice']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid notice ID']);
}

$conn->close();
?>