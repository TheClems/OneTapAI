<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'config.php';

function sendJsonResponse($data, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function logError($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . PHP_EOL, 3, 'gemini_errors.log');
}

function getDatabaseConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        return new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
    } catch (PDOException $e) {
        logError("Database connection error: " . $e->getMessage());
        return null;
    }
}

function saveMessageToDatabase($pdo, $chatChannelId, $role, $content) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO chat_messages (chat_channel_id, role, content, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        return $stmt->execute([$chatChannelId, $role, $content]);
    } catch (PDOException $e) {
        logError("Database insert error: " . $e->getMessage());
        return false;
    }
}

function getMessageHistory($pdo, $chatChannelId, $limit = 10) {
    try {
        $stmt = $pdo->prepare("
            SELECT role, content 
            FROM chat_messages 
            WHERE chat_channel_id = ? 
            ORDER BY created_at ASC 
            LIMIT ?
        ");
        $stmt->execute([$chatChannelId, $limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        logError("Database select error: " . $e->getMessage());
        return [];
    }
}

$chatChannelId = $_GET['id_channel'] ?? null;

if (!$chatChannelId && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    if (isset($input['chat_channel_id'])) {
        $chatChannelId = $input['chat_channel_id'];
    }
}

if (!$chatChannelId && isset($_SERVER['HTTP_REFERER'])) {
    if (preg_match('/id_channel=([^&]+)/', $_SERVER['HTTP_REFERER'], $matches)) {
        $chatChannelId = $matches[1];
    }
}

if (!$chatChannelId) {
    sendJsonResponse(['success' => false, 'error' => 'ID de canal de chat manquant'], 400);
}

$apiKey = 'AIzaSyCCi1o7eOxryROJIG26YS3vlR1tKC1dFcc'; // ← Mets ta clé ici

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'error' => 'Méthode non autorisée'], 405);
}

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

if (!$input || !isset($input['messages']) || !is_array($input['messages'])) {
    sendJsonResponse(['success' => false, 'error' => 'Messages manquants ou invalides']);
}

$messages = $input['messages'];
$pdo = getDatabaseConnection();
if (!$pdo) {
    sendJsonResponse(['success' => false, 'error' => 'Erreur de connexion à la base de données']);
}

$dbHistory = getMessageHistory($pdo, $chatChannelId, 20);

$cleanMessages = [];

// Intégrer l'historique de la BDD
foreach ($dbHistory as $message) {
    $role = trim($message['role']);
    $content = trim($message['content']);
    if (!empty($role) && !empty($content)) {
        $cleanMessages[] = [
            'role' => $role,
            'parts' => [$content]
        ];
    }
}

// Ajouter les messages envoyés dans cette requête (nouvelle interaction)
foreach ($messages as $message) {
    if (!isset($message['role']) || !isset($message['content'])) continue;
    $role = trim($message['role']);
    $content = trim($message['content']);
    if (!empty($role) && !empty($content)) {
        $cleanMessages[] = [
            'role' => $role,
            'parts' => [$content]
        ];
    }
}


if (empty($cleanMessages)) {
    sendJsonResponse(['success' => false, 'error' => 'Aucun message valide trouvé']);
}

$lastMessage = end($cleanMessages);
if ($lastMessage['role'] === 'user') {
    saveMessageToDatabase($pdo, $chatChannelId, 'user', $lastMessage['parts'][0]);
}

$data = [
    "contents" => $cleanMessages,
    "generationConfig" => [
        "temperature" => 0.7,
        "topP" => 0.95,
        "maxOutputTokens" => 1000
    ]
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=" . urlencode($apiKey),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => true
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($response === false || !empty($curlError)) {
    logError("CURL Error: " . $curlError);
    sendJsonResponse(['success' => false, 'error' => 'Erreur de connexion à l\'API Gemini']);
}

if ($httpCode !== 200) {
    logError("API Error HTTP $httpCode - " . substr($response, 0, 500));
    sendJsonResponse(['success' => false, 'error' => 'Erreur API Gemini'], $httpCode);
}

$result = json_decode($response, true);
if (!isset($result['candidates'][0]['content']['parts'][0])) {
    logError("Invalid Gemini API response: " . substr($response, 0, 300));
    sendJsonResponse(['success' => false, 'error' => 'Réponse API invalide']);
}

$content = trim($result['candidates'][0]['content']['parts'][0]);
if (empty($content)) {
    sendJsonResponse(['success' => false, 'error' => 'Réponse vide de l\'API']);
}

saveMessageToDatabase($pdo, $chatChannelId, 'assistant', $content);
$formattedContent = nl2br(htmlspecialchars($content, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

sendJsonResponse([
    'success' => true,
    'content' => $formattedContent,
    'model' => 'gemini-pro',
    'timestamp' => date('c'),
    'chat_channel_id' => $chatChannelId
]);
?>
