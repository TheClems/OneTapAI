<?php
// Ta clé API Stability AI
$apiKey = 'sk-sTX9dwsqDfL8F9k0dpvpTzPQjJ5rFkW1tOPEPwcsGvVv4wHj';  // Remplace par ta clé réelle

// URL de l'API
$url = 'https://api.stability.ai/v2beta/stable-image/generate/sd3';

// Prompt et format de sortie
$prompt = "Napoleon";
$outputFormat = "jpeg";

// Fichier de sortie
$outputFile = __DIR__ . '/napoleon.jpeg';

// Initialisation de cURL
$ch = curl_init($url);

// Configuration des options cURL
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // On récupère la réponse
curl_setopt($ch, CURLOPT_POST, true);

// Construction du formulaire multipart/form-data
$postFields = [
    'prompt' => $prompt,
    'output_format' => $outputFormat,
];

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "authorization: Bearer $apiKey",
    "accept: image/*",
]);

// cURL pour POST avec formulaire (multipart/form-data)
curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

// Exécution de la requête
$response = curl_exec($ch);

// Vérification des erreurs cURL
if(curl_errno($ch)) {
    echo "Erreur cURL : " . curl_error($ch);
    curl_close($ch);
    exit;
}

// Vérification du code HTTP
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo "Erreur HTTP $httpCode reçue de l'API :\n";
    echo $response;
    exit;
}

// Enregistrement de l'image dans un fichier
file_put_contents($outputFile, $response);

echo "Image générée et sauvegardée dans : $outputFile\n";
