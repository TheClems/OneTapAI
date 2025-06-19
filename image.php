<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

$apiKey = '84b3c98803124c7cae41a7bd8b06ad1d'; // Remplace par ta vraie clé API
$apiUrl = 'https://api.bfl.ml/v1/flux-pro-1.1'; // Flux API endpoint
$outputFolder = __DIR__ . '/images_fluc'; // Folder to save the generated images

// Ensure the output folder exists
if (!is_dir($outputFolder)) {
    mkdir($outputFolder, 0777, true);
}

// Function to generate and save the image
function generateAndSaveImage($prompt, $width, $height, $apiKey, $apiUrl, $outputFolder) {
    // Create the payload for the API request
    $payload = [
        'prompt' => $prompt,
        'width' => $width,
        'height' => $height,
        'prompt_upsampling' => false,
        'seed' => rand(0, 999999), // Optional seed for reproducibility
        'safety_tolerance' => 3
    ];

    // Initialize cURL
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-Key: ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute the API request
    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) {
        die('Error: Unable to connect to the API.');
    }

    // Decode the API response
    $data = json_decode($response, true);
    if (!isset($data['image_url'])) {
        die('Error: Invalid API response - ' . $response);
    }

    // Download the image
    $imageContent = file_get_contents($data['image_url']);
    if (!$imageContent) {
        die('Error: Unable to download the image.');
    }

    // Save the image to the output folder
    $outputFile = $outputFolder . '/generated_image_' . time() . '.png';
    if (!file_put_contents($outputFile, $imageContent)) {
        die('Error: Unable to save the image.');
    }

    echo "Image successfully saved to: $outputFile\n";
}

// Example Usage
// Get values from the query string, with defaults
$prompt = isset($_GET['prompt']) ? $_GET['prompt'] : 'a serene lake at sunset, beautiful reflections, mountains in the background';
$width = isset($_GET['width']) ? (int)$_GET['width'] : 1024;
$height = isset($_GET['height']) ? (int)$_GET['height'] : 768;

// Generate the image
generateAndSaveImage($prompt, $width, $height, $apiKey, $apiUrl, $outputFolder);