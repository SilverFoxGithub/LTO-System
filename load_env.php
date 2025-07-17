<?php
function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        echo "ENV file not found!";
        return;
    }
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '=') !== false && strpos(trim($line), '#') !== 0) {
            putenv($line);
        }
    }
}

loadEnv(__DIR__ . '/.env');
?>