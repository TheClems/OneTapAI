// Configuration
const chatMessages = document.getElementById('chatMessages');
const messageInput = document.getElementById('messageInput');
const sendButton = document.getElementById('sendButton');
const loading = document.getElementById('loading');
const chatHistoryPanel = document.getElementById('chatHistoryPanel');
const toggleHistoryBtn = document.getElementById('toggleHistoryBtn');
const newChatBtn = document.getElementById('newChatBtn');
const mainContainer = document.getElementById('mainContainer');


// Fonction pour formater l'heure
function formatTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleTimeString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Gérer le responsive basé sur la navbar
function updateLayoutForNavbar() {
    const nav = document.querySelector('nav') || document.querySelector('.nav') || document.querySelector('#nav');
    if (nav) {
        const navWidth = nav.offsetWidth;
        const isCollapsed = navWidth < 100; // Supposer que la navbar est collapsée si < 100px
        
        mainContainer.classList.toggle('nav-collapsed', isCollapsed);
    }
}

// Gérer le responsive mobile
function handleMobileLayout() {
    const isMobile = window.innerWidth <= 768;
    mainContainer.classList.toggle('mobile', isMobile);
    
    if (isMobile) {
        toggleHistoryBtn.textContent = chatHistoryPanel.classList.contains('collapsed') ? '📊' : '✖️';
    } else {
        toggleHistoryBtn.textContent = chatHistoryPanel.classList.contains('collapsed') ? '📊' : '📈';
    }
}

// Basculer l'affichage de l'historique
toggleHistoryBtn.addEventListener('click', () => {
    chatHistoryPanel.classList.toggle('collapsed');
    const isCollapsed = chatHistoryPanel.classList.contains('collapsed');
    
    if (window.innerWidth <= 768) {
        toggleHistoryBtn.textContent = isCollapsed ? '📊' : '✖️';
    } else {
        toggleHistoryBtn.textContent = isCollapsed ? '📊' : '📈';
    }
});

// Créer un nouveau chat
newChatBtn.addEventListener('click', () => {
    window.location.href = "chat.php"; // Retour à la page sans paramètres
});

