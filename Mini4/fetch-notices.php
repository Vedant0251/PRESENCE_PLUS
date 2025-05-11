<?php
require_once 'connect.php';

try {
    // Fetch notices ordered by most recent first
    $stmt = $conn->prepare("SELECT id, title, content, created_at FROM notices ORDER BY created_at DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    $notices = $result->fetch_all(MYSQLI_ASSOC);

    // Return notices as JSON
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'notices' => $notices]);
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error fetching notices: ' . $e->getMessage()]);
}