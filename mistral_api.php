<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
if (!isset($_GET['post'])) {
    // Le paramètre `post` est manquant dans l'URL
    // Par exemple, on redirige ou on affiche un message
    header("Location: ?post=default_value");
    exit;
}
// Clé API Mistral
$apiKey = 'OX4fzStQrzPd2PfyCAl7PR6ip3bcsvey';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['messages'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Messages manquants']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO chat_channels (name) VALUES (?)");
$stmt->execute(['Conversation du ' . date('Y-m-d H:i:s')]);
$chatChannelId = $pdo->lastInsertId();


$messages = $input['messages'];

// Insertion des messages
$stmt = $pdo->prepare("INSERT INTO chat_messages (chat_channel_id, role, content) VALUES (?, ?, ?)");

foreach ($messages as $message) {
    $stmt->execute([$chatChannelId, $message['role'], $message['content']]);
}


// Validation des messages
foreach ($messages as $message) {
    if (!isset($message['role']) || !isset($message['content'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Format de message invalide']);
        exit;
    }
}

$ch = curl_init();

$data = [
    "model" => "mistral-medium",
    "messages" => $messages,
    "temperature" => 0.7,
    "max_tokens" => 1000,
    "top_p" => 0.95,
    "stream" => false
];

curl_setopt_array($ch, [
    CURLOPT_URL => 'https://api.mistral.ai/v1/chat/completions',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ],
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_USERAGENT => 'Mistral-Chat-App/1.0'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

curl_close($ch);

if ($curlError) {
    error_log("CURL Error: " . $curlError);
    echo json_encode([
        'success' => false, 
        'error' => 'Erreur de connexion à l\'API Mistral'
    ]);
    exit;
}

if ($httpCode !== 200) {
    error_log("API Error: HTTP $httpCode - " . $response);
    
    $errorResponse = json_decode($response, true);
    $errorMessage = 'Erreur API Mistral';
    
    if ($errorResponse && isset($errorResponse['message'])) {
        $errorMessage = $errorResponse['message'];
    } elseif ($httpCode === 401) {
        $errorMessage = 'Clé API invalide';
    } elseif ($httpCode === 429) {
        $errorMessage = 'Trop de requêtes, réessayez plus tard';
    } elseif ($httpCode === 500) {
        $errorMessage = 'Erreur serveur Mistral';
    }
    
    echo json_encode([
        'success' => false, 
        'error' => $errorMessage,
        'http_code' => $httpCode
    ]);
    exit;
}

$result = json_decode($response, true);

if (!$result) {
    error_log("JSON Decode Error: " . json_last_error_msg());
    echo json_encode([
        'success' => false, 
        'error' => 'Réponse API invalide'
    ]);
    exit;
}

if (!isset($result['choices'][0]['message']['content'])) {
    error_log("Unexpected API Response: " . $response);
    echo json_encode([
        'success' => false, 
        'error' => 'Format de réponse inattendu'
    ]);
    exit;
}

// Nettoyer et formater la réponse
$content = trim($result['choices'][0]['message']['content']);
$content = nl2br(htmlspecialchars($content, ENT_QUOTES, 'UTF-8'));

echo json_encode([
    'success' => true,
    'content' => $content,
    'model' => $result['model'] ?? 'mistral-medium',
    'usage' => $result['usage'] ?? null
]);
?>