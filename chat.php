<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
require_once 'config.php';

session_start();

// Gestion de l'utilisateur connecté
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    // Rediriger vers la page de connexion si pas d'utilisateur
    header("Location: login.php");
    exit;
}

// Classe pour gérer la logique de chat
class ChatManager {
    private $pdo;
    private $userId;
    
    public function __construct($userId) {
        $this->pdo = getDBConnection();
        $this->userId = $userId;
    }
    
    /**
     * Récupère les données d'un persona
     */
    public function getPersonaData($personaId) {
        try {
            $stmt = $this->pdo->prepare("SELECT model, instructions, nom, tags FROM personas WHERE id = ?");
            $stmt->execute([$personaId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur récupération persona: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère l'historique d'un channel
     */
    public function getChannelHistory($channelId) {
        try {
            $stmt = $this->pdo->prepare("
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
    
    /**
     * Récupère les channels d'un utilisateur
     */
    public function getUserChannels() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    cc.id, 
                    cc.created_at,
                    cc.model,
                    cc.persona_name,
                    cc.persona_id,
                    COALESCE(
                        (SELECT content FROM chat_messages WHERE chat_channel_id = cc.id AND role = 'user' ORDER BY created_at ASC LIMIT 1),
                        'Nouveau chat'
                    ) as first_message,
                    (SELECT COUNT(*) FROM chat_messages WHERE chat_channel_id = cc.id) as message_count
                FROM chat_channels cc 
                WHERE cc.id_user = ? 
                ORDER BY cc.created_at DESC
            ");
            $stmt->execute([$this->userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur récupération channels: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Vérifie si un channel appartient à l'utilisateur
     */
    public function verifyChannelOwnership($channelId) {
        try {
            $stmt = $this->pdo->prepare("SELECT id_user FROM chat_channels WHERE id = ?");
            $stmt->execute([$channelId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result && $result['id_user'] === $this->userId;
        } catch (PDOException $e) {
            error_log("Erreur vérification propriété channel: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Trouve un channel vide existant
     */
    public function findEmptyChannel() {
        $channels = $this->getUserChannels();
        foreach ($channels as $channel) {
            if ($channel['message_count'] == 0 && (empty($channel['model']) || $channel['model'] === 'null')) {
                return $channel['id'];
            }
        }
        return null;
    }
    
    /**
     * Crée un nouveau channel
     */
    public function createChannel($model = '', $personaName = '', $personaId = null) {
        $id = uniqid('chat_', true);
        $createdAt = date('Y-m-d H:i:s');
        
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO chat_channels (id, id_user, created_at, model, persona_name, persona_id) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$id, $this->userId, $createdAt, $model, $personaName, $personaId]);
            $_SESSION['id_channel'] = $id;
            return $id;
        } catch (PDOException $e) {
            error_log("Erreur création channel: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Met à jour un channel
     */
    public function updateChannel($channelId, $model = null, $personaName = null, $personaId = null) {
        try {
            $updates = [];
            $params = [];
            
            if ($model !== null) {
                $updates[] = "model = ?";
                $params[] = $model;
            }
            if ($personaName !== null) {
                $updates[] = "persona_name = ?";
                $params[] = $personaName;
            }
            if ($personaId !== null) {
                $updates[] = "persona_id = ?";
                $params[] = $personaId;
            }
            
            if (!empty($updates)) {
                $sql = "UPDATE chat_channels SET " . implode(', ', $updates) . " WHERE id = ? AND id_user = ?";
                $params[] = $channelId;
                $params[] = $this->userId;
                
                $stmt = $this->pdo->prepare($sql);
                return $stmt->execute($params);
            }
            return true;
        } catch (PDOException $e) {
            error_log("Erreur mise à jour channel: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Nettoie les channels vides
     */
    public function cleanupEmptyChannels($excludeChannelId = null) {
        try {
            $sql = "
                DELETE cc FROM chat_channels cc 
                LEFT JOIN chat_messages cm ON cc.id = cm.chat_channel_id 
                WHERE cc.id_user = ? 
                AND (cc.model IS NULL OR cc.model = '' OR cc.model = 'null')
                AND cm.id IS NULL
            ";
            $params = [$this->userId];
            
            if ($excludeChannelId) {
                $sql .= " AND cc.id != ?";
                $params[] = $excludeChannelId;
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            $deletedCount = $stmt->rowCount();
            if ($deletedCount > 0) {
                error_log("Supprimé $deletedCount channels vides pour l'utilisateur {$this->userId}");
            }
        } catch (PDOException $e) {
            error_log("Erreur nettoyage channels: " . $e->getMessage());
        }
    }
    
    /**
     * Compte les messages dans un channel
     */
    public function countMessagesInChannel($channelId) {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM chat_messages WHERE chat_channel_id = ?");
            $stmt->execute([$channelId]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Erreur comptage messages: " . $e->getMessage());
            return 0;
        }
    }
}

// Classe pour gérer les redirections
class RedirectManager {
    
    /**
     * Construit une URL de redirection propre
     */
    public static function buildUrl($channelId, $model = null, $personaId = null) {
        $params = ['id_channel' => $channelId];
        
        if ($model && $model !== 'null' && $model !== '') {
            $params['model'] = $model;
        }
        
        if ($personaId && $personaId !== 'null' && $personaId !== '') {
            $params['persona_id'] = $personaId;
        }
        
        return '?' . http_build_query($params);
    }
    
    /**
     * Effectue une redirection sécurisée
     */
    public static function redirect($url) {
        // Nettoyer l'URL pour éviter les injections
        $url = filter_var($url, FILTER_SANITIZE_URL);
        header("Location: " . $url);
        exit;
    }
    
    /**
     * Redirection vers la page principale
     */
    public static function redirectToMain() {
        $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        self::redirect($currentPath);
    }
}

// Initialisation
$chatManager = new ChatManager($userId);

// Liste des modèles disponibles
$availableModels = [
    'mistral-medium-latest' => ['name' => 'Mistral Medium', 'icon' => '', 'description' => 'Équilibré'],
    'mistral-large-latest' => ['name' => 'Mistral Large', 'icon' => '', 'description' => 'Équilibré'],
    'claude-3.5-haiku-latest' => ['name' => 'Claude 3.5 Haiku', 'icon' => '', 'description' => 'Intelligence de Anthropic'],
    'claude-sonnet-4' => ['name' => 'Claude Sonnet 4', 'icon' => '', 'description' => 'Intelligence de Anthropic'],
    'grok-3-mini' => ['name' => 'Grok 3 Mini', 'icon' => '', 'description' => 'Intelligence de Grok'],
    'gemini' => ['name' => 'Gemini', 'icon' => '', 'description' => 'Intelligence de Google'],
    'openrouter' => ['name' => 'OpenRouter', 'icon' => '', 'description' => 'Intelligence de Google'],
    'deepseek' => ['name' => 'DeepSeek', 'icon' => '', 'description' => 'Intelligence de Chine'],
    'gpt' => ['name' => 'GPT', 'icon' => '', 'description' => 'Intelligence de OpenAI'],
    'image' => ['name' => 'Image', 'icon' => '', 'description' => 'Intelligence de OpenAI']
];

// Variables initiales
$selectedModel = $_GET['model'] ?? null;
$personaId = $_GET['persona_id'] ?? null;
$channelId = $_GET['id_channel'] ?? null;

// Données du persona
$personaData = null;
if ($personaId) {
    $personaData = $chatManager->getPersonaData($personaId);
    if ($personaData) {
        $selectedModel = $personaData['model'] ?: $selectedModel;
        $_SESSION['selected_model'] = $selectedModel;
    }
}

// Logique principale de gestion des channels
$currentChannelId = null;
$channelHistory = [];

if (!$channelId) {
    // Pas de channel spécifié - chercher un channel vide ou en créer un
    $existingEmptyChannel = $chatManager->findEmptyChannel();
    
    if ($existingEmptyChannel) {
        // Utiliser le channel vide existant
        $currentChannelId = $existingEmptyChannel;
        
        // Mettre à jour avec les données du persona si nécessaire
        if ($personaData) {
            $chatManager->updateChannel(
                $existingEmptyChannel, 
                $selectedModel, 
                $personaData['nom'], 
                $personaId
            );
        }
        
        // Rediriger avec tous les paramètres
        RedirectManager::redirect(
            RedirectManager::buildUrl($existingEmptyChannel, $selectedModel, $personaId)
        );
    } else {
        // Créer un nouveau channel
        $newChannelId = $chatManager->createChannel(
            $selectedModel ?: '',
            $personaData['nom'] ?? '',
            $personaId
        );
        
        if ($newChannelId) {
            RedirectManager::redirect(
                RedirectManager::buildUrl($newChannelId, $selectedModel, $personaId)
            );
        } else {
            // Erreur lors de la création
            error_log("Impossible de créer un nouveau channel");
            $currentChannelId = null;
        }
    }
} else {
    // Channel spécifié - vérifier l'ownership
    if (!$chatManager->verifyChannelOwnership($channelId)) {
        // Channel invalide - rediriger vers la page principale
        RedirectManager::redirectToMain();
    }
    
    $currentChannelId = $channelId;
    $channelHistory = $chatManager->getChannelHistory($channelId);
    
    // Mettre à jour le channel si nécessaire
    if ($selectedModel || $personaData) {
        $chatManager->updateChannel(
            $channelId,
            $selectedModel,
            $personaData['nom'] ?? null,
            $personaId
        );
    }
    
    // Nettoyer les autres channels vides
    $chatManager->cleanupEmptyChannels($channelId);
}

// Récupérer la liste des channels mise à jour
$userChannels = $chatManager->getUserChannels();

// Gestion de l'affichage
$display_chat = ($selectedModel && array_key_exists($selectedModel, $availableModels)) ? "block" : "none";
$display_list = "block";

if ($currentChannelId) {
    $messageCount = $chatManager->countMessagesInChannel($currentChannelId);
    if ($messageCount > 0) {
        $display_list = "none";
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OneTap AI Chat<?php echo $selectedModel ? ' - ' . $availableModels[$selectedModel]['name'] : ''; ?></title>
    <link rel="stylesheet" href="css/chat.css">
</head>
<style>
    /* Styles pour le sélecteur de modèle */

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
        background: #2d3748;
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
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .model-select:hover {
        background: #2d3748;
        border-color: rgba(255, 255, 255, 0.4);
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .model-select:focus {
        outline: none;
        background: #2d3748;
        border-color: rgba(255, 255, 255, 0.6);
        box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.2);
    }

    .model-select option {
        background: #2d3748;
        color: black;
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

    /* Effet de glow subtil pour le select */
    .model-select:focus {
        box-shadow:
            0 0 0 3px rgba(255, 255, 255, 0.2),
            0 0 20px rgba(255, 255, 255, 0.1),
            0 4px 15px rgba(0, 0, 0, 0.2);
    }
</style>

<body>
    <?php require_once 'nav.php'; ?>

    <div class="particles" id="particles"></div>

    <div class="main-container" id="mainContainer">
        <!-- Panneau historique des chats -->
        <div class="chat-history-panel" id="chatHistoryPanel">


            <div class="history-header">
                <button class="close-chat-history" id="toggleHistoryBtnClose" title="Fermer le panneau">
                    &times;
                </button>
                <h3>💬 Historique</h3>
                <button class="new-chat-btn" id="newChatBtn">
                    ✚
                </button>
            </div>


            <div class="chat-list" id="chatList">
            <?php foreach ($userChannels as $channel): ?>
                <?php if ($channel['message_count'] > 0): ?>
                    <?php
                    // Récupérer l'ID du persona si il existe
                    $channelPersonaId = null;
                    if ($channel['persona_name']) {
                        $pdo = getDBConnection();
                        try {
                            $stmt = $pdo->prepare("SELECT id FROM personas WHERE nom = ?");
                            $stmt->execute([$channel['persona_name']]);
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            if ($result) {
                                $channelPersonaId = $result['id'];
                            }
                        } catch (PDOException $e) {
                            error_log("Erreur récupération persona: " . $e->getMessage());
                        }
                    }
                    ?>
                    <div class="chat-item <?php echo ($channel['id'] === $currentChannelId) ? 'active' : ''; ?>"
                        data-channel-id="<?php echo htmlspecialchars($channel['id']); ?>"
                        data-model="<?php echo htmlspecialchars($channel['model']); ?>"
                        <?php if ($channelPersonaId): ?>
                            data-persona="<?php echo htmlspecialchars($channelPersonaId); ?>"
                        <?php endif; ?>>
                        <div class="chat-preview">
                            <?php echo htmlspecialchars(substr($channel['first_message'], 0, 50)) . (strlen($channel['first_message']) > 50 ? '...' : ''); ?>
                        </div>
                        <div class="chat-model">
                            <?php echo $channel['model']; ?>
                        </div>
                        <?php if ($channel['persona_name']): ?>
                            <div class="chat-persona">
                                <?php echo $channel['persona_name']; ?>
                            </div>
                        <?php endif; ?>
                        <div class="chat-time">
                            🕒 <?php echo date('d/m H:i', strtotime($channel['created_at'])); ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
            </div>
        </div>

        <!-- Zone de chat principale -->
        <div class="chat-container" id="chat-container">
            <div class="header">
                <button class="open-chat-history" id="toggleHistoryBtnOpen" title="Ouvrir le panneau">
                    ▼
                </button>
                <div class="header-content">
                    <h1>🤖 <?php echo $selectedModel ? $availableModels[$selectedModel]['name'] : 'IA'; ?> Chat</h1>
                </div>
            </div>

            <div class="chat-messages" id="chatMessages">
                <?php if (empty($channelHistory)): ?>
                    <!-- Message de bienvenue seulement si pas d'historique -->
                    <div class="message ai">
                        <div class="message-content">
                            Salut ! Je suis votre assistant IA. Choisissez un modèle pour commencer ! 🚀
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
                <p><?php echo $selectedModel ? $availableModels[$selectedModel]['name'] : 'IA'; ?> réfléchit...</p>
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
    <?php if (isset($personaId)) : ?>
        const personaId = <?= json_encode($personaId) ?>;
    <?php endif; ?>

    <?php if (isset($selectedModel)) : ?>
        const selectedModelPersona = <?= json_encode($selectedModel) ?>;
    <?php endif; ?>

    <?php if (isset($instructions)) : ?>
        const personaInstructions = <?= json_encode($instructions) ?>;
    <?php endif; ?>

    <?php if (isset($nom)) : ?>
        const personaNom = <?= json_encode($nom) ?>;
    <?php endif; ?>

    <?php if (isset($tags)) : ?>
        const personaTags = <?= json_encode($tags) ?>;
    <?php endif; ?>
    // Historique des messages depuis PHP
    let messageHistory = <?php echo json_encode(array_map(function ($msg) {
                                return [
                                    'role' => $msg['role'],
                                    'content' => $msg['content']
                                ];
                            }, $channelHistory)); ?>;

    // Historique des messages depuis la base de données
    const channelHistoryFromDB = <?php echo json_encode($channelHistory); ?>;

    // Modèle sélectionné - avec une valeur par défaut si null
    const selectedModel = '<?php echo $selectedModel ?: ''; ?>';

    console.log('Modèle sélectionné au chargement:', selectedModel);

    // Gestion du changement de modèle
    document.getElementById('modelSelect').addEventListener('change', function() {
        const newModel = this.value;
        const currentUrl = new URL(window.location);
        currentUrl.searchParams.set('model', newModel);
        window.location.href = currentUrl.toString();
    });

    // Gestion du nouveau chat - éviter les redirections multiples
    document.getElementById('newChatBtn').addEventListener('click', function() {
        // Empêcher les clics multiples rapides
        if (this.disabled) return;
        this.disabled = true;

        // Rediriger vers la page sans paramètres pour créer un nouveau chat
        const currentUrl = new URL(window.location);
        const modelToUse = selectedModel || 'mistral-large';

        // Construire l'URL proprement
        window.location.href = currentUrl.pathname + '?model=' + encodeURIComponent(modelToUse);
    });

// Gestion des clics sur l'historique avec préservation du modèle
document.querySelectorAll('.chat-item').forEach(item => {
    item.addEventListener('click', function() {
        // Empêcher les clics multiples
        if (this.classList.contains('loading')) return;
        this.classList.add('loading');

        const channelId = this.dataset.channelId;
        const channelModel = this.dataset.model;
        const personaId = this.dataset.persona; // Cette valeur vient du data-persona="5"

        console.log('Clic sur chat item:', {
            channelId: channelId,
            channelModel: channelModel,
            personaId: personaId,
            selectedModel: selectedModel,
        });

        const currentUrl = new URL(window.location);
        currentUrl.searchParams.set('id_channel', channelId);

        // Déterminer le modèle à utiliser
        let modelToUse;
        
        if (channelModel && channelModel !== '' && channelModel !== 'null' && channelModel !== 'undefined') {
            modelToUse = channelModel;
            console.log('Utilisation du modèle du channel:', modelToUse);
        } else if (selectedModel && selectedModel !== '' && selectedModel !== 'null') {
            modelToUse = selectedModel;
            console.log('Utilisation du modèle sélectionné:', modelToUse);
        } else {
            modelToUse = 'mistral-large';
            console.log('Utilisation du modèle par défaut:', modelToUse);
        }

        // Toujours définir le modèle
        currentUrl.searchParams.set('model', modelToUse);

        // Ajouter le persona_id SEULEMENT s'il existe et n'est pas vide
        if (personaId && personaId !== '' && personaId !== 'null' && personaId !== 'undefined') {
            currentUrl.searchParams.set('persona_id', personaId);
            console.log('→ Persona ID ajouté:', personaId);
        } else {
            // Supprimer le paramètre persona_id s'il n'y en a pas
            currentUrl.searchParams.delete('persona_id');
            console.log('→ Pas de persona ID');
        }

        const finalUrl = currentUrl.toString();
        console.log('URL finale:', finalUrl);

        window.location.href = finalUrl;
    });
});
</script>
<script type="text/javascript" src="scripts/chat.js"></script>
<script type="text/javascript" src="scripts/nav.js"></script>
<script type="text/javascript" src="scripts/account.js"></script>
