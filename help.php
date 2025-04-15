<?php
// backend/help.php

$host = "localhost";
$user = "root";
$password = "";  // Default for XAMPP
$dbname = "crop_advice_system";

// Create DB connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get POST data
$name = htmlspecialchars($_POST['name']);
$phone = htmlspecialchars($_POST['phone']);
$message = htmlspecialchars($_POST['message']);

// Prepare and execute SQL
$sql = "INSERT INTO help_requests (name, phone, message) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $name, $phone, $message);

if ($stmt->execute()) {
    echo "<script>alert('Help request submitted successfully!'); window.location.href='help.html';</script>";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
