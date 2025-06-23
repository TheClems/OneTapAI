<?php
// chatgpt_api.php - API spécifique pour ChatGPT

require_once 'api_config.php';

// Configuration spécifique à ChatGPT
const CHATGPT_API_KEY = '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';
const CHATGPT_MODEL = 'gpt-4o-mini';

/**
 * Convertir les messages au format ChatGPT
 */
function convertMessagesToChatGPTFormat($messages)
{
    $chatgptMessages = [];
    
    foreach ($messages as $message) {
        $role = $message['role'];
        $content = $message['content'];
        
        // ChatGPT utilise 'system', 'user', 'assistant'
        // Pas de conversion nécessaire, les rôles sont compatibles
        
        $chatgptMessages[] = [
            'role' => $role,
            'content' => $content
        ];
    }
    
    return $chatgptMessages;
}

/**
 * Processeur spécifique pour l'API ChatGPT
 */
function processChatGPTApi($cleanMessages, $chatChannelId)
{
    // Convertir les messages au format ChatGPT
    $chatgptMessages = convertMessagesToChatGPTFormat($cleanMessages);

    // Préparer les données pour l'API ChatGPT
    $data = [
        "model" => CHATGPT_MODEL,
        "messages" => $chatgptMessages,
        "temperature" => 0.7,
        "max_tokens" => 1000,
        "top_p" => 0.95,
        "frequency_penalty" => 0,
        "presence_penalty" => 0
    ];

    // URL et headers spécifiques à ChatGPT
    $apiUrl = "https://api.openai.com/v1/chat/completions";
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . CHATGPT_API_KEY
    ];

    // Initialiser et exécuter la requête cURL
    $ch = initializeCurl($apiUrl, $data, $headers);
    $curlResult = executeCurlRequest($ch, 'ChatGPT');
    
    $response = $curlResult['response'];
    $httpCode = $curlResult['httpCode'];

    // Vérification du code HTTP
    if ($httpCode !== 200) {
        $errorMessage = handleChatGPTHttpError($httpCode, $response);
        sendJsonResponse(['success' => false, 'error' => $errorMessage], $httpCode >= 500 ? 500 : 400);
    }

    // Décoder et valider la réponse JSON
    $result = json_decode($response, true);
    if ($result === null || json_last_error() !== JSON_ERROR_NONE) {
        logError("JSON Decode Error: " . json_last_error_msg() . " - Response: " . substr($response, 0, 200), 'chatgpt_errors.log');
        sendJsonResponse(['success' => false, 'error' => 'Réponse API invalide']);
    }

    // Vérifier la structure de la réponse ChatGPT
    if (!isset($result['choices']) || !is_array($result['choices']) || empty($result['choices'])) {
        logError("Unexpected API Response structure: " . json_encode($result), 'chatgpt_errors.log');
        sendJsonResponse(['success' => false, 'error' => 'Format de réponse inattendu']);
    }

    if (!isset($result['choices'][0]['message']['content'])) {
        logError("Missing content in API response: " . json_encode($result['choices'][0] ?? 'No choices[0]'), 'chatgpt_errors.log');
        sendJsonResponse(['success' => false, 'error' => 'Contenu de réponse manquant']);
    }

    // Extraire et valider le contenu
    $content = $result['choices'][0]['message']['content'];
    if (!is_string($content)) {
        logError("Content is not a string: " . gettype($content), 'chatgpt_errors.log');
        sendJsonResponse(['success' => false, 'error' => 'Contenu de réponse invalide']);
    }

    $content = trim($content);
    if (empty($content)) {
        sendJsonResponse(['success' => false, 'error' => 'Réponse vide de l\'API']);
    }

    return [
        'content' => $content,
        'model' => $result['model'] ?? CHATGPT_MODEL,
        'usage' => $result['usage'] ?? null
    ];
}

/**
 * Gérer les erreurs HTTP spécifiques à ChatGPT
 */
function handleChatGPTHttpError($httpCode, $response)
{
    logError("API Error: HTTP $httpCode - " . substr($response, 0, 500), 'chatgpt_errors.log');

    $errorMessage = 'Erreur API ChatGPT';
    
    // Essayer de décoder la réponse pour obtenir plus de détails
    $errorResponse = json_decode($response, true);
    
    if ($httpCode === 400) {
        $errorMessage = 'Requête invalide';
        if ($errorResponse && isset($errorResponse['error']['message'])) {
            $errorMessage = $errorResponse['error']['message'];
        }
    } elseif ($httpCode === 401) {
        $errorMessage = 'Clé API invalide ou expirée';
        if ($errorResponse && isset($errorResponse['error']['message'])) {
            $errorMessage = $errorResponse['error']['message'];
        }
    } elseif ($httpCode === 403) {
        $errorMessage = 'Accès refusé - Vérifiez vos permissions API';
    } elseif ($httpCode === 404) {
        $errorMessage = 'Modèle non trouvé ou non disponible';
    } elseif ($httpCode === 429) {
        $errorMessage = 'Limite de taux dépassée, réessayez dans quelques secondes';
        if ($errorResponse && isset($errorResponse['error']['message'])) {
            $errorMessage = $errorResponse['error']['message'];
        }
    } elseif ($httpCode === 500) {
        $errorMessage = 'Erreur serveur OpenAI, réessayez plus tard';
    } elseif ($httpCode === 502) {
        $errorMessage = 'Erreur de passerelle OpenAI';
    } elseif ($httpCode === 503) {
        $errorMessage = 'Service OpenAI temporairement indisponible';
    } elseif ($httpCode >= 400) {
        // Pour les autres erreurs, essayer d'extraire le message d'erreur
        if ($errorResponse && isset($errorResponse['error']['message'])) {
            $errorMessage = $errorResponse['error']['message'];
        }
    }

    return $errorMessage;
}

// Point d'entrée principal
processApiRequest('processChatGPTApi');
?>