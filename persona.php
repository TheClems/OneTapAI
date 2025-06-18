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
        <link rel="stylesheet" href="css/persona.css">
    </head>

    <body class="body_job">
        <?php require_once 'nav.php'; ?>

        <!-- Animated background -->
        <div class="animated-bg" id="animatedBg"></div>

        <div class="floating-elements">
            <div class="floating-element"></div>
            <div class="floating-element"></div>
            <div class="floating-element"></div>
        </div>

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
                    $id = $row['id'];
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
                    echo    "<div class='career-card' data-category='$categorie_min' data-role='$nom_min' data-model='$model' data-specialites='$tags' data-id='$id'>
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

        <script src="scripts/persona.js"></script>
        <script src="scripts/nav.js"></script>
        <script src="scripts/animated-bg.js"></script>

    </body>

</html>