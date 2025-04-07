<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Get the text and target language from the request
$text = $_GET['text'] ?? '';
$targetLang = $_GET['lang'] ?? 'en';

// Use LibreTranslate API directly
$apiUrl = "https://libretranslate.de/translate";

$postData = [
    'q' => $text,
    'source' => 'en',
    'target' => $targetLang,
    'format' => 'text'
];

$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($postData)
    ]
];

$context  = stream_context_create($options);
$response = file_get_contents($apiUrl, false, $context);
$data = json_decode($response, true);

if (isset($data['translatedText'])) {
    echo json_encode([
        'success' => true,
        'translation' => $data['translatedText']
    ]);
} else {
    // Fallback to MyMemory API if LibreTranslate fails
    $fallbackUrl = "https://api.mymemory.translated.net/get?q=" . urlencode($text) . "&langpair=en|" . $targetLang;
    $fallbackResponse = file_get_contents($fallbackUrl);
    $fallbackData = json_decode($fallbackResponse, true);
    
    if (isset($fallbackData['responseData']['translatedText'])) {
        echo json_encode([
            'success' => true,
            'translation' => $fallbackData['responseData']['translatedText']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Translation failed',
            'text' => $text
        ]);
    }
} 
