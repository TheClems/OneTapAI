<?php
// gemini_api.php - API spécifique pour Gemini

require_once 'api_config.php';

// Configuration spécifique à Gemini
const GEMINI_API_KEY = 'AIzaSyBs7CiasxHyT2IrrZiiBPsUMeKaBcFCA7A';
const GEMINI_MODEL = 'gemini-2.0-flash';

/**
 * Convertir les messages au format Gemini
 */
function convertMessagesToGeminiFormat($messages)
{
    $geminiMessages = [];
    
    foreach ($messages as $message) {
        $role = $message['role'];
        $content = $message['content'];
        
        // Gemini utilise 'user' et 'model' au lieu de 'user' et 'assistant'
        if ($role === 'assistant') {
            $role = 'model';
        }
        
        // Ignorer les messages système pour Gemini
        if ($role === 'system') {
            continue;
        }
        
        $geminiMessages[] = [
            'role' => $role,
            'parts' => [
                ['text' => $content]
            ]
        ];
    }
    
    return $geminiMessages;
}

/**
 * Processeur spécifique pour l'API Gemini
 */
function processGeminiApi($cleanMessages, $chatChannelId)
{
    // Convertir les messages au format Gemini
    $geminiMessages = convertMessagesToGeminiFormat($cleanMessages);

    // Préparer les données pour l'API Gemini
    $data = [
        "contents" => $geminiMessages,
        "generationConfig" => [
            "temperature" => 0.7,
            "topK" => 40,
            "topP" => 0.95,
            "maxOutputTokens" => 1000,
            "stopSequences" => []
        ],
        "safetySettings" => [
            [
                "category" => "HARM_CATEGORY_HARASSMENT",
                "threshold" => "BLOCK_MEDIUM_AND_ABOVE"
            ],
            [
                "category" => "HARM_CATEGORY_HATE_SPEECH",
                "threshold" => "BLOCK_MEDIUM_AND_ABOVE"
            ],
            [
                "category" => "HARM_CATEGORY_SEXUALLY_EXPLICIT",
                "threshold" => "BLOCK_MEDIUM_AND_ABOVE"
            ],
            [
                "category" => "HARM_CATEGORY_DANGEROUS_CONTENT",
                "threshold" => "BLOCK_MEDIUM_AND_ABOVE"
            ]
        ]
    ];

    // URL et headers spécifiques à Gemini
    $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/" . GEMINI_MODEL . ":generateContent?key=" . GEMINI_API_KEY;
    $headers = ['Content-Type: application/json'];

    // Initialiser et exécuter la requête cURL
    $ch = initializeCurl($apiUrl, $data, $headers);
    $curlResult = executeCurlRequest($ch, 'Gemini');
    
    $response = $curlResult['response'];
    $httpCode = $curlResult['httpCode'];

    // Vérification du code HTTP
    if ($httpCode !== 200) {
        $errorMessage = handleGeminiHttpError($httpCode, $response);
        sendJsonResponse(['success' => false, 'error' => $errorMessage], $httpCode >= 500 ? 500 : 400);
    }

    // Décoder et valider la réponse JSON
    $result = json_decode($response, true);
    if ($result === null || json_last_error() !== JSON_ERROR_NONE) {
        logError("JSON Decode Error: " . json_last_error_msg() . " - Response: " . substr($response, 0, 200), 'gemini_errors.log');
        sendJsonResponse(['success' => false, 'error' => 'Réponse API invalide']);
    }

    // Vérifier la structure de la réponse Gemini
    if (!isset($result['candidates']) || !is_array($result['candidates']) || empty($result['candidates'])) {
        logError("Unexpected API Response structure: " . json_encode($result), 'gemini_errors.log');
        sendJsonResponse(['success' => false, 'error' => 'Format de réponse inattendu']);
    }

    if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        logError("Missing content in API response: " . json_encode($result['candidates'][0] ?? 'No candidates[0]'), 'gemini_errors.log');
        sendJsonResponse(['success' => false, 'error' => 'Contenu de réponse manquant']);
    }

    // Extraire et valider le contenu
    $content = $result['candidates'][0]['content']['parts'][0]['text'];
    if (!is_string($content)) {
        logError("Content is not a string: " . gettype($content), 'gemini_errors.log');
        sendJsonResponse(['success' => false, 'error' => 'Contenu de réponse invalide']);
    }

    $content = trim($content);
    if (empty($content)) {
        sendJsonResponse(['success' => false, 'error' => 'Réponse vide de l\'API']);
    }

    return [
        'content' => $content,
        'model' => 'gemini-pro',
        'usage' => $result['usageMetadata'] ?? null
    ];
}

/**
 * Gérer les erreurs HTTP spécifiques à Gemini
 */
function handleGeminiHttpError($httpCode, $response)
{
    logError("API Error: HTTP $httpCode - " . substr($response, 0, 500), 'gemini_errors.log');

    $errorMessage = 'Erreur API Gemini';
    if ($httpCode === 400) {
        $errorMessage = 'Requête invalide';
    } elseif ($httpCode === 401) {
        $errorMessage = 'Clé API invalide ou expirée';
    } elseif ($httpCode === 403) {
        $errorMessage = 'Accès refusé';
    } elseif ($httpCode === 429) {
        $errorMessage = 'Trop de requêtes, réessayez dans quelques secondes';
    } elseif ($httpCode === 500) {
        $errorMessage = 'Erreur serveur Gemini, réessayez plus tard';
    } elseif ($httpCode >= 400) {
        // Essayer de décoder la réponse pour obtenir plus de détails
        $errorResponse = json_decode($response, true);
        if ($errorResponse && isset($errorResponse['error']['message'])) {
            $errorMessage = $errorResponse['error']['message'];
        }
    }

    return $errorMessage;
}

// Point d'entrée principal
processApiRequest('processGeminiApi');
?>