// Gérer les clics sur les éléments de chat
document.addEventListener('click', (e) => {
    const chatItem = e.target.closest('.chat-item');
    if (chatItem) {
        const channelId = chatItem.dataset.channelId;
        const channelModel = chatItem.dataset.model;
        
        if (channelId) {
            console.log('Clic sur chat item:', {
                channelId: channelId,
                channelModel: channelModel,
                selectedModel: typeof selectedModel !== 'undefined' ? selectedModel : null
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
            } else if (typeof selectedModel !== 'undefined' && selectedModel && selectedModel !== '' && selectedModel !== 'null') {
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
        }
    }
});

// Charger l'historique existant
function loadHistoryMessages() {
    if (channelHistoryFromDB && channelHistoryFromDB.length > 0) {
        // Vider le conteneur (enlever le message de bienvenue)
        chatMessages.innerHTML = '';
        
        channelHistoryFromDB.forEach((message, index) => {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${message.role === 'user' ? 'user' : 'ai'}`;
            
            const timeString = formatTime(message.created_at);
            
            // Formater le contenu pour les messages de l'IA depuis la DB
            const formattedContent = message.role === 'user' ? 
                message.content : 
                formatMessageContent(message.content);
            
            messageDiv.innerHTML = `
                <div class="message-content">${formattedContent}</div>
                <div class="message-time">${timeString}</div>
            `;
            
            // Ajouter un délai pour l'animation
            setTimeout(() => {
                chatMessages.appendChild(messageDiv);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }, index * 100);
        });
    } else {
        // Pas d'historique, afficher l'heure de bienvenue
        const welcomeTimeEl = document.getElementById('welcomeTime');
        if (welcomeTimeEl) {
            welcomeTimeEl.textContent = new Date().toLocaleTimeString('fr-FR', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    }
}

// Créer les particules d'arrière-plan
function createParticles() {
    const particles = document.getElementById('particles');
    for (let i = 0; i < 20; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        particle.style.left = Math.random() * 100 + '%';
        particle.style.width = Math.random() * 4 + 2 + 'px';
        particle.style.height = particle.style.width;
        particle.style.animationDelay = Math.random() * 20 + 's';
        particle.style.animationDuration = (Math.random() * 10 + 15) + 's';
        particles.appendChild(particle);
    }
}

// Fonction pour formater le contenu d'un message
function formatMessageContent(content) {
    // Convertir les **texte** en <strong>
    content = content.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    
    // Convertir les *texte* en <em>
    content = content.replace(/\*(.*?)\*/g, '<em>$1</em>');

    // Convertir les ##### Titre en <h6>
    content = content.replace(/^###### (.*$)/gm, '<h6>$1</h6>');


    // Convertir les ##### Titre en <h5>
    content = content.replace(/^##### (.*$)/gm, '<h5>$1</h5>');
    // Convertir les #### Titre en <h4>
    content = content.replace(/^#### (.*$)/gm, '<h4>$1</h4>');

    // Convertir les ### Titre en <h3>
    content = content.replace(/^### (.*$)/gm, '<h3>$1</h3>');
    
    // Convertir les ## Titre en <h2>
    content = content.replace(/^## (.*$)/gm, '<h2>$1</h2>');
    
    // Convertir les # Titre en <h1>
    content = content.replace(/^# (.*$)/gm, '<h1>$1</h1>');
    
    // Convertir les blocs de code ```code```
    content = content.replace(/```(\w+)?\n?([\s\S]*?)```/g, function(match, lang, code) {
        return `<pre><code>${code.trim()}</code></pre>`;
    });
    
    // Convertir le code inline `code`
    content = content.replace(/`([^`]+)`/g, '<code>$1</code>');
    
    // Convertir les liens [texte](url)
    content = content.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank">$1</a>');
    
    // Convertir les listes - item
    content = content.replace(/^- (.*$)/gm, '<li>$1</li>');
    content = content.replace(/(<li>.*<\/li>)/s, '<ul>$1</ul>');
    
    // Convertir les listes numérotées 1. item
    content = content.replace(/^\d+\. (.*$)/gm, '<li>$1</li>');
    
    // Convertir les citations > texte
    content = content.replace(/^> (.*$)/gm, '<blockquote>$1</blockquote>');
    
    // Convertir les retours à la ligne
    content = content.replace(/\n/g, '<br>');
    
    return content;
}

// Afficher un message dans le chat (sans affecter l'historique)
function displayMessage(content, isUser = false) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${isUser ? 'user' : 'ai'}`;

    const now = new Date();
    const timeString = now.toLocaleTimeString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit'
    });

    // Formater le contenu seulement pour les messages de l'IA
    const formattedContent = isUser ? content : formatMessageContent(content);

    messageDiv.innerHTML = `
        <div class="message-content">${formattedContent}</div>
        <div class="message-time">${timeString}</div>
    `;

    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

// Afficher une erreur
function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    chatMessages.appendChild(errorDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;

    setTimeout(() => {
        errorDiv.remove();
    }, 5000);
}

function getSelectedModel() {
    // Méthode 1: Depuis l'URL
    const urlParams = new URLSearchParams(window.location.search);
    let model = urlParams.get('model');
    
    // Méthode 2: Depuis la variable PHP globale (si disponible)
    if (!model && typeof selectedModel !== 'undefined') {
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


// Envoyer un message
async function sendMessage() {
    const messageInput = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendButton');
    const chatMessages = document.getElementById('chatMessages');
    const loading = document.getElementById('loading');

    const message = messageInput.value.trim();

    if (!message) return;

    // Récupérer le modèle sélectionné
    const selectedModel = getSelectedModel(); // Cette fonction doit être définie

    // Si instructions personnalisées → forcer un modèle compatible
    if (typeof personaInstructions === 'string' && personaInstructions.trim() !== '') {
        // Par exemple, on force GPT pour les instructions personnalisées
        selectedModel = selectedModelPersona;
    }
    // Déterminer quel script API utiliser
    let apiEndpoint;
    if (selectedModel === 'gemini') {
        apiEndpoint = 'gemini_api.php';
    } else if (selectedModel === 'openrouter') {
        apiEndpoint = 'openrouter_api.php';
    } else if (selectedModel === 'mistral-medium') {
        apiEndpoint = 'mistral_api.php';
    } else if (selectedModel === 'deepseek') {
        apiEndpoint = 'deepseek_api.php';
    } else if (selectedModel === 'gpt') {
        apiEndpoint = 'gpt_api.php';
    }

    // Désactiver l'interface pendant l'envoi
    messageInput.disabled = true;
    sendButton.disabled = true;
    loading.style.display = 'block';

    // Ajouter le message utilisateur à l'affichage
    displayMessage(message, true);
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

        // Créer la liste des messages à envoyer
        let messagesToSend = [...messageHistory];

        // Ajouter les instructions si définies et non vides
        if (typeof personaInstructions === 'string' && personaInstructions.trim() !== '') {
            messagesToSend = [{
                role: 'system',
                content: personaInstructions.trim()
            }, ...messagesToSend];
        }

        const response = await fetch(apiEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                messages: messagesToSend,
                chat_channel_id: chatChannelId
            })
        });

        const data = await response.json();

        if (data.success) {
            // Ajouter la réponse de l'IA
            displayMessage(data.content, false);

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
        displayMessage(`Désolé, une erreur s'est produite : ${error.message}`, false);
    } finally {
        // Réactiver l'interface
        messageInput.disabled = false;
        sendButton.disabled = false;
        loading.style.display = 'none';
        messageInput.focus();
    }
}



// Gestionnaires d'événements
sendButton.addEventListener('click', sendMessage);

messageInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});

// Gestion du redimensionnement
window.addEventListener('resize', () => {
    handleMobileLayout();
    updateLayoutForNavbar();
});

// Auto-focus sur l'input
messageInput.focus();

// Créer les particules
createParticles();

// Charger l'historique au démarrage
loadHistoryMessages();

// Initialiser le layout
handleMobileLayout();
updateLayoutForNavbar();

// Observer les changements de la navbar (si elle peut être toggle)
const navObserver = new MutationObserver(() => {
    updateLayoutForNavbar();
});

// Observer la navbar si elle existe
const nav = document.querySelector('nav') || document.querySelector('.nav') || document.querySelector('#nav');
if (nav) {
    navObserver.observe(nav, { 
        attributes: true, 
        attributeFilter: ['class', 'style'] 
    });
}

// Effet de frappe automatique pour le message de bienvenue (seulement si pas d'historique)
if (channelHistoryFromDB.length === 0) {
    setTimeout(() => {
        const welcomeMessage = document.querySelector('.message.ai .message-content');
        if (welcomeMessage) {
            const text = welcomeMessage.textContent;
            welcomeMessage.textContent = '';

            let i = 0;
            const typeInterval = setInterval(() => {
                welcomeMessage.textContent += text[i];
                i++;
                if (i >= text.length) {
                    clearInterval(typeInterval);
                }
            }, 50);
        }
    }, 500);
}

// Gestion du swipe sur mobile pour l'historique
let startY = 0;
let startX = 0;

chatHistoryPanel.addEventListener('touchstart', (e) => {
    startY = e.touches[0].clientY;
    startX = e.touches[0].clientX;
});

chatHistoryPanel.addEventListener('touchend', (e) => {
    if (!startY || !startX) return;
    
    const endY = e.changedTouches[0].clientY;
    const endX = e.changedTouches[0].clientX;
    const diffY = startY - endY;
    const diffX = startX - endX;
    
    // Swipe vertical sur mobile
    if (window.innerWidth <= 768 && Math.abs(diffY) > Math.abs(diffX) && Math.abs(diffY) > 50) {
        if (diffY > 0) {
            // Swipe up - fermer l'historique
            chatHistoryPanel.classList.add('collapsed');
            toggleHistoryBtn.textContent = '📊';
        } else {
            // Swipe down - ouvrir l'historique
            chatHistoryPanel.classList.remove('collapsed');
            toggleHistoryBtn.textContent = '✖️';
        }
    }
    
    startY = 0;
    startX = 0;
});
