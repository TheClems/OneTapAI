// Variables globales
let messageHistory = [];
let isLoading = false;

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    initializeChat();
    loadHistoryFromPHP();
    setupEventListeners();
    updateWelcomeTime();
});

// Fonction d'initialisation
function initializeChat() {
    const messageInput = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendButton');
    
    if (messageInput) {
        messageInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
    }
    
    if (sendButton) {
        sendButton.addEventListener('click', sendMessage);
    }
}

// Configuration des événements
function setupEventListeners() {
    // Gestion du toggle du panneau historique
    const toggleBtn = document.getElementById('toggleHistoryBtn');
    const historyPanel = document.getElementById('chatHistoryPanel');
    
    if (toggleBtn && historyPanel) {
        toggleBtn.addEventListener('click', function() {
            historyPanel.classList.toggle('collapsed');
        });
    }
}

// Charger l'historique depuis PHP
function loadHistoryFromPHP() {
    if (typeof channelHistoryFromDB !== 'undefined' && channelHistoryFromDB.length > 0) {
        const chatMessages = document.getElementById('chatMessages');
        
        // Vider les messages existants sauf le message de bienvenue
        const welcomeMessage = chatMessages.querySelector('.message.ai');
        if (welcomeMessage) {
            welcomeMessage.remove();
        }
        
        // Ajouter les messages de l'historique
        channelHistoryFromDB.forEach(msg => {
            const role = msg.role === 'assistant' ? 'ai' : msg.role;
            addMessage(role, msg.content, new Date(msg.created_at));
        });
        
        // Mettre à jour messageHistory pour les futures requêtes
        messageHistory = channelHistoryFromDB.map(msg => ({
            role: msg.role,
            content: msg.content
        }));
    }
}

