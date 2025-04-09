<?php
session_start();
require 'connect.php';

$stmt = $conn->prepare("SELECT reference_image FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($image);
    $stmt->fetch();
    header("Content-Type: image/jpeg");
    echo $image;
} else {
    http_response_code(404);
}
