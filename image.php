<?php

require_once 'api_config.php';

// Configuration spécifique à Gemini Image Generation
const GEMINI_API_KEY = 'AIzaSyBs7CiasxHyT2IrrZiiBPsUMeKaBcFCA7A';
const GEMINI_IMAGE_MODEL = 'gemini-2.0-flash';

// Dossier pour stocker les images générées
const IMAGE_STORAGE_DIR = __DIR__ . '/image_api/';

/**
 * Créer le dossier image_api s'il n'existe pas
 */
function ensureImageDirectoryExists()
{
    if (!is_dir(IMAGE_STORAGE_DIR)) {
        if (!mkdir(IMAGE_STORAGE_DIR, 0755, true)) {
            logError("Impossible de créer le dossier image_api");
            sendJsonResponse(['success' => false, 'error' => 'Erreur de configuration du stockage']);
        }
    }
    
    if (!is_writable(IMAGE_STORAGE_DIR)) {
        logError("Le dossier image_api n'est pas accessible en écriture");
        sendJsonResponse(['success' => false, 'error' => 'Erreur de permissions du stockage']);
    }
}

/**
 * Générer un nom de fichier aléatoire unique
 */
function generateRandomFilename($extension = 'jpeg')
{
    $timestamp = time();
    $randomString = bin2hex(random_bytes(8));
    return "img_{$timestamp}_{$randomString}.{$extension}";
}

/**
 * Extraire le prompt d'image du dernier message utilisateur
 */
function extractImagePrompt($messages)
{
    // Récupérer le dernier message utilisateur
    $lastUserMessage = null;
    for ($i = count($messages) - 1; $i >= 0; $i--) {
        if ($messages[$i]['role'] === 'user') {
            $lastUserMessage = $messages[$i]['content'];
            break;
        }
    }
    
    if (!$lastUserMessage) {
        sendJsonResponse(['success' => false, 'error' => 'Aucun prompt trouvé']);
    }
    
    return trim($lastUserMessage);
}

/**
 * Générer l'image avec l'API Stability AI
 */
function generateImageWithStabilityAI($prompt)
{
    // Configuration Stability AI
    $apiKey = 'sk-sTX9dwsqDfL8F9k0dpvpTzPQjJ5rFkW1tOPEPwcsGvVv4wHj';
    $url = 'https://api.stability.ai/v2beta/stable-image/generate/sd3';
    $outputFormat = 'jpeg';
    
    // Générer un nom de fichier unique
    $filename = generateRandomFilename($outputFormat);
    $outputFile = IMAGE_STORAGE_DIR . $filename;
    
    // Initialisation de cURL
    $ch = curl_init($url);
    
    if ($ch === false) {
        logError("Impossible d'initialiser cURL pour Stability AI");
        sendJsonResponse(['success' => false, 'error' => 'Erreur système']);
    }
    
    // Configuration des options cURL
    $success = curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => [
            'prompt' => $prompt,
            'output_format' => $outputFormat,
        ],
        CURLOPT_HTTPHEADER => [
            "authorization: Bearer $apiKey",
            "accept: image/*",
        ],
        CURLOPT_TIMEOUT => 60,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT => 'AI-Chat-App/1.0',
    ]);
    
    if (!$success) {
        curl_close($ch);
        logError("Erreur configuration cURL pour Stability AI");
        sendJsonResponse(['success' => false, 'error' => 'Erreur de configuration']);
    }
    
    // Exécution de la requête
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    
    curl_close($ch);
    
    // Gestion des erreurs cURL
    if ($response === false || !empty($curlError)) {
        logError("CURL Error Stability AI: " . $curlError);
        sendJsonResponse(['success' => false, 'error' => 'Erreur de connexion à l\'API de génération d\'images']);
    }
    
    // Vérification du code HTTP
    if ($httpCode !== 200) {
        logError("Stability AI API Error: HTTP $httpCode - " . substr($response, 0, 500));
        
        $errorMessage = 'Erreur API de génération d\'images';
        if ($httpCode === 400) {
            $errorMessage = 'Prompt invalide pour la génération d\'image';
        } elseif ($httpCode === 401) {
            $errorMessage = 'Clé API invalide';
        } elseif ($httpCode === 429) {
            $errorMessage = 'Trop de requêtes, réessayez dans quelques secondes';
        } elseif ($httpCode === 500) {
            $errorMessage = 'Erreur serveur, réessayez plus tard';
        }
        
        sendJsonResponse(['success' => false, 'error' => $errorMessage], $httpCode >= 500 ? 500 : 400);
    }
    
    // Enregistrer l'image
    $saveResult = file_put_contents($outputFile, $response);
    if ($saveResult === false) {
        logError("Impossible de sauvegarder l'image: $outputFile");
        sendJsonResponse(['success' => false, 'error' => 'Erreur de sauvegarde de l\'image']);
    }
    
    logError("Image générée avec succès: $filename");
    
    return [
        'filename' => $filename,
        'filepath' => $outputFile,
        'relative_path' => 'image_api/' . $filename,
        'size' => filesize($outputFile)
    ];
}

/**
 * Processeur spécifique pour la génération d'images
 */
function processImageGenerationApi($cleanMessages, $chatChannelId)
{
    // S'assurer que le dossier image_api existe
    ensureImageDirectoryExists();
    
    // Extraire le prompt du dernier message utilisateur
    $prompt = extractImagePrompt($cleanMessages);
    
    if (empty($prompt)) {
        sendJsonResponse(['success' => false, 'error' => 'Prompt vide pour la génération d\'image']);
    }
    
    // Générer l'image
    $imageResult = generateImageWithStabilityAI($prompt);
    
    // Le "contenu" de la réponse sera le chemin relatif de l'image
    $responseContent = $imageResult['relative_path'];
    
    return [
        'content' => $responseContent,
        'model' => 'stability-ai-sd3',
        'usage' => [
            'prompt' => $prompt,
            'image_filename' => $imageResult['filename'],
            'image_size' => $imageResult['size']
        ],
        'image_info' => $imageResult
    ];
}

