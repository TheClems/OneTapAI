<?php
// openrouter_api.php - API spécifique à OpenRouter

require_once 'api_config.php';

// Clé API OpenRouter
const OPENROUTER_API_KEY = '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'; // <-- Remplace par ta clé
const OPENROUTER_MODEL = 'moonshotai/kimi-dev-72b:free';

/**
 * Convertir les messages au format OpenRouter
 */
function convertMessagesToOpenRouterFormat($messages)
{
    $converted = [];
    foreach ($messages as $message) {
        if ($message['role'] === 'system') continue; // Ignorer les messages système
        $converted[] = [
            'role' => $message['role'],
            'content' => $message['content']
        ];
    }
    return $converted;
}

/**
 * Processeur pour OpenRouter
 */
function processOpenRouterApi($cleanMessages, $chatChannelId)
{
    $formattedMessages = convertMessagesToOpenRouterFormat($cleanMessages);

    $data = [
        "model" => OPENROUTER_MODEL,
        "messages" => $formattedMessages
    ];

    $apiUrl = "https://openrouter.ai/api/v1/chat/completions";
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . OPENROUTER_API_KEY
    ];

    $ch = initializeCurl($apiUrl, $data, $headers);
    $curlResult = executeCurlRequest($ch, 'OpenRouter');

    $response = $curlResult['response'];
    $httpCode = $curlResult['httpCode'];

    if ($httpCode !== 200) {
        sendJsonResponse([
            'success' => false,
            'error' => "Erreur API OpenRouter (HTTP $httpCode)"
        ], $httpCode >= 500 ? 500 : 400);
    }

    $result = json_decode($response, true);
    if (!$result || !isset($result['choices'][0]['message']['content'])) {
        logError("Invalid API response: $response", 'openrouter_errors.log');
        sendJsonResponse(['success' => false, 'error' => 'Réponse invalide de l’IA']);
    }

    $content = trim($result['choices'][0]['message']['content']);

    return [
        'content' => $content,
        'model' => OPENROUTER_MODEL,
        'usage' => $result['usage'] ?? null
    ];
}
logError("Réponse brute OpenRouter : " . $response, 'openrouter_debug.log');

// Point d'entrée
processApiRequest('processOpenRouterApi');
?>
