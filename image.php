<?php

$apiKey = 'a9c25d736c28420cb74a3d087ac84ce6'; // Ta clé API ici
$endpoint = 'https://api.aimlapi.com/v1/chat/completions';

$data = [
    "model" => "gpt-4o",
    "messages" => [
        [
            "role" => "user",
            "content" => "Read this article and generate a short prompt for illustration generation (no need to output the words like Prompt): Futuristic Cities. Cities of the future promise to radically transform how people live, work, and move. Instead of sprawling layouts, we’ll see vertical structures that integrate residential, work, and public spaces into single, self-sustaining ecosystems. Architecture will adapt to climate conditions, and buildings will be energy-efficient—generating power through solar panels, wind turbines, and even foot traffic. Transportation will be fully autonomous and silent. Streets will be freed from traffic and pollution, with ground-level space given back to pedestrians and greenery. Drones, magnetic levitation pods, and underground tunnels will handle most transit. Artificial intelligence will manage traffic flow and energy distribution in real time, ensuring maximum efficiency and comfort. Digital technology will be woven into every part of urban life. Smart homes will adapt to residents’ habits, while city services will respond instantly to citizen needs. Virtual and augmented reality will blur the line between physical and digital spaces. These cities won’t just be places to live—they’ll be flexible, sustainable environments where technology truly serves people."
        ],
    ],
];

$headers = [
    "Content-Type: application/json",
    "Authorization: Bearer $apiKey",
];

$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo 'Erreur cURL : ' . curl_error($ch);
} else {
    if ($httpCode == 200) {
        $result = json_decode($response, true);
        if (isset($result['choices'][0]['message']['content'])) {
            echo "Réponse du modèle :\n" . $result['choices'][0]['message']['content'] . "\n";
        } else {
            echo "Réponse inattendue :\n$response\n";
        }
    } else {
        echo "Erreur HTTP $httpCode :\n$response\n";
    }
}

curl_close($ch);
