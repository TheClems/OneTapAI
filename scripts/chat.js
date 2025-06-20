// Configuration
const chatMessages = document.getElementById('chatMessages');
const messageInput = document.getElementById('messageInput');
const sendButton = document.getElementById('sendButton');
const loading = document.getElementById('loading');
const chatHistoryPanel = document.getElementById('chatHistoryPanel');
const toggleHistoryBtn = document.getElementById('toggleHistoryBtn');
const newChatBtn = document.getElementById('newChatBtn');
const mainContainer = document.getElementById('mainContainer');
const chatHistoryPanel2 = document.getElementById('chatHistoryPanel2');
const chatContainer = document.getElementById('chatContainer');

if (typeof personaId !== 'undefined') {
    console.log('personaId:', personaId);
}
if (typeof selectedModelPersona !== 'undefined') {
    console.log('selectedModelPersona:', selectedModelPersona);
}
if (typeof personaInstructions !== 'undefined') {
    console.log('personaInstructions:', personaInstructions);
}
if (typeof personaNom !== 'undefined') {
    console.log('personaNom:', personaNom);
}
if (typeof personaTags !== 'undefined') {
    console.log('personaTags:', personaTags);
}


if (toggleHistoryBtn) {
    toggleHistoryBtn.addEventListener('click', () => {
        if (window.innerWidth < 1024) {
            if (chatContainer) chatContainer.classList.add('collapsed');
            if (chatHistoryPanel2) chatHistoryPanel2.classList.add('collapsed');
        }
    });
}

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
/*function handleMobileLayout() {
    const isMobile = window.innerWidth <= 768;
    mainContainer.classList.toggle('mobile', isMobile);
    
    if (isMobile) {
        toggleHistoryBtn.textContent = chatHistoryPanel.classList.contains('collapsed') ? '📊' : '✖️';
    } else {
        toggleHistoryBtn.textContent = chatHistoryPanel.classList.contains('collapsed') ? '📊' : '📈';
    }
}
*/


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
    // Vérifier si c'est un chemin d'image
    if (isImageResponse(content)) {
        return `<img src="${content}" alt="Image générée" style="max-width: 100%; height: auto; border-radius: 8px; cursor: pointer;" onclick="window.open('${content}', '_blank')">`;
    }
    
    // Vérifier si c'est un marqueur d'image dans l'historique
    if (content.startsWith('[IMAGE:') && content.endsWith(']')) {
        const imagePath = content.slice(8, -1); // Extraire le chemin entre [IMAGE: et ]
        return `<img src="${imagePath}" alt="Image générée" style="max-width: 100%; height: auto; border-radius: 8px; cursor: pointer;" onclick="window.open('${imagePath}', '_blank')">`;
    }
    
    // Formatage normal pour le texte
    content = content.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    content = content.replace(/\*(.*?)\*/g, '<em>$1</em>');
    content = content.replace(/^###### (.*$)/gm, '<h6>$1</h6>');
    content = content.replace(/^##### (.*$)/gm, '<h5>$1</h5>');
    content = content.replace(/^#### (.*$)/gm, '<h4>$1</h4>');
    content = content.replace(/^### (.*$)/gm, '<h3>$1</h3>');
    content = content.replace(/^## (.*$)/gm, '<h2>$1</h2>');
    content = content.replace(/^# (.*$)/gm, '<h1>$1</h1>');
    content = content.replace(/```(\w+)?\n?([\s\S]*?)```/g, function(match, lang, code) {
        return `<pre><code>${code.trim()}</code></pre>`;
    });
    content = content.replace(/`([^`]+)`/g, '<code>$1</code>');
    content = content.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank">$1</a>');
    content = content.replace(/^- (.*$)/gm, '<li>$1</li>');
    content = content.replace(/(<li>.*<\/li>)/s, '<ul>$1</ul>');
    content = content.replace(/^\d+\. (.*$)/gm, '<li>$1</li>');
    content = content.replace(/^> (.*$)/gm, '<blockquote>$1</blockquote>');
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

// Fonction pour afficher une image dans le chat
function displayImageMessage(imagePath, isUser = false) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${isUser ? 'user' : 'ai'}`;

    const now = new Date();
    const timeString = now.toLocaleTimeString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit'
    });

    // Créer l'élément image avec gestion d'erreur
    const imageHtml = `
        <div class="message-content">
            <img src="${imagePath}" 
                 alt="Image générée" 
                 style="max-width: 100%; height: auto; border-radius: 8px; cursor: pointer;"
                 onclick="window.open('${imagePath}', '_blank')"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
            <div style="display: none; padding: 10px; background: #f0f0f0; border-radius: 8px; color: #666;">
                ❌ Erreur de chargement de l'image<br>
                <small>Chemin: ${imagePath}</small>
            </div>
        </div>
        <div class="message-time">${timeString}</div>
    `;

    messageDiv.innerHTML = imageHtml;
    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

// Fonction pour déterminer si c'est une réponse d'image
function isImageResponse(content) {
    return content && (
        content.startsWith('image_api/') ||
        content.includes('.jpg') ||
        content.includes('.jpeg') ||
        content.includes('.png') ||
        content.includes('.gif') ||
        content.includes('.webp')
    );
}


//Envoyer message
async function sendMessage() {
    const messageInput = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendButton');
    const chatMessages = document.getElementById('chatMessages');
    const loading = document.getElementById('loading');

    const message = messageInput.value.trim();
    if (!message) return;

    let selectedModel = getSelectedModel();

    // Si des instructions personnalisées existent, forcer un modèle spécifique
    if (typeof personaInstructions !== 'undefined' &&
        typeof personaInstructions === 'string' &&
        personaInstructions.trim() !== '' &&
        typeof selectedModelPersona !== 'undefined') {
        selectedModel = selectedModelPersona;
    }

    // Déterminer l'endpoint API à utiliser
    let apiEndpoint;
    switch (selectedModel) {
        case 'gemini': apiEndpoint = 'gemini_api.php'; break;
        case 'openrouter': apiEndpoint = 'openrouter_api.php'; break;
        case 'mistral-medium-latest': apiEndpoint = 'mistral_api_medium_latest.php'; break;
        case 'mistral-large-latest': apiEndpoint = 'mistral_api_large_latest.php'; break;
        case 'claude-3.5-haiku-latest': apiEndpoint = 'claude_api_haiku_latest.php'; break;
        case 'claude-sonnet-4': apiEndpoint = 'claude_api_sonnet_4.php'; break;
        case 'grok-3-mini': apiEndpoint = 'grok_api_3_mini.php'; break;
        case 'deepseek': apiEndpoint = 'deepseek_api.php'; break;
        case 'gpt': apiEndpoint = 'gpt_api.php'; break;
        case 'image': apiEndpoint = 'image.php'; break; // Utiliser votre nouveau fichier
        default: apiEndpoint = 'default_api.php'; break;
    }

    // Désactiver l'interface pendant l'envoi
    messageInput.disabled = true;
    sendButton.disabled = true;
    loading.style.display = 'block';

    displayMessage(message, true);
    messageInput.value = '';

    messageHistory.push({
        role: 'user',
        content: message
    });

    try {
        const urlParams = new URLSearchParams(window.location.search);
        const chatChannelId = urlParams.get('id_channel');

        let messagesToSend = [...messageHistory];

        // Ajouter instructions si elles existent
        if (typeof personaInstructions !== 'undefined' &&
            typeof personaInstructions === 'string' &&
            personaInstructions.trim() !== '' &&
            typeof personaNom !== 'undefined' &&
            typeof personaTags !== 'undefined') {

            messagesToSend = [{
                role: 'system',
                content: `Tu es ${personaNom}, Utilise tes compétences en ${personaTags}. ${personaInstructions.trim()}`
            }, ...messagesToSend];
        }

        const response = await fetch(apiEndpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                messages: messagesToSend,
                chat_channel_id: chatChannelId
            })
        });

        // Vérifier si la réponse est OK
        if (!response.ok) {
            throw new Error(`Erreur HTTP: ${response.status} - ${response.statusText}`);
        }

        // Lire le contenu de la réponse
        const responseText = await response.text();
        
        // Vérifier si le contenu est vide
        if (!responseText || responseText.trim() === '') {
            throw new Error('Réponse vide du serveur');
        }

        let data;
        try {
            data = JSON.parse(responseText);
        } catch (jsonError) {
            console.error('Erreur JSON:', jsonError);
            console.error('Contenu de la réponse:', responseText);
            throw new Error('Réponse invalide du serveur (JSON malformé)');
        }

        if (data.success) {
            // Vérifier si c'est une réponse d'image
            if (data.type === 'image' || isImageResponse(data.content)) {
                displayImageMessage(data.content, false);
                messageHistory.push({
                    role: 'assistant',
                    content: `[IMAGE: ${data.content}]`
                });
            } else {
                // Réponse texte normale
                displayMessage(data.content, false);
                messageHistory.push({
                    role: 'assistant',
                    content: data.content.replace(/<br\s*\/?>/gi, '\n')
                });
            }
        } else {
            throw new Error(data.error || 'Erreur inconnue');
        }

    } catch (error) {
        console.error('Erreur complète:', error);
        
        // Afficher un message d'erreur plus détaillé
        let errorMessage = 'Désolé, une erreur s\'est produite';
        
        if (error.message.includes('JSON')) {
            errorMessage += ' (erreur de format de réponse)';
        } else if (error.message.includes('HTTP')) {
            errorMessage += ' (erreur de connexion)';
        } else if (error.message.includes('vide')) {
            errorMessage += ' (réponse vide du serveur)';
        }
        
        errorMessage += ` : ${error.message}`;
        
        displayMessage(errorMessage, false);
    } finally {
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
