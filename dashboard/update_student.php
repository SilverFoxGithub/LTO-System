<?php
require_once '../db/db_connect.php';

// Log the request method for debugging
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $student_id = trim($_POST['student_id'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $role = trim($_POST['role'] ?? '');

    // Log received data for debugging
    error_log("Received: student_id=$student_id, full_name=$full_name, role=$role");

    // Validate inputs
    if (empty($student_id) || empty($full_name) || empty($role)) {
        echo "All fields are required.";
        exit;
    }

    // Validate role (ensure it matches enum values)
    $valid_roles = ['student', 'instructor', 'admin'];
    if (!in_array($role, $valid_roles)) {
        echo "Invalid role value.";
        exit;
    }

    // Prepare and execute the update query
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, role = ? WHERE id = ?");
    $stmt->bind_param("ssi", $full_name, $role, $student_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo "Student updated successfully.";
        } else {
            echo "No changes made or student not found.";
        }
    } else {
        echo "Error updating student: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method: " . $_SERVER['REQUEST_METHOD'];
}
?>