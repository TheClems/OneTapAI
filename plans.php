<?php
require_once 'config.php';
requireLogin();

$user = getCurrentUser();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account - OneTapAI</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lucide/0.263.1/umd/lucide.js"></script>
    <link rel="stylesheet" href="css/tailwind-build.css">
    <link rel="stylesheet" href="css/plans.css">
    <link rel="stylesheet" href="css/animations.css">
</head>
<body class="body_plans">
    <?php require_once 'nav.php'; ?>

    <div class="animated-bg" id="animatedBg"></div>

    <div class="main-content">
        
        <h1>Plans</h1>
        <p class="subtitle">Choose the right plan for you</p>

        <div class="container">
        <div class="pricing-header">
            <h1>Choisissez votre plan AI</h1>
            <p>Débloquez le potentiel de l'intelligence artificielle</p>
        </div>

        <div id="loading" class="loading" style="display: none;">
            <i class="bi bi-arrow-clockwise" style="animation: spin 1s linear infinite;"></i>
            Chargement de votre plan actuel...
        </div>

        <div id="error" class="error" style="display: none;">
            Impossible de charger les informations du plan actuel.
        </div>

        <div class="cards-container" id="cards-container">
            <!-- Card 1 - Essential -->
            <div class="card" data-plan="essential">
                <h3>
                    <span>9€</span>
                    <span>/mois</span>
                </h3>
                <p>Outils IA essentiels pour un usage quotidien</p>
                <hr />
                <ul>
                    <li><i class="bi bi-check-circle-fill"></i><span>1 000 messages de chat IA</span></li>
                    <li><i class="bi bi-check-circle-fill"></i><span>30 générations d'images premium</span></li>
                    <li><i class="bi bi-check-circle-fill"></i><span>10 générations de musique premium</span></li>
                    <li class="disabled"><i class="bi bi-check-circle-fill"></i><span>Accès à tous les modèles IA premium</span></li>
                    <li class="disabled"><i class="bi bi-check-circle-fill"></i><span>Accès anticipé aux nouvelles fonctionnalités</span></li>
                </ul>
                <a href="#" class="btn">Choisir ce plan</a>
            </div>

            <!-- Card 2 - Pro -->
            <div class="card highlight" data-plan="pro">
                <h3>
                    <span>17€</span>
                    <span>/mois</span>
                </h3>
                <p>Fonctionnalités avancées pour les passionnés d'IA</p>
                <hr />
                <ul>
                    <li><i class="bi bi-check-circle-fill"></i><span>5 000 messages de chat IA</span></li>
                    <li><i class="bi bi-check-circle-fill"></i><span>100 générations d'images premium</span></li>
                    <li><i class="bi bi-check-circle-fill"></i><span>40 générations de musique premium</span></li>
                    <li><i class="bi bi-check-circle-fill"></i><span>Accès à tous les modèles IA premium</span></li>
                    <li class="disabled"><i class="bi bi-check-circle-fill"></i><span>Accès anticipé aux nouvelles fonctionnalités</span></li>
                </ul>
                <a href="#" class="btn">Choisir ce plan</a>
            </div>

            <!-- Card 3 - Ultimate -->
            <div class="card" data-plan="ultimate">
                <h3>
                    <span>29€</span>
                    <span>/mois</span>
                </h3>
                <p>Potentiel illimité pour les utilisateurs experts</p>
                <hr />
                <ul>
                    <li><i class="bi bi-check-circle-fill"></i><span>10 000 messages de chat IA</span></li>
                    <li><i class="bi bi-check-circle-fill"></i><span>300 générations d'images premium</span></li>
                    <li><i class="bi bi-check-circle-fill"></i><span>100 générations de musique premium</span></li>
                    <li><i class="bi bi-check-circle-fill"></i><span>Accès à tous les modèles IA premium</span></li>
                    <li><i class="bi bi-check-circle-fill"></i><span>Accès anticipé aux nouvelles fonctionnalités</span></li>
                </ul>
                <a href="#" class="btn">Choisir ce plan</a>
            </div>
        </div>
    </div>

    </div>
    <script type="text/javascript" src="scripts/nav.js"></script>
    <script type="text/javascript" src="scripts/animated-bg.js"></script>
</body>
</html>