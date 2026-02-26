<?php
/**
 * DentConsent AI Chat Proxy - GROQ VERSION
 * Uses LLaMA-3.3-70B via Groq (fast + free tier).
 * Keeps conversation history server-side via PHP sessions.
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

session_start();

// ── Config ────────────────────────────────────────────────────────────────────
define('GROQ_API_KEY', ''); // ← YOUR GROQ KEY
define('GROQ_MODEL',   'llama-3.3-70b-versatile');
define('MAX_TOKENS',   200);

define('MAX_HISTORY_MESSAGES', 30);

// ── System prompt ─────────────────────────────────────────────────────────────
$SYSTEM_PROMPT = "You are DentConsent AI, a friendly dental care assistant embedded in a patient consent app. "
    . "Your job: help patients understand dental procedures, post-op care, medication effects, and oral hygiene. "
    . "RULES: "
    . "1. Answer ONLY dental/health-related questions. "
    . "2. Keep every answer to 2-3 short sentences maximum. "
    . "3. Always recommend consulting the treating dentist for diagnosis or emergencies. "
    . "4. Refuse unrelated questions with: 'I can only help with dental and oral health questions.' "
    . "5. Never give definitive diagnoses — only guidance and reassurance.";

// ── Read request ──────────────────────────────────────────────────────────────
$body    = file_get_contents('php://input');
$data    = json_decode($body, true);
$message = trim($data['message'] ?? '');
$role    = trim($data['role']    ?? 'patient');

if (empty($message)) {
    echo json_encode(['reply' => 'Please send a message.']);
    exit;
}

if ($role === 'doctor') {
    $SYSTEM_PROMPT .= " The user is a dentist — you may use clinical terminology.";
}

// ── Session-based history ─────────────────────────────────────────────────────
if (empty($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [
        ['role' => 'system', 'content' => $SYSTEM_PROMPT]
    ];
}
$history = &$_SESSION['chat_history'];

// Add new user message
$history[] = ['role' => 'user', 'content' => $message];

// Trim to prevent context overflow
if (count($history) > MAX_HISTORY_MESSAGES) {
    array_splice($history, 1, count($history) - MAX_HISTORY_MESSAGES - 1);
}

// Rough token safety check
$approxTokens = 0;
foreach ($history as $msg) {
    $approxTokens += strlen($msg['content']) / 4;
}
if ($approxTokens > 100000) {
    array_splice($history, 1, 10);
}

// ── Groq API request (OpenAI-compatible format) ───────────────────────────────
$payload = json_encode([
    'model'       => GROQ_MODEL,
    'messages'    => $history,
    'max_tokens'  => MAX_TOKENS,
    'temperature' => 0.4,
    'stream'      => false
]);

$ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . GROQ_API_KEY
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 20,
]);

$response = curl_exec($ch);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($curlErr || !$response) {
    http_response_code(500);
    echo json_encode(['reply' => 'Connection error. Please try again.']);
    exit;
}

// ── Parse response ────────────────────────────────────────────────────────────
$result = json_decode($response, true);

if (isset($result['error'])) {
    echo json_encode(['reply' => 'API error: ' . $result['error']['message']]);
    exit;
}

$reply = trim($result['choices'][0]['message']['content'] ?? 'Sorry, I could not generate a reply.');

// Save assistant reply to history
$history[] = ['role' => 'assistant', 'content' => $reply];

echo json_encode(['reply' => $reply, 'success' => true]);
