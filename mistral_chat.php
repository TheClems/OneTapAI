<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
require_once 'config.php';

session_start();  // Toujours démarrer la session en début de script

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    // Tu peux maintenant utiliser $userId
} else {
    // Pas d'utilisateur connecté ou ID non stocké en session
    $userId = null;
}

// Gestion du modèle sélectionné
$selectedModel = isset($_GET['model']) ? $_GET['model'] : null;
$_SESSION['selected_model'] = $selectedModel;

// Liste des modèles disponibles
$availableModels = [
    'mistral-large' => [
        'name' => 'Mistral Large',
        'icon' => '🚀',
        'description' => 'Le plus puissant'
    ],
    'mistral-medium' => [
        'name' => 'Mistral Medium', 
        'icon' => '⚡',
        'description' => 'Équilibré'
    ],
    'mistral-small' => [
        'name' => 'Mistral Small',
        'icon' => '💨',
        'description' => 'Rapide et efficace'
    ],
    'codestral' => [
        'name' => 'Codestral',
        'icon' => '💻',
        'description' => 'Spécialisé code'
    ]
];

// Fonction pour récupérer l'historique des messages d'un channel
function getChannelHistory($channelId)
{
    $pdo = getDBConnection();
    try {
        $stmt = $pdo->prepare("
            SELECT role, content, created_at 
            FROM chat_messages 
            WHERE chat_channel_id = ? 
            ORDER BY created_at ASC
        ");
        $stmt->execute([$channelId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur récupération historique: " . $e->getMessage());
        return [];
    }
}

// Fonction pour récupérer la liste des channels
function getUserChannels($userId)
{
    $pdo = getDBConnection();
    try {
        $stmt = $pdo->prepare("
            SELECT 
                cc.id, 
                cc.created_at,
                COALESCE(
                    (SELECT content FROM chat_messages WHERE chat_channel_id = cc.id AND role = 'user' ORDER BY created_at ASC LIMIT 1),
                    'Nouveau chat'
                ) as first_message
            FROM chat_channels cc 
            WHERE cc.id_user = ? 
            ORDER BY cc.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur récupération channels: " . $e->getMessage());
        return [];
    }
}

$channelHistory = [];
$currentChannelId = null;
$userChannels = getUserChannels($userId);

if (!isset($_GET['id_channel']) || empty($_GET['id_channel'])) {
    // Pas de paramètre id_channel ou id_channel vide => création d'un nouveau chat

    $id = uniqid('chat_', true); // ID unique

    $createdAt = date('Y-m-d H:i:s');
    $pdo = getDBConnection();

    $stmt = $pdo->prepare("INSERT INTO chat_channels (id, id_user, created_at) VALUES (:id, :id_user, :created_at)");
    $stmt->execute([
        ':id' => $id,
        ':id_user' => $userId,
        ':created_at' => $createdAt
    ]);
    $_SESSION['id_channel'] = $id;

    // Préserver le modèle sélectionné lors de la redirection
    $modelParam = isset($_GET['model']) ? '&model=' . urlencode($_GET['model']) : '';
    header("Location: ?id_channel=" . $id . $modelParam);
    exit;
} else {

    $currentChannelId = $_GET['id_channel'];

    // Vérifie si ce chat appartient à l'utilisateur connecté
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT id_user FROM chat_channels WHERE id = ?");
    $stmt->execute([$currentChannelId]);
    $channelOwner = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$channelOwner || $channelOwner['id_user'] !== $userId) {
        // Soit le chat n'existe pas, soit il n'appartient pas à cet utilisateur
        header("Location: mistral_chat.php"); // ou page d'accueil, ou 403.php
        exit;
    }

    // Sinon on récupère l'historique normalement
    $channelHistory = getChannelHistory($currentChannelId);

}

if (isset($_GET['model']) && array_key_exists($_GET['model'], $availableModels)) {
    $display_chat = "block";
    if ($currentChannelId !== null) {
        $pdo = getDBConnection();
        try {
            $stmt = $pdo->prepare("UPDATE chat_channels SET model = ? WHERE id = ?");
            $stmt->execute([$_GET['model'], $currentChannelId]);
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour model_valid : " . $e->getMessage());
        }
    }
} else {
    $display_chat = "none";
}

function countMessagesInChannel($channelId) {
    $pdo = getDBConnection();
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM chat_messages WHERE chat_channel_id = ?");
        $stmt->execute([$channelId]);
        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Erreur lors du comptage des messages : " . $e->getMessage());
        return 0;
    }
}
if ($currentChannelId !== null) {
    $messageCount = countMessagesInChannel($currentChannelId);

    if ($messageCount > 1) {
        // Ici tu mets ce que tu veux faire si plus de 1 message
        // Par exemple afficher un message, modifier une variable, etc.
        $display_list = "none";
    } else {
        $display_list = "block";
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mistral AI Chat - <?php echo $availableModels[$selectedModel]['name']; ?></title>
    <link rel="stylesheet" href="css/chat.css">
</head>
<style>
/* Styles pour le sélecteur de modèle */
.header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1rem 2rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 1200px;
    margin: 0 auto;
    gap: 2rem;
}

.header-content h1 {
    margin: 0;
    font-size: 1.8rem;
    font-weight: 600;
}

.model-selector {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.5rem;
    min-width: 200px;
}

.model-label {
    font-size: 0.9rem;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 0.25rem;
}

.model-select {
    background: rgba(255, 255, 255, 0.15);
    border: 2px solid rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    color: white;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    min-width: 200px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.model-select:hover {
    background: rgba(255, 255, 255, 0.25);
    border-color: rgba(255, 255, 255, 0.4);
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.model-select:focus {
    outline: none;
    background: rgba(255, 255, 255, 0.25);
    border-color: rgba(255, 255, 255, 0.6);
    box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.2);
}

.model-select option {
    background: #2d3748;
    color: white;
    padding: 0.5rem;
    font-weight: 500;
}

.model-description {
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.8);
    font-style: italic;
    text-align: right;
    margin-top: 0.25rem;
    min-height: 1rem;
    transition: all 0.3s ease;
}

/* Animation pour la description */
.model-description {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-5px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive pour mobile */
@media (max-width: 768px) {
    .header-content {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }

    .header-content h1 {
        font-size: 1.5rem;
    }

    .model-selector {
        align-items: center;
        width: 100%;
    }

    .model-select {
        min-width: 250px;
        max-width: 100%;
    }

    .model-description {
        text-align: center;
    }
}

@media (max-width: 480px) {
    .header {
        padding: 1rem;
    }

    .model-select {
        min-width: 200px;
        font-size: 0.9rem;
        padding: 0.6rem 0.8rem;
    }

    .header-content h1 {
        font-size: 1.3rem;
    }
}

/* Style amélioré pour les options du select */
.model-select option[value="mistral-large"] {
    background: linear-gradient(135deg, #ff6b6b, #ee5a52);
}

.model-select option[value="mistral-medium"] {
    background: linear-gradient(135deg, #4ecdc4, #44a08d);
}

.model-select option[value="mistral-small"] {
    background: linear-gradient(135deg, #45b7d1, #3498db);
}

.model-select option[value="codestral"] {
    background: linear-gradient(135deg, #96ceb4, #85c9a0);
}

/* Effet de glow subtil pour le select */
.model-select:focus {
    box-shadow: 
        0 0 0 3px rgba(255, 255, 255, 0.2),
        0 0 20px rgba(255, 255, 255, 0.1),
        0 4px 15px rgba(0,0,0,0.2);
}

/* Animation au survol de la description */
.model-selector:hover .model-description {
    color: rgba(255, 255, 255, 1);
    transform: scale(1.05);
}
</style>
<body>
    <?php require_once 'nav.php'; ?>

    <div class="particles" id="particles"></div>

    <div class="main-container" id="mainContainer">
        <!-- Panneau historique des chats -->
        <div class="chat-history-panel" id="chatHistoryPanel">
            <button class="toggle-history-btn" id="toggleHistoryBtn" title="Basculer l'historique">
                📊
            </button>

            <div class="history-header">
                <h3>
                    💬 Historique
                </h3>
                <button class="new-chat-btn" id="newChatBtn">
                    ➕ Nouveau
                </button>
            </div>

            <div class="chat-list" id="chatList" >
                <?php foreach ($userChannels as $channel): ?>
                    <div class="chat-item <?php echo ($channel['id'] === $currentChannelId) ? 'active' : ''; ?>"
                        data-channel-id="<?php echo htmlspecialchars($channel['id']); ?>">
                        <div class="chat-preview">
                            <?php echo htmlspecialchars(substr($channel['first_message'], 0, 50)) . (strlen($channel['first_message']) > 50 ? '...' : ''); ?>
                        </div>
                        <div class="chat-time">
                            🕒 <?php echo date('d/m H:i', strtotime($channel['created_at'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Zone de chat principale -->
        <div class="chat-container">
            <div class="header">
                <div class="header-content">
                    <h1>🤖 Mistral AI Chat</h1>
                    

                </div>
            </div>

            <div class="chat-messages" id="chatMessages">
                <?php if (empty($channelHistory)): ?>
                    <!-- Message de bienvenue seulement si pas d'historique -->
                    <div class="message ai">
                        <div class="message-content">
                            Salut ! Je suis <?php echo $availableModels[$selectedModel]['name']; ?>. Comment puis-je t'aider aujourd'hui ? 🚀
                        </div>
                        <div class="message-time" id="welcomeTime"></div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="loading" id="loading">
                <div class="loading-dots">
                    <div class="loading-dot"></div>
                    <div class="loading-dot"></div>
                    <div class="loading-dot"></div>
                </div>
                <p><?php echo $availableModels[$selectedModel]['name']; ?> réfléchit...</p>
            </div>

            <div class="input-container">
                <div class="input-group">
                    <!-- Sélecteur de modèle -->
                    <div class="model-selector" style="display: <?php echo $display_list; ?>;">
                        <select id="modelSelect" class="model-select">
                            <option value="" disabled selected>-- Choisir un modèle --</option>
                            <?php foreach ($availableModels as $modelKey => $modelInfo): ?>
                                <option value="<?php echo $modelKey; ?>"
                                    <?php echo ($selectedModel === $modelKey) ? 'selected' : ''; ?>>
                                    <?php echo $modelInfo['icon'] . ' ' . $modelInfo['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <input type="text" class="message-input" id="messageInput" placeholder="Tapez votre message..." autocomplete="off" style="display: <?php echo $display_chat; ?>;">
                    <button class="send-button" id="sendButton" style="display: <?php echo $display_chat; ?>;">
                        <span>Envoyer</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
<script>
    // Historique des messages depuis PHP
    let messageHistory = <?php echo json_encode(array_map(function ($msg) {
                                return [
                                    'role' => $msg['role'],
                                    'content' => $msg['content']
                                ];
                            }, $channelHistory)); ?>;

    // Historique des messages depuis la base de données
    const channelHistoryFromDB = <?php echo json_encode($channelHistory); ?>;
    
    // Modèle sélectionné
    const selectedModel = '<?php echo $selectedModel; ?>';

    // Gestion du changement de modèle
    document.getElementById('modelSelect').addEventListener('change', function() {
        const newModel = this.value;
        const currentUrl = new URL(window.location);
        currentUrl.searchParams.set('model', newModel);
        window.location.href = currentUrl.toString();
    });

    // Gestion du nouveau chat avec préservation du modèle
    document.getElementById('newChatBtn').addEventListener('click', function() {
        const currentUrl = new URL(window.location);
        currentUrl.searchParams.delete('id_channel');
        currentUrl.searchParams.set('model', selectedModel);
        window.location.href = currentUrl.toString();
    });

    // Gestion des clics sur l'historique avec préservation du modèle
    document.querySelectorAll('.chat-item').forEach(item => {
        item.addEventListener('click', function() {
            const channelId = this.dataset.channelId;
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('id_channel', channelId);
            currentUrl.searchParams.set('model', selectedModel);
            window.location.href = currentUrl.toString();
        });
    });
</script>
<script type="text/javascript" src="scripts/chat.js"></script>
<script type="text/javascript" src="scripts/nav.js"></script>
<script type="text/javascript" src="scripts/account.js"></script>