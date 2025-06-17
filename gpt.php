<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ta clé API OpenAI (à garder secrète)
$api_key = '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';

// Le message que tu veux envoyer à ChatGPT
$prompt = "Explique-moi la différence entre HTML et PHP.";

// Préparation des données à envoyer
$data = [
    "model" => "gpt-3.5-turbo", // ou "gpt-4" si tu y as accès
    "messages" => [
        ["role" => "user", "content" => $prompt]
    ]
];

// Initialisation de cURL
$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $api_key
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

// Exécution de la requête
$response = curl_exec($ch);

// Vérification des erreurs
if (curl_errno($ch)) {
    echo 'Erreur cURL : ' . curl_error($ch);
} else {
    $responseData = json_decode($response, true);

    // Affichage complet de la réponse
    echo "<h3>Réponse complète de l'API :</h3>";
    echo "<pre>";
    print_r($responseData);
    echo "</pre>";
    
    // Vérifie si 'choices' existe
    if (isset($responseData['choices'][0]['message']['content'])) {
        echo "<h3>Réponse de ChatGPT :</h3>";
        echo "<pre>" . htmlspecialchars($responseData['choices'][0]['message']['content']) . "</pre>";
    } else {
        echo "<h3>Erreur : aucune réponse trouvée.</h3>";
        echo "<pre>";
        echo "HTTP Code : $httpcode\n";
        echo "Clé API valide ? Modèle disponible ?\n";
        echo "Réponse JSON brute :\n" . htmlspecialchars($response);
        echo "</pre>";
    }
    
}

// Fermeture de cURL
curl_close($ch);
?>
