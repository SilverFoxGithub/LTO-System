<?php
include '../db/db_connect.php';
session_start();

// Make sure to include PHPMailer classes with correct paths
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';
require 'PHPMailer-master/src/Exception.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];

    // Check if email exists in the users table
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        echo "Email not found!";
        exit();
    }

    // Generate a secure random token for reset
    $token = bin2hex(random_bytes(50));

    // Save the reset token in the database for this user
    $stmt = $conn->prepare("UPDATE users SET reset_token = ? WHERE email = ?");
    $stmt->bind_param("ss", $token, $email);
    $stmt->execute();

    // Create reset link pointing to reset_password.php with token as GET param
    $resetLink = "http://localhost/LTO-System/dashboard/reset_password.html?token=$token";

    // Send email using PHPMailer
    $mail = new PHPMailer(true);
    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'noreplymssb@gmail.com';  // Your Gmail address
        $mail->Password   = 'ysnbdwtsvnfifkfp';       // Your Gmail App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Email sender and recipient
        $mail->setFrom('noreplymssb@gmail.com', 'LTO Support');
        $mail->addAddress($email);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        $mail->Body    = "
            <p>Hello,</p>
            <p>You requested a password reset. Please click the link below to reset your password:</p>
            <p><a href='$resetLink'>$resetLink</a></p>
            <p>If you did not request this, please ignore this email.</p>
        ";

        $mail->send();
        echo "Password reset link has been sent to your email.";
    } catch (Exception $e) {
        echo "Failed to send email. Mailer Error: {$mail->ErrorInfo}";
    }

    $stmt->close();
    $conn->close();
}
?>
