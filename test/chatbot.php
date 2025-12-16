<?php
// Simple proxy to Gemini assistant for histories page
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

$GEMINI_API_KEY = getenv('GEMINI_API_KEY');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['reply' => 'Invalid request method.']);
    exit;
}

$userMessage = isset($_POST['message']) ? trim(strip_tags($_POST['message'])) : '';
$userMessage = mb_substr($userMessage, 0, 1000);

if ($userMessage === '') {
    echo json_encode(['reply' => 'Please type a question about how to use the forum.']);
    exit;
}

$lowerMsg = mb_strtolower($userMessage);
if (preg_match('/\b(thank|thanks|thank you|merci|appreciate)\b/i', $userMessage)) {
    echo json_encode(['reply' => "You're welcome! If you have any other questions about the forum, feel free to ask! 😊"]);
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
    echo json_encode(['reply' => "I'm only here to help you use this forum: logging in, posts, comments, edits, deletes, and reactions."]);
    exit;
}

$systemInstructions = <<<EOT
You are the official assistant for PeaceLink, a web discussion forum for sharing stories and getting support.
Answer only questions about how to use the site (login, stories, comments, reactions, initiatives). Keep answers short.
If the question is unrelated, reply exactly: "I'm only here to help you use this forum: logging in, posts, comments, edits, deletes, and reactions."
EOT;

$fullPrompt = $systemInstructions . "\n\nUser question:\n" . $userMessage;

if (!$GEMINI_API_KEY || $GEMINI_API_KEY === 'your_gemini_api_key_here') {
    echo json_encode(['reply' => "Hello! I'm your PeaceLink assistant. Configure GEMINI_API_KEY in .env for live answers. How can I help you use the forum?"]);
    exit;
}

$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . urlencode($GEMINI_API_KEY);
$payload = [
    'contents' => [[ 'parts' => [['text' => $fullPrompt]] ]],
    'generationConfig' => ['temperature' => 0.3, 'maxOutputTokens' => 512]
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
$response = curl_exec($ch);

if ($response === false) {
    curl_close($ch);
    echo json_encode(['reply' => 'Sorry, I cannot reach the assistant service right now.']);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
if ($httpCode !== 200) {
    $errorResponse = json_decode($response, true);
    $errorMsg = $errorResponse['error']['message'] ?? 'Unknown API error';
    error_log("Gemini API Error (HTTP $httpCode): " . $errorMsg);
    echo json_encode(['reply' => 'Sorry, there was a problem with the assistant: ' . $errorMsg]);
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
echo json_encode(['reply' => $replyText]);
exit;
