<?php
session_start();
require 'connect.php';

if (!isset($_SESSION['id'])) {
    die("User not authenticated.");
}

if (!isset($_POST['image'])) {
    die("No image data received.");
}

$student_id = $_SESSION['id'];
$imageData = $_POST['image'];

// Decode base64 image
$imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
$imageData = str_replace(' ', '+', $imageData);
$decodedImage = base64_decode($imageData);

$stmt = $conn->prepare("UPDATE users SET reference_image = ? WHERE id = ?");
$stmt->bind_param("bi", $null, $student_id);
$stmt->send_long_data(0, $decodedImage);

if ($stmt->execute()) {
    echo "✅ Image stored successfully in database!";
} else {
    echo "❌ Failed to store image: " . $stmt->error;
}
?>
