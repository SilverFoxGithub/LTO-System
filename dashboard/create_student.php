<?php
require_once '../db/db_connect.php';

// Log the request method for debugging
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Log received data for debugging
    error_log("Received: full_name=$full_name, email=$email");

    // Validate inputs
    if (empty($full_name) || empty($email) || empty($password)) {
        echo "All fields are required.";
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format.";
        exit;
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo "Email already exists.";
        exit;
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare and execute the insert query
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, 'student')");
    $stmt->bind_param("sss", $full_name, $email, $hashed_password);

    if ($stmt->execute()) {
        echo "Student created successfully.";
    } else {
        echo "Error creating student: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method: " . $_SERVER['REQUEST_METHOD'];
}
?>