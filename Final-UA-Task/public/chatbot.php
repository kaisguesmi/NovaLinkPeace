<?php
// chatbot.php - Google Gemini backend for Forum Assistant


ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

// === INIT API KEY ===
// Load environment variables from .env file
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
if (!$GEMINI_API_KEY) {
    // Debug: Check if .env file exists and what's in it
    $envFile = __DIR__ . '/../.env';
    error_log("Debug: .env file exists: " . (file_exists($envFile) ? 'YES' : 'NO'));
    if (file_exists($envFile)) {
        error_log("Debug: .env file contents: " . file_get_contents($envFile));
    }
    error_log("Debug: GEMINI_API_KEY from getenv: " . ($GEMINI_API_KEY ? 'SET' : 'NOT SET'));
    
    http_response_code(500);
    echo json_encode(['reply' => 'AI service not configured.']);
    exit;
}
// ================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['reply' => 'Invalid request method.']);
    exit;
}

if (!isset($_POST['message'])) {
    http_response_code(400);
    echo json_encode(['reply' => 'No message provided.']);
    exit;
}

$userMessage = $_POST['message'];
$userMessage = trim($userMessage);
$userMessage = strip_tags($userMessage);
$userMessage = mb_substr($userMessage, 0, 1000);

if ($userMessage === '') {
    echo json_encode(['reply' => 'Please type a question about how to use the forum.']);
    exit;
}

// Check for greetings in English and French
$greetings = [
    'hello', 'hi', 'hey', 'greetings', 'good morning', 'good afternoon', 'good evening',
    'bonjour', 'salut', 'coucou', 'bonsoir', 'bon matin', 'bon après-midi'
];

$isGreeting = false;
$lowerMsg = mb_strtolower($userMessage);

foreach ($greetings as $greeting) {
    if (strpos($lowerMsg, $greeting) === 0) {
        $isGreeting = true;
        break;
    }
}

if ($isGreeting) {
    echo json_encode([
        'reply' => 'Hello! 👋 I\'m your PeaceLink assistant. I\'m here to help you with any questions about using our forum. How can I assist you today?'
    ]);
    exit;
}

// Basic topic filter: only allow forum-usage questions before calling Gemini
$lowerMsg = mb_strtolower($userMessage);
$allowedKeywords = [
    'login', 'log in', 'connexion', 'sign in',
    'logout', 'log out', 'déconnexion', 'sign out',
    'post', 'story', 'stories', 'histoire', 'add story', 'share experience',
    'comment', 'commenter', 'commentaire', 'discuss', 'discussion',
    'edit', 'modifier', 'update',
    'delete', 'supprimer', 'remove',
    'reaction', 'reactions', 'réaction', 'réactions', 'like', 'emoji', 'support',
    'account', 'profil', 'profile', 'my account',
    'purpose', 'what is this', 'about', 'help', 'what can i do', 'how to use', 
    'peacelink', 'peace link', 'what is peacelink', 'what is peace link',
    'initiative', 'initiatives', 'create initiative', 'start initiative',
    'professional', 'professionals', 'expert', 'experts', 'ask expert',
    'community', 'communities', 'support group', 'help others', 'get help',
    'association', 'associations', 'ngo', 'nonprofit', 'non-profit',
    'volunteer', 'volunteering', 'get involved', 'contribute',
    'peace', 'unity', 'harmony', 'understanding', 'supportive community'
];


// Check for thank you messages first
if (preg_match('/\b(thank|thanks|thank you|merci|appreciate)\b/i', $userMessage)) {
    echo json_encode([
        'reply' => "You're welcome! If you have any other questions about the forum, feel free to ask! 😊"
    ]);
    exit;
}



// Check for forum-related questions
$isForumQuestion = false;
foreach ($allowedKeywords as $kw) {
    if (mb_strpos($lowerMsg, $kw) !== false) {
        $isForumQuestion = true;
        break;
    }
}

if (!$isForumQuestion) {
    echo json_encode([
        'reply' => "I'm only here to help you use this forum: logging in, posts, comments, edits, deletes, and reactions."
    ]);
    exit;
}

$systemInstructions = <<<EOT
You are the official assistant for PeaceLink, a web discussion forum dedicated to promoting peace and community support.

ABOUT PEACELINK:
PeaceLink is a unique online community and forum platform that serves as a bridge between people seeking support and professionals who can help. Our platform enables:

FOR INDIVIDUALS:
- Share personal stories and experiences in a supportive environment
- Seek advice and opinions from a diverse community
- Connect with professionals and experts in various fields
- Find emotional support and understanding from others
- Participate in meaningful discussions about important topics

FOR PROFESSIONALS & ASSOCIATIONS:
- Offer guidance and support to those in need
- Create and promote initiatives and programs
- Connect with individuals who can benefit from your expertise
- Build a network of like-minded professionals
- Raise awareness about important causes

KEY FEATURES:
- Safe and moderated discussions
- Professional verification system
- Initiative creation and management tools
- Multilingual support
- Community guidelines that promote respect and understanding

Your ONLY job is to help users with:
- What is PeaceLink? (A global peace forum for sharing stories and supporting each other)
- What is the purpose of this website? (It's a peace forum where people can share stories, ask for help, and support each other in a positive community)
- How to log in and log out
- How to create a new post or story
- How to comment on posts
- How to edit their own posts
- How to delete their own posts
- How reactions (likes/emojis) work on posts and comments

VERY IMPORTANT RULES:
- Answer ONLY questions directly related to using this forum website.
- If the user asks about anything else (programming, math, history, general knowledge, other websites, personal advice, etc.), do NOT answer it.
- For any unrelated question, reply with this exact sentence (in English):

"I'm only here to help you use this forum: logging in, posts, comments, edits, deletes, and reactions."

- Keep answers short, clear, and friendly.
- When needed, describe the steps (menus, buttons) the user should click.
EOT;

$fullPrompt = $systemInstructions . "\n\nUser question:\n" . $userMessage;

// Test mode - remove this block when you have a real API key
if ($GEMINI_API_KEY === 'your_gemini_api_key_here') {
    echo json_encode([
        'reply' => "Hello! I'm your PeaceLink assistant. I'm here to help you with any questions about using our forum. How can I assist you today? (Note: This is a test response. Please add a real Gemini API key to your .env file to enable full functionality.)"
    ]);
    exit;
}

// Use hardcoded Gemini model
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . urlencode($GEMINI_API_KEY);

$payload = [
    'contents' => [
        [
            'parts' => [
                ['text' => $fullPrompt]
            ]
        ]
    ],
    'generationConfig' => [
        'temperature' => 0.3,
        'maxOutputTokens' => 512,
    ]
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

if (
    isset($data['candidates'][0]['content']['parts']) &&
    is_array($data['candidates'][0]['content']['parts'])
) {
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
