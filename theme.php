<?php
header('Content-Type: application/json');
<script>
  const currentUserId = <?php echo json_encode($userId); ?>;
</script>
// Exemple d'exécution de la mise à jour
// Récupération des données envoyées en POST
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['theme']) || !isset($data['user_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Données manquantes']);
    exit;
}

// Connexion à la base de données (exemple)
$conn = new mysqli('localhost', 'user', 'password', 'database');
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de connexion à la base']);
    exit;
}

// Mise à jour du thème
$stmt = $conn->prepare("UPDATE users SET theme = ? WHERE id = ?");
$stmt->bind_param("ii", $data['theme'], $data['user_id']);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de la mise à jour']);
}

$stmt->close();
$conn->close();
