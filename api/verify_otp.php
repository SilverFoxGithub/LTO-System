<?php
// Enable all error reporting (for debugging during development)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set the response content type to JSON
header('Content-Type: application/json');

// Start the PHP session (for future use if needed)
session_start();

// Include the database connection file
include '../db/db_connect.php'; // Make sure this file creates the `$conn` MySQLi connection object

// Get the raw POSTed JSON input and decode it into an associative array
$data = json_decode(file_get_contents("php://input"), true);

// Check if the request method is POST (only allow POST requests)
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405); // 405 = Method Not Allowed
    echo json_encode(["error" => "Invalid request method."]);
    exit;
}

// Extract form fields from the decoded JSON input, use empty string if a key is missing
$full_name = $data['full_name'] ?? '';
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';
$confirm_password = $data['confirm_password'] ?? '';
$role = $data['role'] ?? '';
$otp = $data['otp'] ?? '';

// Basic input validation – ensure none of the required fields are empty
if (!$full_name || !$email || !$password || !$confirm_password || !$role || !$otp) {
    http_response_code(400); // 400 = Bad Request
    echo json_encode(["error" => "Please fill out all fields hehe."]);
    exit;
}

// Ensure passwords match
if ($password !== $confirm_password) {
    http_response_code(400);
    echo json_encode(["error" => "Passwords do not match."]);
    exit;
}

// Prepare SQL to check if the OTP exists for the email (get the most recent one)
$stmt = $conn->prepare("SELECT otp FROM otp_table WHERE email = ? ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param("s", $email); // Bind the email to the SQL query
$stmt->execute(); // Run the query
$result = $stmt->get_result(); // Get the result set

// If no OTP was found for the email, return an error
if ($result->num_rows === 0) {
    http_response_code(404); // 404 = Not Found
    echo json_encode(["error" => "No OTP found for this email."]);
    exit;
}

// Get the stored OTP value from the result
$row = $result->fetch_assoc();
$stored_otp = $row['otp'];

// Compare the submitted OTP with the one stored in the database
if ($otp != $stored_otp) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid OTP."]);
    exit;
}

// If OTP is valid, hash the password using bcrypt for security
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// Prepare SQL to insert the new user into the `users` table
$stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $full_name, $email, $hashed_password, $role);

// Try to execute the insert query
if ($stmt->execute()) {
    // If signup succeeded, delete the OTP from the otp_table to prevent reuse
    $delete_stmt = $conn->prepare("DELETE FROM otp_table WHERE email = ?");
    $delete_stmt->bind_param("s", $email);
    $delete_stmt->execute();

    // Return success message
    http_response_code(201); // 201 = Created
    echo json_encode(["success" => "Signup successful!"]);
} else {
    // If user insert fails (e.g., duplicate email), return error
    http_response_code(500); // 500 = Internal Server Error
    echo json_encode(["error" => "Signup failed. Please try again."]);
}

// Clean up prepared statements and close DB connection
$stmt->close();
$conn->close();
?>
