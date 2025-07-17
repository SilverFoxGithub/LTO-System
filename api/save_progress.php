<?php
require '../db/db_connect.php';
session_start();

// 🔍 Log session at start
file_put_contents(__DIR__ . "/debug_log.txt", "=== NEW REQUEST ===\n", FILE_APPEND);
file_put_contents(__DIR__ . "/debug_log.txt", print_r($_SESSION, true), FILE_APPEND);

if (!isset($_SESSION['user_id'])) {
    file_put_contents(__DIR__ . "/debug_log.txt", "User not logged in.\n", FILE_APPEND);
    die(json_encode(["error" => "User not logged in."]));
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lesson_id = intval($_POST['lesson_id']);
    $completion_percentage = intval($_POST['completion_percentage']);

    file_put_contents(__DIR__ . "/debug_log.txt", "Lesson ID: $lesson_id\n", FILE_APPEND);
    file_put_contents(__DIR__ . "/debug_log.txt", "Completion: $completion_percentage\n", FILE_APPEND);

    if ($lesson_id < 1 || $lesson_id > 11) {
        file_put_contents(__DIR__ . "/debug_log.txt", "Invalid lesson ID.\n", FILE_APPEND);
        die(json_encode(["error" => "Invalid lesson ID."]));
    }

    // ✅ Save progress
    $update_progress = $conn->prepare("
        INSERT INTO progress (user_id, lesson_id, completion_percentage) 
        VALUES (?, ?, ?) 
        ON DUPLICATE KEY UPDATE completion_percentage = VALUES(completion_percentage)
    ");
    $update_progress->bind_param("iii", $user_id, $lesson_id, $completion_percentage);

    if (!$update_progress->execute()) {
        file_put_contents(__DIR__ . "/debug_log.txt", "DB ERROR: " . $update_progress->error . "\n", FILE_APPEND);
        die(json_encode(["error" => "Error updating progress: " . $conn->error]));
    } else {
        file_put_contents(__DIR__ . "/debug_log.txt", "Progress saved!\n", FILE_APPEND);
    }
    $update_progress->close();

    // ✅ Unlock next lesson if applicable
    $next_lesson = $lesson_id + 1;
    if ($next_lesson <= 11) {
        $unlock_lesson = $conn->prepare("
            INSERT INTO progress (user_id, lesson_id, completion_percentage) 
            VALUES (?, ?, 0) 
            ON DUPLICATE KEY UPDATE lesson_id = VALUES(lesson_id)
        ");
        $unlock_lesson->bind_param("ii", $user_id, $next_lesson);
        if (!$unlock_lesson->execute()) {
            file_put_contents(__DIR__ . "/debug_log.txt", "Next lesson unlock failed: " . $unlock_lesson->error . "\n", FILE_APPEND);
        } else {
            file_put_contents(__DIR__ . "/debug_log.txt", "Next lesson $next_lesson unlocked.\n", FILE_APPEND);
        }
        $unlock_lesson->close();
    }

    echo json_encode(["success" => "Lesson completed and progress saved."]);
    $conn->close();
} else {
    file_put_contents(__DIR__ . "/debug_log.txt", "Invalid request method.\n", FILE_APPEND);
    die(json_encode(["error" => "Invalid request."]));
}
