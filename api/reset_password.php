<?php
include '../db/db_connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Ensure values are set
    $token = $_POST["token"] ?? '';
    $password = $_POST["password"] ?? '';

    if (empty($token) || empty($password)) {
        echo "Missing token or password!";
        exit();
    }

    // Hash the new password
    $newPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if token exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        echo "Invalid or expired reset token!";
        exit();
    }

    // Get user ID
    $stmt->bind_result($userId);
    $stmt->fetch();
    $stmt->close();

    // Update password and clear token
    $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL WHERE id = ?");
    $stmt->bind_param("si", $newPassword, $userId);

    if ($stmt->execute()) {
        echo "Password updated successfully! <a href='../index.html'>Login Here</a>";
    } else {
        echo "Error updating password!";
    }

    $stmt->close();
    $conn->close();
}
?>
