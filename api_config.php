<?php
// common_functions.php - Fonctions communes pour toutes les APIs d'IA

// Désactiver l'affichage des erreurs pour éviter de corrompre le JSON
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Inclure la configuration de la base de données
require_once 'config.php';

/**
 * Configure les headers pour l'API JSON
 */
function setupApiHeaders()
{
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
}

/**
 * Gérer les requêtes OPTIONS (preflight)
 */
function handleOptionsRequest()
{
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

/**
 * Envoie une réponse JSON et arrête l'exécution
 */
function sendJsonResponse($data, $httpCode = 200)
{
    http_response_code($httpCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Logger les erreurs sans les afficher
 */
function logError($message, $logFile = 'api_errors.log')
{
    error_log(date('[Y-m-d H:i:s] ') . $message . PHP_EOL, 3, $logFile);
}

/**
 * Se connecter à la base de données
 */
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

/**
 * Sauvegarder un message en base de données
 */
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

/**
 * Récupérer l'historique des messages
 */
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

/**
 * Extraire le chat_channel_id depuis différentes sources
 */
function extractChatChannelId()
{
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

    return $chatChannelId;
}

/**
 * Valider et nettoyer les messages d'entrée
 */
function validateAndCleanMessages($messages)
{
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

    return $cleanMessages;
}

/**
 * Lire et valider les données d'entrée JSON
 */
function readAndValidateInput()
{
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

    return $input;
}

/**
 * Initialiser et configurer cURL
 */
function initializeCurl($url, $data, $headers, $timeout = 30, $connectTimeout = 10)
{
    $ch = curl_init();
    if ($ch === false) {
        logError("Impossible d'initialiser cURL");
        sendJsonResponse(['success' => false, 'error' => 'Erreur système']);
    }

    $success = curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_UNICODE),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_CONNECTTIMEOUT => $connectTimeout,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT => 'AI-Chat-App/1.0',
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3
    ]);

    if (!$success) {
        curl_close($ch);
        logError("Erreur configuration cURL");
        sendJsonResponse(['success' => false, 'error' => 'Erreur de configuration']);
    }

    return $ch;
}

/**
 * Exécuter la requête cURL et gérer les erreurs
 */
function executeCurlRequest($ch, $apiName)
{
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);

    curl_close($ch);

    // Gestion des erreurs cURL
    if ($response === false || !empty($curlError)) {
        logError("CURL Error: " . $curlError);
        sendJsonResponse(['success' => false, 'error' => "Erreur de connexion à l'API $apiName"]);
    }

    return ['response' => $response, 'httpCode' => $httpCode];
}

/**
 * Formater le contenu de réponse (convertir les retours à la ligne en <br>)
 */
function formatResponseContent($content)
{
    return nl2br(htmlspecialchars($content, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
}

/**
 * Processus principal commun à toutes les APIs
 */
function processApiRequest($apiProcessor)
{
    // Configuration des headers
    setupApiHeaders();
    handleOptionsRequest();

    // Extraction du chat_channel_id
    $chatChannelId = extractChatChannelId();
    if (!$chatChannelId) {
        sendJsonResponse(['success' => false, 'error' => 'ID de canal de chat manquant'], 400);
    }

    // Lecture et validation des données d'entrée
    $input = readAndValidateInput();
    $messages = $input['messages'];

    // Connexion à la base de données
    $pdo = getDatabaseConnection();
    if (!$pdo) {
        sendJsonResponse(['success' => false, 'error' => 'Erreur de connexion à la base de données']);
    }

    // Récupération de l'historique (si nécessaire)
    $dbHistory = getMessageHistory($pdo, $chatChannelId, 20);

    // Validation et nettoyage des messages
    $cleanMessages = validateAndCleanMessages($messages);
    if (empty($cleanMessages)) {
        sendJsonResponse(['success' => false, 'error' => 'Aucun message valide trouvé']);
    }

    // Sauvegarder le message utilisateur en base
    $lastMessage = end($cleanMessages);
    if ($lastMessage['role'] === 'user') {
        $saveResult = saveMessageToDatabase($pdo, $chatChannelId, $lastMessage['role'], $lastMessage['content']);
        if (!$saveResult) {
            logError("Failed to save user message to database");
        }
    }

    // Appeler le processeur spécifique à l'API
    $result = $apiProcessor($cleanMessages, $chatChannelId);

    // Sauvegarder la réponse de l'assistant en base
    $saveAssistantResult = saveMessageToDatabase($pdo, $chatChannelId, 'assistant', $result['content']);
    if (!$saveAssistantResult) {
        logError("Failed to save assistant message to database");
    }

    // Formater et envoyer la réponse finale
    $formattedContent = formatResponseContent($result['content']);
    
    sendJsonResponse([
        'success' => true,
        'content' => $formattedContent,
        'model' => $result['model'],
        'usage' => $result['usage'] ?? null,
        'timestamp' => date('c'),
        'chat_channel_id' => $chatChannelId
    ]);
}
?>