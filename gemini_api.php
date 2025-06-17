<?phpAdd commentMore actions
// Désactiver l'affichage des erreurs pour éviter de corrompre le JSON
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);
require_once 'api_config.php';

// Headers pour l'API JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Gérer les requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Inclure la configuration de la base de données
require_once 'config.php';

// Fonction pour envoyer une réponse JSON et arrêter l'exécution
function sendJsonResponse($data, $httpCode = 200)
{
    http_response_code($httpCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Fonction pour logger les erreurs sans les afficher
function logError($message)
{
    error_log(date('[Y-m-d H:i:s] ') . $message . PHP_EOL, 3, 'gemini_errors.log');
}

// Fonction pour se connecter à la base de données
function getDatabaseConnection()
{
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
        return $pdo;
    } catch (PDOException $e) {
        logError("Database connection error: " . $e->getMessage());
        return null;
    }
}

// Fonction pour sauvegarder un message en base
function saveMessageToDatabase($pdo, $chatChannelId, $role, $content)
{
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

// Fonction pour récupérer l'historique des messages
function getMessageHistory($pdo, $chatChannelId, $limit = 10)
{
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

// Fonction pour convertir les messages au format Gemini
function convertMessagesToGeminiFormat($messages)
{
    $geminiMessages = [];
@@ -116,105 +31,10 @@
    return $geminiMessages;
}

// Extraire le chat_channel_id de l'URL ou des paramètres
$chatChannelId = null;

// Vérifier dans les paramètres GET
if (isset($_GET['id_channel'])) {
    $chatChannelId = $_GET['id_channel'];
}

// Vérifier dans les paramètres POST
if (!$chatChannelId && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    if ($rawInput !== false) {
        $input = json_decode($rawInput, true);
        if (isset($input['chat_channel_id'])) {
            $chatChannelId = $input['chat_channel_id'];
        }
    }
}

// Si pas trouvé, vérifier dans l'URL de référence
if (!$chatChannelId && isset($_SERVER['HTTP_REFERER'])) {
    if (preg_match('/id_channel=([^&]+)/', $_SERVER['HTTP_REFERER'], $matches)) {
        $chatChannelId = $matches[1];
    }
}

if (!$chatChannelId) {
    sendJsonResponse(['success' => false, 'error' => 'ID de canal de chat manquant'], 400);
}

// Clé API Gemini - Remplacez par votre vraie clé API
$apiKey = 'AIzaSyBs7CiasxHyT2IrrZiiBPsUMeKaBcFCA7A';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'error' => 'Méthode non autorisée'], 405);
}

// Lire et décoder les données d'entrée
$rawInput = file_get_contents('php://input');
if ($rawInput === false) {
    sendJsonResponse(['success' => false, 'error' => 'Impossible de lire les données']);
}

$input = json_decode($rawInput, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    sendJsonResponse(['success' => false, 'error' => 'Données JSON invalides']);
}

if (!$input || !isset($input['messages']) || !is_array($input['messages'])) {
    sendJsonResponse(['success' => false, 'error' => 'Messages manquants ou invalides']);
}

$messages = $input['messages'];

// Connexion à la base de données
$pdo = getDatabaseConnection();
if (!$pdo) {
    sendJsonResponse(['success' => false, 'error' => 'Erreur de connexion à la base de données']);
}

// Récupérer l'historique depuis la base de données
$dbHistory = getMessageHistory($pdo, $chatChannelId, 20);

// Validation et nettoyage des messages
$cleanMessages = [];
foreach ($messages as $message) {
    if (!isset($message['role']) || !isset($message['content'])) {
        continue; // Ignorer les messages mal formés
    }

    $role = trim($message['role']);
    $content = trim($message['content']);

    if (empty($role) || empty($content)) {
        continue;
    }

    if (!in_array($role, ['user', 'assistant', 'system'])) {
        $role = 'user'; // Par défaut
    }

    $cleanMessages[] = [
        'role' => $role,
        'content' => $content
    ];
}

if (empty($cleanMessages)) {
    sendJsonResponse(['success' => false, 'error' => 'Aucun message valide trouvé']);
}

// Sauvegarder le message utilisateur en base (le dernier message est normalement celui de l'utilisateur)
$lastMessage = end($cleanMessages);
if ($lastMessage['role'] === 'user') {
    $saveResult = saveMessageToDatabase($pdo, $chatChannelId, $lastMessage['role'], $lastMessage['content']);
    if (!$saveResult) {
        logError("Failed to save user message to database");
    }
}

// Convertir les messages au format Gemini
$geminiMessages = convertMessagesToGeminiFormat($cleanMessages);
@@ -252,12 +72,7 @@
// URL de l'API Gemini
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $apiKey;

// Initialiser cURL
$ch = curl_init();
if ($ch === false) {
    logError("Impossible d'initialiser cURL");
    sendJsonResponse(['success' => false, 'error' => 'Erreur système']);
}


// Configuration cURL
$success = curl_setopt_array($ch, [
@@ -276,18 +91,6 @@
    CURLOPT_MAXREDIRS => 3
]);

if (!$success) {
    curl_close($ch);
    logError("Erreur configuration cURL");
    sendJsonResponse(['success' => false, 'error' => 'Erreur de configuration']);
}

// Exécuter la requête
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

curl_close($ch);

// Gestion des erreurs cURL
if ($response === false || !empty($curlError)) {
@@ -321,52 +124,14 @@
    sendJsonResponse(['success' => false, 'error' => $errorMessage], $httpCode >= 500 ? 500 : 400);
}

// Décoder la réponse JSON
$result = json_decode($response, true);

if ($result === null || json_last_error() !== JSON_ERROR_NONE) {
    logError("JSON Decode Error: " . json_last_error_msg() . " - Response: " . substr($response, 0, 200));
    sendJsonResponse(['success' => false, 'error' => 'Réponse API invalide']);
}

// Vérifier la structure de la réponse Gemini
if (!isset($result['candidates']) || !is_array($result['candidates']) || empty($result['candidates'])) {
    logError("Unexpected API Response structure: " . json_encode($result));
    sendJsonResponse(['success' => false, 'error' => 'Format de réponse inattendu']);
}

if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
    logError("Missing content in API response: " . json_encode($result['candidates'][0] ?? 'No candidates[0]'));
    sendJsonResponse(['success' => false, 'error' => 'Contenu de réponse manquant']);
}

// Extraire et nettoyer le contenu
$content = $result['candidates'][0]['content']['parts'][0]['text'];
if (!is_string($content)) {
    logError("Content is not a string: " . gettype($content));
    sendJsonResponse(['success' => false, 'error' => 'Contenu de réponse invalide']);
}

$content = trim($content);
if (empty($content)) {
    sendJsonResponse(['success' => false, 'error' => 'Réponse vide de l\'API']);
}

// Sauvegarder la réponse de l'assistant en base
$saveAssistantResult = saveMessageToDatabase($pdo, $chatChannelId, 'assistant', $content);
if (!$saveAssistantResult) {
    logError("Failed to save assistant message to database");
}

// Formater la réponse (convertir les retours à la ligne en <br>)
$formattedContent = nl2br(htmlspecialchars($content, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

// Envoyer la réponse finale
sendJsonResponse([
    'success' => true,
    'content' => $formattedContent,
    'model' => 'gemini-pro',
    'usage' => $result['usageMetadata'] ?? null,
    'timestamp' => date('c'),
    'chat_channel_id' => $chatChannelId
]);