// Fonction pour ajouter un message à l'interface
function addMessage(role, content, timestamp = null) {
    const chatMessages = document.getElementById('chatMessages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${role}`;
    
    // Créer le contenu du message
    const messageContent = document.createElement('div');
    messageContent.className = 'message-content';
    
    // Si c'est du HTML (pour les réponses formatées), l'insérer directement
    if (content.includes('<br>') || content.includes('<p>') || content.includes('<code>')) {
        messageContent.innerHTML = content;
    } else {
        messageContent.textContent = content;
    }
    
    // Créer le timestamp
    const messageTime = document.createElement('div');
    messageTime.className = 'message-time';
    
    if (timestamp) {
        messageTime.textContent = formatTime(timestamp);
    } else {
        messageTime.textContent = formatTime(new Date());
    }
    
    // Assembler le message
    messageDiv.appendChild(messageContent);
    messageDiv.appendChild(messageTime);
    
    // Ajouter au conteneur
    chatMessages.appendChild(messageDiv);
    
    // Faire défiler vers le bas
    chatMessages.scrollTop = chatMessages.scrollHeight;
    
    // Animation d'apparition
    setTimeout(() => {
        messageDiv.classList.add('show');
    }, 10);
}

// Fonction pour formater l'heure
function formatTime(date) {
    return date.toLocaleTimeString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Fonction pour mettre à jour l'heure de bienvenue
function updateWelcomeTime() {
    const welcomeTime = document.getElementById('welcomeTime');
    if (welcomeTime) {
        welcomeTime.textContent = formatTime(new Date());
    }
}

// Fonction pour récupérer le modèle sélectionné
function getSelectedModel() {
    // Méthode 1: Depuis l'URL
    const urlParams = new URLSearchParams(window.location.search);
    let model = urlParams.get('model');
    
    // Méthode 2: Depuis la variable PHP globale (si disponible)
    if (!model && typeof selectedModel !== 'undefined' && selectedModel) {
        model = selectedModel;
    }
    
    // Méthode 3: Depuis le select (si visible)
    if (!model) {
        const modelSelect = document.getElementById('modelSelect');
        if (modelSelect && modelSelect.value) {
            model = modelSelect.value;
        }
    }
    
    return model || 'mistral-medium'; // Valeur par défaut
}

// Fonction principale pour envoyer un message
async function sendMessage() {
    if (isLoading) return;
    
    const messageInput = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendButton');
    const chatMessages = document.getElementById('chatMessages');
    const loading = document.getElementById('loading');

    const message = messageInput.value.trim();
    if (!message) return;

    // Récupérer le modèle sélectionné
    const currentModel = getSelectedModel();
    console.log('Modèle utilisé:', currentModel);

    // Déterminer quel script API utiliser
    let apiEndpoint;
    if (currentModel === 'gemini') {
        apiEndpoint = 'gemini_api.php';
    } else {
        // Pour tous les autres modèles (mistral-large, mistral-medium, etc.)
        apiEndpoint = 'mistral_api.php'; // Remplacez par le nom de votre script Mistral
    }

    // Marquer comme en cours de chargement
    isLoading = true;

    // Désactiver l'interface pendant l'envoi
    if (messageInput) messageInput.disabled = true;
    if (sendButton) sendButton.disabled = true;
    if (loading) loading.style.display = 'block';

    // Masquer la liste des modèles après le premier message
    const modelSelector = document.querySelector('.model-selector');
    if (modelSelector) {
        modelSelector.style.display = 'none';
    }

    // Afficher l'input si il était caché
    if (messageInput) messageInput.style.display = 'block';
    if (sendButton) sendButton.style.display = 'block';

    // Ajouter le message utilisateur à l'affichage
    addMessage('user', message);
    messageInput.value = '';

    // Ajouter le message à l'historique
    messageHistory.push({
        role: 'user',
        content: message
    });

    try {
        // Récupérer l'ID du channel depuis l'URL
        const urlParams = new URLSearchParams(window.location.search);
        const chatChannelId = urlParams.get('id_channel');

        console.log('Envoi vers:', apiEndpoint);
        console.log('Channel ID:', chatChannelId);
        console.log('Messages:', messageHistory);

        const response = await fetch(apiEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                messages: messageHistory,
                chat_channel_id: chatChannelId
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log('Réponse API:', data);

        if (data.success) {
            // Ajouter la réponse de l'IA
            addMessage('ai', data.content);
            
            // Ajouter à l'historique
            messageHistory.push({
                role: 'assistant',
                content: data.content.replace(/<br\s*\/?>/gi, '\n') // Convertir les <br> en retours à la ligne
            });
        } else {
            throw new Error(data.error || 'Erreur inconnue');
        }

    } catch (error) {
        console.error('Erreur:', error);
        addMessage('ai', `Désolé, une erreur s'est produite : ${error.message}`);
    } finally {
        // Réactiver l'interface
        isLoading = false;
        if (messageInput) {
            messageInput.disabled = false;
            messageInput.focus();
        }
        if (sendButton) sendButton.disabled = false;
        if (loading) loading.style.display = 'none';
    }
}

// Fonction pour créer un nouveau chat
function createNewChat() {
    const newChatBtn = document.getElementById('newChatBtn');
    if (newChatBtn && !newChatBtn.disabled) {
        newChatBtn.click();
    }
}

// Fonction pour nettoyer l'historique (utilitaire)
function clearHistory() {
    messageHistory = [];
    const chatMessages = document.getElementById('chatMessages');
    if (chatMessages) {
        chatMessages.innerHTML = '';
    }
}

// Fonction pour exporter l'historique (bonus)
function exportHistory() {
    const historyText = messageHistory.map(msg => {
        const role = msg.role === 'assistant' ? 'IA' : 'Utilisateur';
        return `${role}: ${msg.content}`;
    }).join('\n\n');
    
    const blob = new Blob([historyText], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `chat_history_${new Date().toISOString().split('T')[0]}.txt`;
    a.click();
    URL.revokeObjectURL(url);
}

// Gestion des raccourcis clavier
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + Enter pour envoyer
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        sendMessage();
    }
    
    // Ctrl/Cmd + N pour nouveau chat
    if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
        e.preventDefault();
        createNewChat();
    }
});

// Debug: afficher les variables globales dans la console
console.log('Chat.js chargé');
console.log('messageHistory initial:', messageHistory);
console.log('selectedModel:', typeof selectedModel !== 'undefined' ? selectedModel : 'non défini');