<?php
// Simple proxy to Gemini assistant for histories page
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $parsed = parse_ini_file($envFile, false, INI_SCANNER_RAW);
    if ($parsed !== false) {
        foreach ($parsed as $k => $v) {
            $v = trim(trim($v), "'\"");
            putenv($k . '=' . $v);
            $_ENV[$k] = $v;
        }
    }
}

$GEMINI_API_KEY = getenv('GEMINI_API_KEY') ?: ($_ENV['GEMINI_API_KEY'] ?? '');
$GEMINI_MODEL   = getenv('GEMINI_MODEL') ?: ($_ENV['GEMINI_MODEL'] ?? 'gemini-pro');
$GEMINI_TIMEOUT = (int)(getenv('GEMINI_TIMEOUT') ?: ($_ENV['GEMINI_TIMEOUT'] ?? 10));
$GEMINI_INSECURE = (getenv('GEMINI_INSECURE') ?: ($_ENV['GEMINI_INSECURE'] ?? '0')) === '1';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['reply' => 'Invalid request method.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$userMessage = isset($_POST['message']) ? trim(strip_tags($_POST['message'])) : '';
$userMessage = mb_substr($userMessage, 0, 1000);

if ($userMessage === '') {
    echo json_encode(['reply' => 'Please type a question about how to use the forum.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$lowerMsg = mb_strtolower($userMessage);
if (preg_match('/\b(thank|thanks|thank you|merci|appreciate)\b/i', $userMessage)) {
    echo json_encode(['reply' => "You're welcome! If you have any other questions about the forum, feel free to ask! 😊"], JSON_UNESCAPED_UNICODE);
    exit;
}

$allowedKeywords = [
    'login','log in','connexion','sign in','logout','log out','déconnexion','sign out',
    'post','story','stories','histoire','add story','share experience','comment','commenter','commentaire',
    'discuss','discussion','edit','modifier','update','delete','supprimer','remove',
    'reaction','reactions','réaction','réactions','like','emoji','support','account','profil','profile',
    'my account','purpose','what is this','about','help','how to use','peacelink','peace link',
    'initiative','initiatives','create initiative','start initiative','expert','experts','community','communities',
    'support group','association','ngo','volunteer','get involved'
];

$isForumQuestion = false;
foreach ($allowedKeywords as $kw) {
    if (mb_strpos($lowerMsg, $kw) !== false) {
        $isForumQuestion = true;
        break;
    }
}

if (!$isForumQuestion) {
    echo json_encode(['reply' => "I'm only here to help you use this forum: logging in, posts, comments, edits, deletes, and reactions."], JSON_UNESCAPED_UNICODE);
    exit;
}

$systemInstructions = <<<EOT
You are the official assistant for PeaceLink, a web discussion forum for sharing stories and getting support.
Answer only questions about how to use the site (login, stories, comments, reactions, initiatives). Keep answers short.
If the question is unrelated, reply exactly: "I'm only here to help you use this forum: logging in, posts, comments, edits, deletes, and reactions."
EOT;

$fullPrompt = $systemInstructions . "\n\nUser question:\n" . $userMessage;

if (!$GEMINI_API_KEY || $GEMINI_API_KEY === 'your_gemini_api_key_here') {
    echo json_encode(['reply' => "Hello! I'm your PeaceLink assistant. Configure GEMINI_API_KEY in .env for live answers. How can I help you use the forum?"], JSON_UNESCAPED_UNICODE);
    exit;
}

$apiUrlBase = "https://generativelanguage.googleapis.com/v1/models/";
$apiUrl = $apiUrlBase . rawurlencode($GEMINI_MODEL) . ":generateContent?key=" . urlencode($GEMINI_API_KEY);
$payload = [
    'contents' => [[ 'parts' => [['text' => $fullPrompt]] ]],
    'generationConfig' => ['temperature' => 0.3, 'maxOutputTokens' => 512]
];

$attempt = 0;
$maxAttempts = 2; // if first attempt 404 model, retry with gemini-pro
$lastResponse = null;
$lastHttpCode = null;
$lastErrorMsg = '';

do {
    $attempt++;
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json','Accept: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_TIMEOUT, $GEMINI_TIMEOUT > 0 ? $GEMINI_TIMEOUT : 10);
    if ($GEMINI_INSECURE) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    }

    $response = curl_exec($ch);
    $curlErrNo = curl_errno($ch);

    if ($response === false) {
        $error = curl_error($ch);
        error_log("Gemini API cURL Error (#{$curlErrNo}): " . $error);
        curl_close($ch);
        echo json_encode(['reply' => 'Sorry, something went wrong contacting the assistant: (' . $curlErrNo . ') ' . $error], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $lastResponse = $response;
    $lastHttpCode = $httpCode;

    if ($httpCode === 200) {
        break;
    }

    // Retry once with gemini-pro if model not found
    if ($httpCode === 404 && $attempt === 1 && $GEMINI_MODEL !== 'gemini-pro') {
        $fallbackModel = 'gemini-pro';
        $lastErrorMsg = 'Model ' . $GEMINI_MODEL . ' not found; retrying with ' . $fallbackModel;
        error_log($lastErrorMsg);
        $GEMINI_MODEL = $fallbackModel;
        $apiUrl = $apiUrlBase . rawurlencode($fallbackModel) . ":generateContent?key=" . urlencode($GEMINI_API_KEY);
        continue;
    }

    break;
} while ($attempt < $maxAttempts);

if ($lastHttpCode !== 200) {
    $errorResponse = json_decode($lastResponse, true);
    $errorMsg = $errorResponse['error']['message'] ?? $lastErrorMsg ?: 'Unknown API error';
    error_log("Gemini API Error (HTTP $lastHttpCode): " . $errorMsg . " - Response: " . $lastResponse);
    $snippet = mb_substr(is_string($lastResponse) ? $lastResponse : '', 0, 2000);
    echo json_encode(['reply' => 'Sorry, there was a problem with the assistant (HTTP ' . $lastHttpCode . '): ' . $errorMsg . ' | raw: ' . $snippet], JSON_UNESCAPED_UNICODE);
    exit;
}

$data = json_decode($response, true);
$replyText = 'Sorry, I could not generate a response.';

if (isset($data['candidates'][0]['content']['parts']) && is_array($data['candidates'][0]['content']['parts'])) {
    $parts = $data['candidates'][0]['content']['parts'];
    $texts = [];
    foreach ($parts as $part) {
        if (isset($part['text']) && is_string($part['text'])) {
            $texts[] = $part['text'];
        }
    }
    if (!empty($texts)) {
        $replyText = implode("\n", $texts);
    }
}

$replyText = trim(strip_tags($replyText));
echo json_encode(['reply' => $replyText], JSON_UNESCAPED_UNICODE);
exit;
