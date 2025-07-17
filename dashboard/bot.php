<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userMessage = trim($_POST['message']);

    // ✅ Your Gemini API Key
    $apiKey = "AIzaSyDxMNQoc8_2bWRXvs9dI8SDSrnbAVqEzjY"; // Replace this if needed

    // ✅ Gemini Flash Model Endpoint
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=$apiKey";

    // ✅ Gemini Request Format
    $postData = [
        "contents" => [
            [
                "role" => "user",
                "parts" => [
                    ["text" => "You are an expert in driving topics like car maintenance, traffic rules, safe driving, and vehicles. Only answer if it's about driving. If it's unrelated, politely decline."],
                    ["text" => $userMessage]
                ]
            ]
        ]
    ];

    $payload = json_encode($postData);

    // ✅ cURL Request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo "⚠️ Request Error: " . curl_error($ch);
        curl_close($ch);
        exit;
    }

    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        echo nl2br(html_entity_decode($result['candidates'][0]['content']['parts'][0]['text']));
    } else {
        if (isset($result['error']['message'])) {
            echo "⚠️ API Error: " . html_entity_decode($result['error']['message']);
        } else {
            echo "🚧 Sorry, I couldn't get a proper answer. Try again later!";
        }
    }
}
?>
