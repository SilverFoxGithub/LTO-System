<?php
// Clear any previous output and start buffering
ob_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection
require_once '../db/db_connect.php';
session_start();

// 🔍 Log initial environment
file_put_contents(__DIR__ . "/debug_log.txt", "=== NEW REQUEST === " . date('Y-m-d H:i:s') . " ===\n", FILE_APPEND);
file_put_contents(__DIR__ . "/debug_log.txt", "PHP Version: " . phpversion() . "\n", FILE_APPEND);
file_put_contents(__DIR__ . "/debug_log.txt", "Session: " . print_r($_SESSION, true) . "\n", FILE_APPEND);
file_put_contents(__DIR__ . "/debug_log.txt", "GET: " . print_r($_GET, true) . "\n", FILE_APPEND);
file_put_contents(__DIR__ . "/debug_log.txt", "POST: " . print_r($_POST, true) . "\n", FILE_APPEND);

// Check if $conn is defined
if (!isset($conn) || !$conn instanceof mysqli) {
    file_put_contents(__DIR__ . "/debug_log.txt", "ERROR: \$conn is not defined or not a mysqli object.\n", FILE_APPEND);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    ob_end_flush();
    exit;
}

if (!isset($_SESSION['user_id'])) {
    file_put_contents(__DIR__ . "/debug_log.txt", "User not logged in.\n", FILE_APPEND);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    ob_end_flush();
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $test_score = isset($_POST['test_score']) ? (int)$_POST['test_score'] : null;

    file_put_contents(__DIR__ . "/debug_log.txt", "Test Score: $test_score\n", FILE_APPEND);

    if ($test_score === null) {
        file_put_contents(__DIR__ . "/debug_log.txt", "Invalid test score.\n", FILE_APPEND);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid test score']);
        ob_end_flush();
        exit;
    }

    // ✅ Save score
    $stmt = $conn->prepare("UPDATE users SET test_1_score = ? WHERE id = ?");
    if ($stmt === false) {
        file_put_contents(__DIR__ . "/debug_log.txt", "Prepare failed: " . $conn->error . "\n", FILE_APPEND);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to prepare statement: ' . $conn->error]);
        ob_end_flush();
        exit;
    }

    $stmt->bind_param("ii", $test_score, $user_id);
    if (!$stmt->execute()) {
        file_put_contents(__DIR__ . "/debug_log.txt", "Execute failed: " . $conn->error . "\n", FILE_APPEND);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to save score: ' . $conn->error]);
    } else {
        file_put_contents(__DIR__ . "/debug_log.txt", "Score saved successfully!\n", FILE_APPEND);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Score saved successfully']);
    }
    $stmt->close();
} else {
    file_put_contents(__DIR__ . "/debug_log.txt", "Invalid request method.\n", FILE_APPEND);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

// Flush output buffer and stop
ob_end_flush();
?>