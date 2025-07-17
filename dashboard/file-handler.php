<?php
require_once '../db/db_connect.php';
header('Content-Type: application/json');

// Handle certificate upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['action'] === 'upload') {
    $response = ['success' => false, 'error' => ''];

    if (!isset($_FILES['certificate']) || !isset($_POST['student_id'])) {
        $response['error'] = 'Missing file or student ID.';
        echo json_encode($response);
        exit;
    }

    $studentId = (int)$_POST['student_id'];
    $file = $_FILES['certificate'];

    $allowedTypes = ['application/pdf'];
    if (!in_array($file['type'], $allowedTypes)) {
        $response['error'] = 'Only PDF files are allowed.';
        echo json_encode($response);
        exit;
    }

    $uploadDir = '../uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = uniqid("cert_") . ".pdf";
    $filePath = $uploadDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        $stmt = $conn->prepare("UPDATE users SET pdf_file = ?, status = 'pass' WHERE id = ?");
        $stmt->bind_param("si", $fileName, $studentId);
        if ($stmt->execute()) {
            $response['success'] = true;
        } else {
            $response['error'] = 'Database update failed.';
        }
    } else {
        $response['error'] = 'File upload failed.';
    }

    echo json_encode($response);
    exit;
}

// Handle student deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['action'] === 'delete') {
    $response = ['success' => false, 'error' => ''];

    if (!isset($_POST['student_id'])) {
        $response['error'] = 'Missing student ID.';
        echo json_encode($response);
        exit;
    }

    $studentId = (int)$_POST['student_id'];

    // Check for uploaded file
    $check = $conn->prepare("SELECT pdf_file FROM users WHERE id = ?");
    $check->bind_param("i", $studentId);
    $check->execute();
    $check->bind_result($pdfFile);
    $check->fetch();
    $check->close();

    if ($pdfFile) {
        $filePath = "../uploads/" . $pdfFile;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // First delete progress records
$deleteProgress = $conn->prepare("DELETE FROM progress WHERE user_id = ?");
$deleteProgress->bind_param("i", $studentId);
$deleteProgress->execute();
    // Delete student record
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $studentId);
    if ($stmt->execute()) {
        $response['success'] = true;
    } else {
        $response['error'] = 'Failed to delete user.';
    }

    echo json_encode($response);
    exit;
}

// ✅ Handle pass/fail status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['action'] === 'update_status') {
    $response = ['success' => false, 'error' => ''];

    if (!isset($_POST['student_id']) || !isset($_POST['status'])) {
        $response['error'] = 'Missing student ID or status.';
        echo json_encode($response);
        exit;
    }

    $studentId = (int)$_POST['student_id'];
    $status = $_POST['status'];

    if (!in_array($status, ['pass', 'fail'])) {
        $response['error'] = 'Invalid status value.';
        echo json_encode($response);
        exit;
    }

    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $studentId);

    if ($stmt->execute()) {
        $response['success'] = true;
    } else {
        $response['error'] = 'Failed to update status.';
    }

    echo json_encode($response);
    exit;
}
