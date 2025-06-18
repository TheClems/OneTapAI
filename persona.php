
<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
require_once 'config.php';

session_start();  // Toujours démarrer la session en début de script
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Métiers IA - Portfolio Professionnel</title>

    <link rel="stylesheet" href="css/animations.css">
    <style>
        .body_job {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #ffffff;
            min-height: 100vh;
            line-height: 1.6;
            margin-left: 17.5rem;
        }

        .body_job.collapsed {
            margin-left: 0;
        }

        .body_job.mobile {
            margin-left: 0;

        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .search-container {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .search-box {
            position: relative;
            width: 100%;
            max-width: 500px;
        }

        .search-input {
            width: 100%;
            padding: 1rem 1.5rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 25px;
            color: #ffffff;
            font-size: 1rem;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #64ffda;
            box-shadow: 0 0 20px rgba(100, 255, 218, 0.3);
        }

        .search-input::placeholder {
            color: #888;
        }

        .filter-tabs {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 3rem;
        }

        .filter-tab {
            padding: 0.8rem 1.5rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 25px;
            color: #ffffff;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .filter-tab:hover, .filter-tab.active {
            background: linear-gradient(135deg, #64ffda, #1de9b6);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(100, 255, 218, 0.3);
        }

        .careers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .career-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2rem;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            cursor: pointer;
            opacity: 1;
            transform: translateY(0);
        }

        .career-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(135deg, #64ffda, #1de9b6);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .career-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .career-card:hover::before {
            opacity: 1;
        }

        .career-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            display: block;
        }

        .career-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #ffffff;
        }

        .career-description {
            color: #cccccc;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }

        .career-category {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            background: rgba(100, 255, 218, 0.2);
            border: 1px solid rgba(100, 255, 218, 0.3);
            border-radius: 15px;
            font-size: 0.8rem;
            color: #64ffda;
            margin-bottom: 1rem;
        }

        .career-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .tag {
            padding: 0.3rem 0.8rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            font-size: 0.75rem;
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .highlighted {
            background: rgba(100, 255, 218, 0.2);
            border-color: rgba(100, 255, 218, 0.3);
            color: #64ffda;
        }

        .premium {
            background: rgba(255, 215, 0, 0.2);
            border-color: rgba(255, 215, 0, 0.3);
            color: #ffd700;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
            margin: 5% auto;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            width: 90%;
            max-width: 600px;
            position: relative;
            backdrop-filter: blur(10px);
            animation: modalAppear 0.3s ease;
        }

        @keyframes modalAppear {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .close {
            position: absolute;
            right: 1.5rem;
            top: 1.5rem;
            font-size: 2rem;
            font-weight: bold;
            color: #888;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .close:hover {
            color: #fff;
        }

        .modal-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .modal-icon {
            font-size: 3rem;
            margin-right: 1rem;
        }

        .modal-title {
            font-size: 2rem;
            font-weight: 600;
            color: #ffffff;
        }

        .modal-category {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: rgba(100, 255, 218, 0.2);
            border: 1px solid rgba(100, 255, 218, 0.3);
            border-radius: 15px;
            font-size: 0.9rem;
            color: #64ffda;
            margin-bottom: 1.5rem;
        }

        .modal-description {
            color: #cccccc;
            margin-bottom: 2rem;
            font-size: 1rem;
            line-height: 1.6;
        }

        .modal-details {
            margin-bottom: 2rem;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .detail-label {
            font-weight: 600;
            color: #64ffda;
        }

        .detail-value {
            color: #ffffff;
        }

        .start-chat-btn {
            width: 100%;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #64ffda, #1de9b6);
            border: none;
            border-radius: 25px;
            color: #000000;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .start-chat-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(100, 255, 218, 0.4);
        }

        .hidden {
            display: none !important;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 2.5rem;
            }
            
            .careers-grid {
                grid-template-columns: 1fr;
            }
            
            .container {
                padding: 1rem;
            }

            .modal-content {
                margin: 2% auto;
                width: 95%;
                padding: 1.5rem;
            }

            .modal-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body class="body_job">
<?php require_once 'nav.php'; ?>

<div class="particles" id="particles"></div>
    <div class="container">
        <div class="header">
            <h1>Portfolio Métiers IA</h1>
            <p class="subtitle">Découvrez une sélection d'experts IA spécialisés dans différents domaines du marketing digital et de l'entrepreneuriat</p>
        </div>

        <div class="search-container">
            <div class="search-box">
                <input type="text" class="search-input" placeholder="Rechercher un métier IA..." id="searchInput">
            </div>
        </div>

        <div class="filter-tabs">
            <div class="filter-tab active" data-category="tous">Tous</div>
            <div class="filter-tab" data-category="marketing">Marketing</div>
            <div class="filter-tab" data-category="contenu">Contenu</div>
            <div class="filter-tab" data-category="technique">Technique</div>
            <div class="filter-tab" data-category="business">Business</div>
            <div class="filter-tab" data-category="media">Média</div>
        </div>

        <div class="careers-grid">

        <div class="career-card" data-category="contenu" data-role="redacteur_editorial" data-model="Claude-3.5" data-specialites="Journalisme, Investigation, Ligne éditoriale">
                <div class="career-icon">📰</div>
                <div class="career-category">Média & Presse</div>
                <h3 class="career-title">Rédacteur Éditorial</h3>
                <p class="career-description">Spécialiste de la rédaction éditoriale pour la presse. Création de contenus journalistiques de qualité et gestion de ligne éditoriale.</p>
                <div class="career-tags">
                    <span class="tag">Journalisme</span>
                    <span class="tag">Éditorial</span>
                    <span class="tag">Presse</span>
                    <span class="tag">Investigation</span>
                </div>
            </div>
            <?php
            $pdo = getDBConnection();
            $stmt = $pdo->query("SELECT * FROM personas");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $logo = $row['logo'];
                $nom = $row['nom'];
                $description = $row['description'];
                $categorie = $row['categorie'];
                $sous_categorie = $row['sous_categorie'];
                $tags = $row['tags'];
                $model = $row['model'];
                $instructions = $row['instructions'];
                $tagsArray = explode(';', $tags);
                $categorie_min = strtolower($categorie);
                $nom_min = strtolower($nom);
                echo    "<div class='career-card' data-category='$categorie_min' data-role='$nom_min' data-model='$model' data-specialites='$tags'>
                <div class='career-icon'>$logo</div>
                <div class='career-category'>$categorie</div>
                <h3 class='career-title'>$nom</h3>
                <p class='career-description'>$description</p>
                <div class='career-tags'>";
                    foreach ($tagsArray as $tag) {
                        echo "<span class='tag'>$tag</span>";
                    }
                echo "</div>
            </div>";
            }
            ?>
            
        </div>
    </div>

    <!-- Modal -->
    <div id="careerModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div class="modal-header">
                <div class="modal-icon" id="modalIcon"></div>
                <div>
                    <h2 class="modal-title" id="modalTitle"></h2>
                    <div class="modal-category" id="modalCategory"></div>
                </div>
            </div>
            <p class="modal-description" id="modalDescription"></p>
            <div class="modal-details">
                <div class="detail-item">
                    <span class="detail-label">Modèle IA utilisé :</span>
                    <span class="detail-value" id="modalModel"></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Spécialités :</span>
                    <span class="detail-value" id="modalSpecialites"></span>
                </div>
            </div>
            <button class="start-chat-btn" id="startChatBtn">Commencer une conversation</button>
        </div>
    </div>

    <script>
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
        let activeCategory = 'tous';

        // Fonction de recherche améliorée
        function searchCareers() {
            const searchTerm = normalizeText(searchInput.value);
            
            careerCards.forEach(card => {
                const title = normalizeText(card.querySelector('.career-title').textContent);
                const description = normalizeText(card.querySelector('.career-description').textContent);
                const category = normalizeText(card.querySelector('.career-category').textContent);
                const tags = Array.from(card.querySelectorAll('.tag')).map(tag => normalizeText(tag.textContent)).join(' ');
                
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
            
            // Mise à jour du contenu du modal
            document.getElementById('modalIcon').textContent = icon;
            document.getElementById('modalCategory').textContent = category;
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalDescription').textContent = description;
            document.getElementById('modalModel').textContent = model;
            document.getElementById('modalSpecialites').textContent = specialites;
            
            currentRole = role;
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

        // Fonction pour démarrer une conversation
        function startConversation() {
            // Ici vous pouvez ajouter la logique pour démarrer une conversation
            // Par exemple, rediriger vers une page de chat ou ouvrir une interface de chat
            
            const roleNames = {
                'redacteur_discours': 'Rédacteur Discours Politique',
                'redacteur_editorial': 'Rédacteur Éditorial',
                'prompt_engineer': 'Prompt Engineer',
                'broadcaster': 'Broadcaster/Journalist',
                'chef_produit': 'Chef de Produit',
                'growth_hacker': 'Growth Hacker',
                'social_media_manager': 'Social Media Manager',
                'etude_marche': 'Chargé d\'Étude de Marché',
                'influence_marketing': 'Influence Marketing',
                'marketing_automation': 'Expert Marketing Automation',
                'email_marketing': 'Expert E-mail Marketing',
                'ui_ux_designer': 'UI/UX Designer',
                'ecommerce_marketing': 'Marketing E-commerce',
                'expert_seo': 'Expert SEO',
                'entrepreneur': 'Serial Entrepreneur',
                'directeur_marketing': 'Directeur Marketing & Commercial',
                'ecrivain': 'Écrivain'
            };

            alert(`Conversation démarrée avec ${roleNames[currentRole]}!\n\nCette fonctionnalité peut être connectée à votre système de chat préféré.`);
            closeModalFunction();
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
        searchCareers = function() {
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
    </script>

    <script src="scripts/nav.js"></script>