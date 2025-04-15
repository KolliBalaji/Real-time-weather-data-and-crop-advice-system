<?php
$conn = new mysqli("localhost", "root", "", "weather_crop_system");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $phone = $_POST["phone"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (name, phone, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $phone, $password);
    $stmt->execute();
    $stmt->close();

    header("Location: login.html");
    exit();
}
?>
