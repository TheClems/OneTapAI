<?php
session_start();
require_once 'config.php';

// On vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // non autorisé
    echo json_encode(["error" => "Non connecté"]);
    exit;
}

// On récupère la donnée envoyée en JSON
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['theme'])) {
    http_response_code(400);
    echo json_encode(["error" => "Thème non spécifié"]);
    exit;
}

$theme = $data['theme']; // 0 ou 1

$sql = "UPDATE users SET theme = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $theme, $user_id);
$stmt->execute();

echo json_encode(["success" => true]);
?>
