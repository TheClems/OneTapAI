<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config.php';

// Vérifier que l'utilisateur a un channel_id
if (!isset($_SESSION['id_channel'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Channel ID manquant']);
    exit;
}

$channel_id = $_SESSION['id_channel'];

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

$messages = $input['messages'];

// Validation des messages
foreach ($messages as $message) {
    if (!isset($message['role']) || !isset($message['content'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Format de message invalide']);
        exit;
    }
}

// Fonction pour sauvegarder un message en base de données
function saveMessage($pdo, $channel_id, $role, $content) {
    try {
        $stmt = $pdo->prepare("INSERT INTO chat_messages (chat_channel_id, role, content, created_at) VALUES (?, ?, ?, NOW())");
        return $stmt->execute([$channel_id, $role, $content]);
    } catch (PDOException $e) {
        error_log("Erreur sauvegarde message: " . $e->getMessage());
        return false;
    }
}

// Sauvegarder le dernier message utilisateur (le plus récent dans le tableau)
$lastUserMessage = null;
for ($i = count($messages) - 1; $i >= 0; $i--) {
    if ($messages[$i]['role'] === 'user') {
        $lastUserMessage = $messages[$i];
        break;
    }
}

if ($lastUserMessage) {
    if (!saveMessage($pdo, $channel_id, 'user', $lastUserMessage['content'])) {
        error_log("Erreur lors de la sauvegarde du message utilisateur");
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

// Récupérer la réponse de l'IA
$aiContent = trim($result['choices'][0]['message']['content']);

// Sauvegarder la réponse de l'IA en base de données (contenu brut, sans formatage HTML)
if (!saveMessage($pdo, $channel_id, 'assistant', $aiContent)) {
    error_log("Erreur lors de la sauvegarde de la réponse IA");
}

// Nettoyer et formater la réponse pour l'affichage
$formattedContent = nl2br(htmlspecialchars($aiContent, ENT_QUOTES, 'UTF-8'));

echo json_encode([
    'success' => true,
    'content' => $formattedContent,
    'model' => $result['model'] ?? 'mistral-medium',
    'usage' => $result['usage'] ?? null
]);
?>