/**
 * Fonction pour lire et valider les données d'entrée spécifique aux images
 */
function readAndValidateImageInput()
{
    // DEBUG: Log de la méthode de requête
    logError("DEBUG: Request method: " . $_SERVER['REQUEST_METHOD']);
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJsonResponse(['success' => false, 'error' => 'Méthode non autorisée'], 405);
    }

    // Lire et décoder les données d'entrée
    $rawInput = file_get_contents('php://input');
    
    // DEBUG: Log des données brutes reçues
    logError("DEBUG: Raw input received: " . substr($rawInput, 0, 500));
    
    if ($rawInput === false) {
        sendJsonResponse(['success' => false, 'error' => 'Impossible de lire les données']);
    }

    $input = json_decode($rawInput, true);
    $jsonError = json_last_error();
    
    // DEBUG: Log de l'erreur JSON si présente
    if ($jsonError !== JSON_ERROR_NONE) {
        logError("DEBUG: JSON decode error: " . json_last_error_msg());
        sendJsonResponse(['success' => false, 'error' => 'Données JSON invalides: ' . json_last_error_msg()]);
    }
    
    // DEBUG: Log de l'input décodé
    logError("DEBUG: Decoded input: " . json_encode($input));

    // Pour la génération d'images, on peut accepter soit un prompt direct, soit des messages
    if (isset($input['prompt']) && !empty($input['prompt'])) {
        // Convertir le prompt direct en format messages
        $input['messages'] = [
            ['role' => 'user', 'content' => $input['prompt']]
        ];
        logError("DEBUG: Converted prompt to messages format");
    }

    if (!$input || !isset($input['messages']) || !is_array($input['messages'])) {
        logError("DEBUG: Messages validation failed - input: " . json_encode($input));
        sendJsonResponse(['success' => false, 'error' => 'Messages ou prompt manquants']);
    }
    
    // DEBUG: Log du nombre de messages
    logError("DEBUG: Number of messages: " . count($input['messages']));

    return $input;
}

/**
 * Processus principal pour la génération d'images
 */
function processImageApiRequest()
{
    try {
        // DEBUG: Log du début du processus
        logError("DEBUG: Starting image API request processing");
        
        // Configuration des headers
        setupApiHeaders();
        handleOptionsRequest();

        // Lecture et validation des données d'entrée
        $input = readAndValidateImageInput();
        $messages = $input['messages'];
        
        // DEBUG: Log des messages
        logError("DEBUG: Messages to process: " . json_encode($messages));

        // Extraction du chat_channel_id
        $chatChannelId = extractChatChannelId();
        
        // DEBUG: Log du chat channel ID
        logError("DEBUG: Chat channel ID: " . ($chatChannelId ? $chatChannelId : 'NULL'));
        
        if (!$chatChannelId) {
            sendJsonResponse(['success' => false, 'error' => 'ID de canal de chat manquant'], 400);
        }

        // Connexion à la base de données
        $pdo = getDatabaseConnection();
        if (!$pdo) {
            logError("DEBUG: Database connection failed");
            sendJsonResponse(['success' => false, 'error' => 'Erreur de connexion à la base de données']);
        }
        
        logError("DEBUG: Database connection successful");

        // Validation et nettoyage des messages
        $cleanMessages = validateAndCleanMessages($messages);
        
        // DEBUG: Log des messages nettoyés
        logError("DEBUG: Clean messages count: " . count($cleanMessages));
        
        if (empty($cleanMessages)) {
            sendJsonResponse(['success' => false, 'error' => 'Aucun message valide trouvé']);
        }

        // Sauvegarder le message utilisateur en base
        $lastMessage = end($cleanMessages);
        if ($lastMessage['role'] === 'user') {
            $saveResult = saveMessageToDatabase($pdo, $chatChannelId, $lastMessage['role'], $lastMessage['content']);
            if (!$saveResult) {
                logError("Failed to save user message to database");
            } else {
                logError("DEBUG: User message saved to database");
            }
        }

        // Générer l'image
        logError("DEBUG: Starting image generation");
        $result = processImageGenerationApi($cleanMessages, $chatChannelId);
        logError("DEBUG: Image generation completed");

        // Sauvegarder le chemin de l'image comme réponse de l'assistant
        $saveAssistantResult = saveMessageToDatabase($pdo, $chatChannelId, 'assistant', $result['content']);
        if (!$saveAssistantResult) {
            logError("Failed to save assistant message (image path) to database");
        } else {
            logError("DEBUG: Assistant message saved to database");
        }

        // Envoyer la réponse finale
        logError("DEBUG: Sending final response");
        sendJsonResponse([
            'success' => true,
            'content' => $result['content'],
            'model' => $result['model'],
            'usage' => $result['usage'],
            'image_info' => $result['image_info'],
            'timestamp' => date('c'),
            'chat_channel_id' => $chatChannelId,
            'type' => 'image'
        ]);
        
    } catch (Exception $e) {
        logError("DEBUG: Exception caught: " . $e->getMessage());
        logError("DEBUG: Exception trace: " . $e->getTraceAsString());
        sendJsonResponse(['success' => false, 'error' => 'Erreur interne: ' . $e->getMessage()], 500);
    }
}

// Point d'entrée principal
processImageApiRequest();
?>