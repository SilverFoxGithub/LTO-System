<?php
include 'load_env.php'; // Load the environment variables

$apiKey = getenv('DEEPSEEK_API_KEY'); // Get the API key

if ($apiKey) {
    echo "DEEPSEEK_API_KEY: " . $apiKey;
} else {
    echo "DEEPSEEK_API_KEY is not set.";
}
?>