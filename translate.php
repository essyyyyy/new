<?php
header('Content-Type: application/json');

// Get the text and target language from the request
$text = isset($_GET['text']) ? $_GET['text'] : '';
$lang = isset($_GET['lang']) ? $_GET['lang'] : '';

if (empty($text) || empty($lang)) {
    echo json_encode(['success' => false, 'error' => 'Missing text or language parameter']);
    exit;
}

// Map language codes to LibreTranslate format
$langMap = [
    'en' => 'en',
    'es' => 'es',
    'de' => 'de',
    'fr' => 'fr',
    'sv' => 'sv'
];

$targetLang = isset($langMap[$lang]) ? $langMap[$lang] : 'en';

// Direct translation using LibreTranslate API
$apiUrl = 'https://libretranslate.de/translate';

$data = [
    'q' => $text,
    'source' => 'en',
    'target' => $targetLang,
    'format' => 'text'
];

$options = [
    'http' => [
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data)
    ]
];

$context = stream_context_create($options);

try {
    $result = file_get_contents($apiUrl, false, $context);
    $response = json_decode($result, true);
    
    if (isset($response['translatedText'])) {
        echo json_encode(['success' => true, 'translation' => $response['translatedText']]);
    } else {
        // Fallback to MyMemory API if LibreTranslate fails
        $fallbackUrl = "https://api.mymemory.translated.net/get?q=" . urlencode($text) . "&langpair=en|" . $targetLang;
        $fallbackResult = file_get_contents($fallbackUrl);
        $fallbackResponse = json_decode($fallbackResult, true);
        
        if (isset($fallbackResponse['responseData']['translatedText'])) {
            echo json_encode(['success' => true, 'translation' => $fallbackResponse['responseData']['translatedText']]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Translation failed']);
        }
    }
} catch (Exception $e) {
    // Fallback to MyMemory API if LibreTranslate fails
    $fallbackUrl = "https://api.mymemory.translated.net/get?q=" . urlencode($text) . "&langpair=en|" . $targetLang;
    $fallbackResult = file_get_contents($fallbackUrl);
    $fallbackResponse = json_decode($fallbackResult, true);
    
    if (isset($fallbackResponse['responseData']['translatedText'])) {
        echo json_encode(['success' => true, 'translation' => $fallbackResponse['responseData']['translatedText']]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Translation failed: ' . $e->getMessage()]);
    }
}
?> 
