<?php
session_start();
include 'connect.php';

header("Content-Type: image/jpeg");

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo "Unauthorized";
    exit;
}

$student_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT reference_image FROM users WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo $row['reference_image'];
} else {
    http_response_code(404);
    echo "Image not found";
}
?>
