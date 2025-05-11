<?php
session_start();
require_once 'connect.php';

header('Content-Type: application/json');

// Ensure user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Handle POST request for adding new notice
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['action']) && $data['action'] === 'delete' && isset($data['notice_id'])) {
        // Handle notice deletion
        $notice_id = intval($data['notice_id']);
        $stmt = $conn->prepare("DELETE FROM notices WHERE id = ?");
        $stmt->bind_param("i", $notice_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Notice deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting notice']);
        }
        $stmt->close();
    } else {
        // Handle notice creation
        $title = $data['title'] ?? '';
        $content = $data['content'] ?? '';
        
        if (empty($title) || empty($content)) {
            echo json_encode(['success' => false, 'message' => 'Title and content are required']);
            exit;
        }
        
        $stmt = $conn->prepare("INSERT INTO notices (title, content, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", $title, $content);
        
        if ($stmt->execute()) {
            $notice_id = $stmt->insert_id;
            echo json_encode([
                'success' => true,
                'message' => 'Notice added successfully',
                'notice' => [
                    'id' => $notice_id,
                    'title' => $title,
                    'content' => $content,
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error adding notice']);
        }
        $stmt->close();
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Handle GET request to fetch notices
    $stmt = $conn->prepare("SELECT id, title, content, created_at FROM notices ORDER BY created_at DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notices = [];
    while ($row = $result->fetch_assoc()) {
        $notices[] = $row;
    }
    
    echo json_encode(['success' => true, 'notices' => $notices]);
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>