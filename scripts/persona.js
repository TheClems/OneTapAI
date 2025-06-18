// Fonction pour normaliser le texte (enlever accents et mettre en minuscules)
function normalizeText(text) {
    return text.toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');
}

// Variables globales
const filterTabs = document.querySelectorAll('.filter-tab');
const careerCards = document.querySelectorAll('.career-card');
const searchInput = document.getElementById('searchInput');
const modal = document.getElementById('careerModal');
const closeModal = document.querySelector('.close');
const startChatBtn = document.getElementById('startChatBtn');

let currentRole = '';
let currentId = ''; // Nouvelle variable pour stocker l'ID
let activeCategory = 'tous';

// Fonction de recherche améliorée
function searchCareers() {
    const searchTerm = normalizeText(searchInput.value);

    careerCards.forEach(card => {
        const title = normalizeText(card.querySelector('.career-title').textContent);
        const description = normalizeText(card.querySelector('.career-description').textContent);
        const category = normalizeText(card.querySelector('.career-category').textContent);
        const tags = Array.from(card.querySelectorAll('.tag'))
        .map(tag => normalizeText(tag.textContent).replace(/;/g, ', '))
        .join(' ');
        const searchableText = `${title} ${description} ${category} ${tags}`;

        const matchesSearch = searchableText.includes(searchTerm) || searchTerm === '';
        const matchesCategory = activeCategory === 'tous' || card.dataset.category === activeCategory;

        if (matchesSearch && matchesCategory) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// Fonction de filtrage par catégorie
function filterByCategory(category) {
    activeCategory = category;

    // Mise à jour des onglets actifs
    filterTabs.forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelector(`[data-category="${category}"]`).classList.add('active');

    // Applique les filtres
    searchCareers();
}

// Fonction pour ouvrir le modal
function openModal(card) {
    const icon = card.querySelector('.career-icon').textContent;
    const category = card.querySelector('.career-category').textContent;
    const title = card.querySelector('.career-title').textContent;
    const description = card.querySelector('.career-description').textContent;
    const model = card.dataset.model;
    const specialites = card.dataset.specialites;
    const role = card.dataset.role;
    const id = card.dataset.id; // Récupérer l'ID

    // Mise à jour du contenu du modal
    document.getElementById('modalIcon').textContent = icon;
    document.getElementById('modalCategory').textContent = category;
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalDescription').textContent = description;
    document.getElementById('modalModel').textContent = model;
    document.getElementById('modalSpecialites').textContent = specialites;

    currentRole = role;
    currentId = id; // Stocker l'ID globalement
    modal.style.display = 'block';

    // Animation d'ouverture
    setTimeout(() => {
        modal.querySelector('.modal-content').style.transform = 'scale(1)';
        modal.querySelector('.modal-content').style.opacity = '1';
    }, 10);
}

// Fonction pour fermer le modal
function closeModalFunction() {
    modal.querySelector('.modal-content').style.transform = 'scale(0.7)';
    modal.querySelector('.modal-content').style.opacity = '0';

    setTimeout(() => {
        modal.style.display = 'none';
    }, 300);
}
function uniqid(prefix = '', more_entropy = false) {
    const now = Date.now(); // timestamp en ms
    const random = Math.floor(Math.random() * 1000000000).toString(16); // valeur pseudo-aléatoire
    let unique = prefix + now.toString(16) + random;

    if (more_entropy) {
        unique += (Math.random() * 10).toFixed(8).toString(); // ajoute un peu plus d'entropie
    }

    return unique;
}

// Fonction pour démarrer une conversation
function startConversation() {
    if (currentId) {
        // Passer l'ID du persona en paramètre, chat.php générera son propre id_channel
        
        window.location.href = `chat.php?persona_id=${currentId}`;
    } else {
        alert('Erreur: ID du persona non trouvé');
    }
}
// Event Listeners

// Recherche en temps réel
searchInput.addEventListener('input', searchCareers);

// Filtrage par catégorie
filterTabs.forEach(tab => {
    tab.addEventListener('click', () => {
        const category = tab.dataset.category;
        filterByCategory(category);
    });
});

// Ouverture du modal au clic sur une carte
careerCards.forEach(card => {
    card.addEventListener('click', () => {
        openModal(card);
    });
});

// Fermeture du modal
closeModal.addEventListener('click', closeModalFunction);

// Fermeture du modal en cliquant en dehors
window.addEventListener('click', (event) => {
    if (event.target === modal) {
        closeModalFunction();
    }
});

// Démarrage de conversation
startChatBtn.addEventListener('click', startConversation);
// Gestion du clavier
document.addEventListener('keydown', (event) => {
    // Fermer le modal avec Escape
    if (event.key === 'Escape' && modal.style.display === 'block') {
        closeModalFunction();
    }

    // Focus sur la recherche avec Ctrl+F ou Cmd+F
    if ((event.ctrlKey || event.metaKey) && event.key === 'f') {
        event.preventDefault();
        searchInput.focus();
    }
});

// Fonction pour réinitialiser les filtres
function resetFilters() {
    searchInput.value = '';
    filterByCategory('tous');
}

// Animation au chargement de la page
document.addEventListener('DOMContentLoaded', () => {
    // Animation d'apparition des cartes
    careerCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';

        setTimeout(() => {
            card.style.transition = 'all 0.3s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 50);
    });
});

// Fonction utilitaire pour compter les résultats
function updateResultsCount() {
    const visibleCards = Array.from(careerCards).filter(card =>
        card.style.display !== 'none'
    );

    // Vous pouvez ajouter un élément pour afficher le nombre de résultats
    console.log(`${visibleCards.length} résultat(s) trouvé(s)`);
}

// Mise à jour du compteur après chaque recherche
const originalSearchCareers = searchCareers;
searchCareers = function () {
    originalSearchCareers();
    updateResultsCount();
};

// Fonction pour exporter les données (optionnel)
function exportCareersData() {
    const careersData = Array.from(careerCards).map(card => ({
        title: card.querySelector('.career-title').textContent,
        category: card.querySelector('.career-category').textContent,
        description: card.querySelector('.career-description').textContent,
        model: card.dataset.model,
        specialites: card.dataset.specialites,
        role: card.dataset.role
    }));

    console.log('Données des métiers IA:', careersData);
    return careersData;
}

// Rendre certaines fonctions accessibles globalement si nécessaire
window.portfolioIA = {
    resetFilters,
    exportCareersData,
    searchCareers,
    filterByCategory
};