<?php
// gemini_image_api.php - API spécifique pour la génération d'images avec Gemini

require_once 'common_functions.php';

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
 * Générer l'image avec l'API Stability AI (comme dans votre exemple)
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
        CURLOPT_TIMEOUT => 60, // Plus de temps pour la génération d'image
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

    // Pour la génération d'images, on peut accepter soit un prompt direct, soit des messages
    if (isset($input['prompt']) && !empty($input['prompt'])) {
        // Convertir le prompt direct en format messages
        $input['messages'] = [
            ['role' => 'user', 'content' => $input['prompt']]
        ];
    }

    if (!$input || !isset($input['messages']) || !is_array($input['messages'])) {
        sendJsonResponse(['success' => false, 'error' => 'Messages ou prompt manquants']);
    }

    return $input;
}

/**
 * Processus principal pour la génération d'images
 */
function processImageApiRequest()
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
    $input = readAndValidateImageInput();
    $messages = $input['messages'];

    // Connexion à la base de données
    $pdo = getDatabaseConnection();
    if (!$pdo) {
        sendJsonResponse(['success' => false, 'error' => 'Erreur de connexion à la base de données']);
    }

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

    // Générer l'image
    $result = processImageGenerationApi($cleanMessages, $chatChannelId);

    // Sauvegarder le chemin de l'image comme réponse de l'assistant
    $saveAssistantResult = saveMessageToDatabase($pdo, $chatChannelId, 'assistant', $result['content']);
    if (!$saveAssistantResult) {
        logError("Failed to save assistant message (image path) to database");
    }

    // Envoyer la réponse finale
    sendJsonResponse([
        'success' => true,
        'content' => $result['content'], // Chemin relatif de l'image
        'model' => $result['model'],
        'usage' => $result['usage'],
        'image_info' => $result['image_info'],
        'timestamp' => date('c'),
        'chat_channel_id' => $chatChannelId,
        'type' => 'image' // Indiquer que c'est une réponse image
    ]);
}

// Point d'entrée principal
processImageApiRequest();
?>