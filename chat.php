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

// Variables pour les données du persona
$instructions = '';
$nom = '';
$tags = '';

if (isset($_GET['persona_id'])) {
    $personaId = $_GET['persona_id'];
    $pdo = getDBConnection();
    try {
        $stmt = $pdo->prepare("SELECT model, instructions, nom, tags FROM personas WHERE id = ?");
        $stmt->execute([$personaId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $selectedModel = $result['model'];
            $instructions = $result['instructions'];
            $nom = $result['nom'];
            $tags = $result['tags'];
        }
    } catch (PDOException $e) {
        error_log("Erreur récupération modèle: " . $e->getMessage());
    }
}

// Liste des modèles disponibles
$availableModels = [
    'mistral-medium-latest' => [
        'name' => 'Mistral Medium',
        'icon' => '',
        'description' => 'Équilibré'
    ],
    'mistral-large-latest' => [
        'name' => 'Mistral Large',
        'icon' => '',
        'description' => 'Équilibré'
    ],
    'claude-3.5-haiku-latest' => [
        'name' => 'Claude 3.5 Haiku',
        'icon' => '',
        'description' => 'Intelligence de Anthropic'
    ],
    'claude-sonnet-4' => [
        'name' => 'Claude Sonnet 4',
        'icon' => '',
        'description' => 'Intelligence de Anthropic'
    ],
    'grok-3-mini' => [
        'name' => 'Grok 3 Mini',
        'icon' => '',
        'description' => 'Intelligence de Grok'
    ],
    'gemini' => [
        'name' => 'Gemini',
        'icon' => '',
        'description' => 'Intelligence de Google'
    ],
    'openrouter' => [
        'name' => 'OpenRouter',
        'icon' => '',
        'description' => 'Intelligence de Google'
    ],
    'deepseek' => [
        'name' => 'DeepSeek',
        'icon' => '',
        'description' => 'Intelligence de Chine'
    ],
    'gpt' => [
        'name' => 'GPT',
        'icon' => '',
        'description' => 'Intelligence de OpenAI'
    ],
    'image' => [
        'name' => 'Image',
        'icon' => '',
        'description' => 'Intelligence de OpenAI'
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
                cc.model,
                cc.persona_name,
                COALESCE(
                    (SELECT content FROM chat_messages WHERE chat_channel_id = cc.id AND role = 'user' ORDER BY created_at ASC LIMIT 1),
                    'Nouveau chat'
                ) as first_message,
                (SELECT COUNT(*) FROM chat_messages WHERE chat_channel_id = cc.id) as message_count
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

// Fonction améliorée pour supprimer les channels vides
function cleanupEmptyChannels($userId, $currentChannelId = null)
{
    if (!$userId) return;

    $pdo = getDBConnection();
    try {
        // Supprimer tous les channels sans messages ET sans modèle défini
        // En excluant le channel actuel si spécifié
        $sql = "
            DELETE cc FROM chat_channels cc 
            LEFT JOIN chat_messages cm ON cc.id = cm.chat_channel_id 
            WHERE cc.id_user = ? 
            AND (cc.model IS NULL OR cc.model = '' OR cc.model = 'null')
            AND cm.id IS NULL
        ";
        $params = [$userId];

        if ($currentChannelId) {
            $sql .= " AND cc.id != ?";
            $params[] = $currentChannelId;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $deletedCount = $stmt->rowCount();
        if ($deletedCount > 0) {
            error_log("Supprimé $deletedCount channels vides pour l'utilisateur $userId");
        }
    } catch (PDOException $e) {
        error_log("Erreur lors du nettoyage des channels : " . $e->getMessage());
    }
}

// Fonction pour vérifier si un channel existe et appartient à l'utilisateur
function verifyChannelOwnership($channelId, $userId)
{
    $pdo = getDBConnection();
    try {
        $stmt = $pdo->prepare("SELECT id_user FROM chat_channels WHERE id = ?");
        $stmt->execute([$channelId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result && $result['id_user'] === $userId;
    } catch (PDOException $e) {
        error_log("Erreur vérification propriété channel: " . $e->getMessage());
        return false;
    }
}

// Fonction pour générer l'URL avec tous les paramètres
function buildRedirectUrl($channelId, $model = null, $personaId = null) {
    $params = ['id_channel' => $channelId];
    
    if ($model) {
        $params['model'] = $model;
    }
    
    if ($personaId) {
        $params['persona_id'] = $personaId;
    }
    
    return '?' . http_build_query($params);
}

$channelHistory = [];
$currentChannelId = null;

// Récupérer d'abord les channels existants
$userChannels = getUserChannels($userId);

if (!isset($_GET['id_channel']) || empty($_GET['id_channel'])) {
    // Pas de paramètre id_channel => vérifier s'il existe déjà un channel vide

    // Chercher un channel existant sans messages et sans modèle
    $existingEmptyChannel = null;
    foreach ($userChannels as $channel) {
        if ($channel['message_count'] == 0 && (empty($channel['model']) || $channel['model'] === 'null')) {
            $existingEmptyChannel = $channel['id'];
            break;
        }
    }

    if ($existingEmptyChannel) {
        $currentChannelId = $existingEmptyChannel;
        
        // Si un persona est sélectionné, mettre à jour le channel
        if (!empty($nom)) {
            $pdo = getDBConnection();
            try {
                $stmt = $pdo->prepare("UPDATE chat_channels SET persona_name = ?, model = ? WHERE id = ?");
                $stmt->execute([$nom, $selectedModel ?: '', $existingEmptyChannel]);
            } catch (PDOException $e) {
                error_log("Erreur mise à jour persona: " . $e->getMessage());
            }
        }
        
        $redirectUrl = buildRedirectUrl($existingEmptyChannel, $selectedModel, $_GET['persona_id'] ?? null);
        header("Location: " . $redirectUrl);
        exit;
    } else {
        // Créer un nouveau channel seulement s'il n'y en a pas de vide
        $id = uniqid('chat_', true);
        $createdAt = date('Y-m-d H:i:s');
        $pdo = getDBConnection();

        try {
            $stmt = $pdo->prepare("INSERT INTO chat_channels (id, id_user, created_at, model, persona_name) VALUES (:id, :id_user, :created_at, :model, :persona_name)");
            $stmt->execute([
                ':id' => $id,
                ':id_user' => $userId,
                ':created_at' => $createdAt,
                ':model' => $selectedModel ?: '',
                ':persona_name' => $nom ?: ''
            ]);
            $_SESSION['id_channel'] = $id;

            // Préserver TOUS les paramètres lors de la redirection
            $redirectUrl = buildRedirectUrl(
                $id, 
                $selectedModel, 
                isset($_GET['persona_id']) ? $_GET['persona_id'] : null
            );
            header("Location: " . $redirectUrl);
            exit;
        } catch (PDOException $e) {
            error_log("Erreur création channel: " . $e->getMessage());
            // En cas d'erreur, afficher la page sans redirection
            $currentChannelId = null;
        }
    }
} else {
    $currentChannelId = $_GET['id_channel'];

    // Vérifier si ce channel appartient à l'utilisateur
    if (!verifyChannelOwnership($currentChannelId, $userId)) {
        // Channel n'existe pas ou n'appartient pas à cet utilisateur
        // Rediriger vers la page sans paramètres (éviter la boucle)
        header("Location: " . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        exit;
    }

    // Récupérer l'historique
    $channelHistory = getChannelHistory($currentChannelId);

    // Nettoyer les autres channels vides (pas celui-ci)
    cleanupEmptyChannels($userId, $currentChannelId);
}

// Récupérer la liste des channels APRÈS le traitement
$userChannels = getUserChannels($userId);

// Gestion de la mise à jour du modèle
if (isset($_GET['model']) && array_key_exists($_GET['model'], $availableModels)) {
    $display_chat = "block";
    if ($currentChannelId !== null) {
        $pdo = getDBConnection();
        try {
            $stmt = $pdo->prepare("UPDATE chat_channels SET model = ? WHERE id = ? AND id_user = ?");
            $stmt->execute([$_GET['model'], $currentChannelId, $userId]);
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour du modèle : " . $e->getMessage());
        }
    }
} else {
    $display_chat = "none";
}

// Fonction pour compter les messages dans un channel
function countMessagesInChannel($channelId)
{
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

$display_list = "block"; // Par défaut, afficher la liste des modèles

if ($currentChannelId !== null) {
    $messageCount = countMessagesInChannel($currentChannelId);

    if ($messageCount > 0) {
        // Si il y a des messages, cacher la liste des modèles
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
                    <?php if ($channel['message_count'] > 0): // Afficher seulement les channels avec des messages 
                    ?>
                        <div class="chat-item <?php echo ($channel['id'] === $currentChannelId) ? 'active' : ''; ?>"
                            data-channel-id="<?php echo htmlspecialchars($channel['id']); ?>"
                            data-model="<?php echo htmlspecialchars($channel['model']); ?>">
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

            console.log('Clic sur chat item:', {
                channelId: channelId,
                channelModel: channelModel,
                selectedModel: selectedModel
            });

            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('id_channel', channelId);

            // Priorité 1: Modèle du channel s'il existe et est valide
            // Priorité 2: Modèle sélectionné actuellement
            // Priorité 3: Modèle par défaut
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

            currentUrl.searchParams.set('model', modelToUse);

            const finalUrl = currentUrl.toString();
            console.log('URL finale:', finalUrl);

            window.location.href = finalUrl;
        });
    });
</script>
<script type="text/javascript" src="scripts/chat.js"></script>
<script type="text/javascript" src="scripts/nav.js"></script>
<script type="text/javascript" src="scripts/account.js"></script>

