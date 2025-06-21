<?php
// mistral_api.php - API spécifique pour Mistral

require_once 'api_config.php';

// Configuration spécifique à Mistral
const MISTRAL_API_KEY = 'URJIXdnJ1d8qvXgwntjZ6KCRDVv4qRqp';
const MISTRAL_API_URL = 'https://api.mistral.ai/v1/chat/completions';
const MISTRAL_MODEL = 'mistral-large-2411';
$userId = $_SESSION['user_id'];
/**
 * Processeur spécifique pour l'API Mistral
 */
function processMistralApi($cleanMessages, $chatChannelId)
{
    global $userId;
    // Préparer les données pour l'API Mistral
    $data = [
        "model" => MISTRAL_MODEL,
        "messages" => $cleanMessages,
        "temperature" => 0.7,
        "max_tokens" => 1000,
        "top_p" => 0.95,
        "stream" => false
    ];

    // Headers spécifiques à Mistral
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . MISTRAL_API_KEY
    ];

    // Initialiser et exécuter la requête cURL
    $ch = initializeCurl(MISTRAL_API_URL, $data, $headers);
    $curlResult = executeCurlRequest($ch, 'Mistral');
    
    $response = $curlResult['response'];
    $httpCode = $curlResult['httpCode'];

    // Vérification du code HTTP
    if ($httpCode !== 200) {
        $errorMessage = handleMistralHttpError($httpCode, $response);
        sendJsonResponse(['success' => false, 'error' => $errorMessage], $httpCode >= 500 ? 500 : 400);
    }

    // Décoder et valider la réponse JSON
    $result = json_decode($response, true);
    if ($result === null || json_last_error() !== JSON_ERROR_NONE) {
        logError("JSON Decode Error: " . json_last_error_msg() . " - Response: " . substr($response, 0, 200), 'mistral_errors.log');
        sendJsonResponse(['success' => false, 'error' => 'Réponse API invalide']);
    }

    // Vérifier la structure de la réponse Mistral
    if (!isset($result['choices']) || !is_array($result['choices']) || empty($result['choices'])) {
        logError("Unexpected API Response structure: " . json_encode($result), 'mistral_errors.log');
        sendJsonResponse(['success' => false, 'error' => 'Format de réponse inattendu']);
    }

    if (!isset($result['choices'][0]['message']['content'])) {
        logError("Missing content in API response: " . json_encode($result['choices'][0] ?? 'No choices[0]'), 'mistral_errors.log');
        sendJsonResponse(['success' => false, 'error' => 'Contenu de réponse manquant']);
    }

    // Extraire et valider le contenu
    $content = $result['choices'][0]['message']['content'];
    if (!is_string($content)) {
        logError("Content is not a string: " . gettype($content), 'mistral_errors.log');
        sendJsonResponse(['success' => false, 'error' => 'Contenu de réponse invalide']);
    }

    $content = trim($content);
    if (empty($content)) {
        sendJsonResponse(['success' => false, 'error' => 'Réponse vide de l\'API']);
    }
    $usage = $result['usage'] ?? null;
    if ($usage && isset($usage['prompt_tokens'], $usage['completion_tokens'])) {
        $promptTokens = $usage['prompt_tokens'];
        $completionTokens = $usage['completion_tokens'];
        $totalTokens = $promptTokens + $completionTokens;   

        $pdo = getDBConnection();
        try {
            // Récupérer le coût des tokens d'entrée
            $stmt = $pdo->prepare("SELECT tokens_par_credit FROM ia_models WHERE modele_ia = 'mistral-large-latest' AND io_type = 'input'");
            $stmt->execute();
            $inputResult = $stmt->fetch(PDO::FETCH_ASSOC);
            $tokenParCreditsInput = $inputResult['tokens_par_credit'];
            $creditsUsedInput = (1 / $tokenParCreditsInput) * $promptTokens;

            // Récupérer le coût des tokens de sortie
            $stmt = $pdo->prepare("SELECT tokens_par_credit FROM ia_models WHERE modele_ia = 'mistral-large-latest' AND io_type = 'output'");
            $stmt->execute();
            $outputResult = $stmt->fetch(PDO::FETCH_ASSOC);
            $tokenParCreditsOutput = $outputResult['tokens_par_credit'];
            $creditsUsedOutput = (1 / $tokenParCreditsOutput) * $completionTokens;

            $creditsUsed = $creditsUsedInput + $creditsUsedOutput;

            // Déduire les crédits de l'utilisateur
            $stmt = $pdo->prepare("UPDATE users SET credits = credits - ? WHERE id = ?");
            $stmt->execute([$creditsUsed, $userId]);

        } catch (PDOException $e) {
            error_log("Erreur récupération modèle: " . $e->getMessage());
        }
    }
    return [
        'content' => $content,
        'model' => $result['model'] ?? MISTRAL_MODEL,
        'usage' => $result['usage'] ?? null
    ];

}

/**
 * Gérer les erreurs HTTP spécifiques à Mistral
 */
function handleMistralHttpError($httpCode, $response)
{
    logError("API Error: HTTP $httpCode - " . substr($response, 0, 500), 'mistral_errors.log');

    $errorMessage = 'Erreur API Mistral';
    if ($httpCode === 401) {
        $errorMessage = 'Clé API invalide ou expirée';
    } elseif ($httpCode === 429) {
        $errorMessage = 'Trop de requêtes, réessayez dans quelques secondes';
    } elseif ($httpCode === 500) {
        $errorMessage = 'Erreur serveur Mistral, réessayez plus tard';
    } elseif ($httpCode >= 400) {
        // Essayer de décoder la réponse pour obtenir plus de détails
        $errorResponse = json_decode($response, true);
        if ($errorResponse && isset($errorResponse['message'])) {
            $errorMessage = $errorResponse['message'];
        }
    }

    return $errorMessage;
}

// Point d'entrée principal
processApiRequest('processMistralApi');
?>