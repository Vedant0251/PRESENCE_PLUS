<?php
session_start();
require 'connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

if (!isset($_POST['image'])) {
    echo json_encode(['error' => 'No image data received']);
    exit;
}

$student_id = $_SESSION['user_id'];
$imageData = $_POST['image'];

// Validate base64 image format
if (strpos($imageData, 'data:image/') !== 0) {
    echo json_encode(['error' => 'Invalid image format']);
    exit;
}

// Extract image format and base64 data
$parts = explode(';base64,', $imageData);
if (count($parts) !== 2) {
    echo json_encode(['error' => 'Invalid image data format']);
    exit;
}

// Decode base64 image
$decodedImage = base64_decode($parts[1]);
if ($decodedImage === false) {
    echo json_encode(['error' => 'Failed to decode image data']);
    exit;
}

// Verify image data is valid
if (!@imagecreatefromstring($decodedImage)) {
    echo json_encode(['error' => 'Invalid image data']);
    exit;
}

// Store image in database
$stmt = $conn->prepare("UPDATE users SET reference_image = ? WHERE id = ?");
$stmt->bind_param("si", $decodedImage, $student_id);

if ($stmt->execute()) {
    // Verify the image was stored
    $checkStmt = $conn->prepare("SELECT reference_image FROM users WHERE id = ?");
    $checkStmt->bind_param("i", $student_id);
    $checkStmt->execute();
    $checkStmt->store_result();
    
    if ($checkStmt->num_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Image stored successfully']);
    } else {
        echo json_encode(['error' => 'Image stored but not verified']);
    }
    $checkStmt->close();
} else {
    echo json_encode(['error' => 'Failed to store image: ' . $stmt->error]);
}
$stmt->close();
?>
