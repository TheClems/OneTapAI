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

// Fonction pour récupérer l'historique des messages d'un channel
function getChannelHistory($channelId) {
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
function getUserChannels($userId) {
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

    header("Location: ?id_channel=" . $id);
    exit;
} else {
    // Channel existant, récupérer l'historique
    $currentChannelId = $_GET['id_channel'];
    $channelHistory = getChannelHistory($currentChannelId);
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mistral AI Chat</title>
    <link rel="stylesheet" href="css/chat.css">
</head>

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
            
            <div class="chat-list" id="chatList">
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
                <h1>🤖 Mistral AI Chat</h1>
            </div>

            <div class="chat-messages" id="chatMessages">
                <?php if (empty($channelHistory)): ?>
                    <!-- Message de bienvenue seulement si pas d'historique -->
                    <div class="message ai">
                        <div class="message-content">
                            Salut ! Je suis Mistral AI. Comment puis-je t'aider aujourd'hui ? 🚀
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
                <p>Mistral réfléchit...</p>
            </div>

            <div class="input-container">
                <div class="input-group">
                    <input type="text" class="message-input" id="messageInput" placeholder="Tapez votre message..." autocomplete="off">
                    <button class="send-button" id="sendButton">
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
let messageHistory = <?php echo json_encode(array_map(function($msg) {
    return [
        'role' => $msg['role'],
        'content' => $msg['content']
    ];
}, $channelHistory)); ?>;

// Historique des messages depuis la base de données
const channelHistoryFromDB = <?php echo json_encode($channelHistory); ?>;
</script>
<script type="text/javascript" src="scripts/chat.js"></script>
<script type="text/javascript" src="scripts/nav.js"></script>
<script type="text/javascript" src="scripts/account.js"></script>