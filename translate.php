<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Load translations from JSON file
$translations = json_decode(file_get_contents('translations.json'), true);

// Get the text and target language from the request
$text = $_GET['text'] ?? '';
$targetLang = $_GET['lang'] ?? 'en';

// If we have a stored translation, use it
if (isset($translations[$targetLang][$text])) {
    echo json_encode([
        'success' => true,
        'translation' => $translations[$targetLang][$text],
        'source' => 'stored'
    ]);
    exit;
}

// If no stored translation, use MyMemory API
$apiUrl = "https://api.mymemory.translated.net/get?q=" . urlencode($text) . "&langpair=en|" . $targetLang;
$response = file_get_contents($apiUrl);
$data = json_decode($response, true);

if (isset($data['responseData']['translatedText'])) {
    // Store the new translation
    $translations[$targetLang][$text] = $data['responseData']['translatedText'];
    file_put_contents('translations.json', json_encode($translations, JSON_PRETTY_PRINT));

    echo json_encode([
        'success' => true,
        'translation' => $data['responseData']['translatedText'],
        'source' => 'api'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Translation failed',
        'text' => $text
    ]);
} 