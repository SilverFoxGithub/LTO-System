<?php
header("Content-Type: application/json"); // Set response type to JSON

// Start session (not required just for OTP sending, but added in case needed later)
session_start();

// Include database connection
include '../db/db_connect.php'; // Ensure this file defines `$conn`

// Read and decode JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Check if required fields are present
if (!isset($data['email']) || empty($data['email'])) {
    echo json_encode(["status" => "error", "message" => "Email address is required."]);
    exit;
}

// Assign values
$email = $data['email'];
$phone = isset($data['phone']) ? $data['phone'] : ''; // Optional, in case needed later

// Initialize PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';
require 'PHPMailer-master/src/Exception.php';

$mail = new PHPMailer(true);

try {
    // SMTP configuration
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'noreplymssb@gmail.com'; // Your Gmail address
    $mail->Password = 'ysnbdwtsvnfifkfp'; // Google App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Email setup
    $mail->setFrom('noreplymssb@gmail.com', 'LTO Support');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = "Your OTP Code";
    $otp = rand(100000, 999999); // Generate a 6-digit OTP
    $mail->Body = "Your OTP code is: <b>$otp</b>";

    // Attempt to send the email
    if ($mail->send()) {
        // ✅ Insert OTP into database
        $stmt = $conn->prepare("INSERT INTO otp_table (email, otp, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", $email, $otp);
        $stmt->execute();
        $stmt->close();
        $conn->close();

        echo json_encode(["status" => "success", "message" => "OTP sent successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Mailer Error: " . $mail->ErrorInfo]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Mailer Exception: " . $e->getMessage()]);
}
?>
