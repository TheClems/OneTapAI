<?php
require_once 'config.php';
requireLogin();

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AI Credits</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

</head>

<body>
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
            <div class="user-info">
                <h1 class="welcome">Bienvenue, <?php echo htmlspecialchars($user['username']); ?></h1>
            </div>
        </div>

        <div class="credits-box">
            <h2>Vos crédits</h2>
            <div class="credits-number"><?php echo number_format($user['credits']); ?></div>
            <span class="credits-text">crédits disponibles</span>
        </div>

        <div class="actions">
            <a href="buy_credits.php" class="btn btn-success">Acheter des crédits</a>

            <a href="new_chat.php" class="btn btn-primary">Créer un nouveau chat</a>
        </div>

        <div class="section account-info">
            <h3>Informations du compte</h3>
            <div class="info-group">
                <div class="info-item">
                    <span class="label">Nom d'utilisateur :</span>
                    <span class="value"><?php echo htmlspecialchars($user['username']); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Email :</span>
                    <span class="value"><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Date d'inscription :</span>
                    <span class="value"><?php echo date('d/m/Y à H:i', strtotime($user['date_inscription'])); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Crédits :</span>
                    <span class="value"><?php echo number_format($user['credits']); ?></span>
                </div>
            </div>
        </div>

        <div class="section features">
            <h3>Fonctionnalités disponibles</h3>
            <ul>
                <li>Chat avec des IA avancées</li>
                <li>Historique de vos conversations</li>
                <li>Gestion flexible de vos crédits</li>
                <li>Support technique</li>
            </ul>
        </div>
    </div>
    <script type="text/javascript" src="scripts/nav.js"></script>
    <script type="text/javascript" src="scripts/floating-element.js"></script>
</body>

</html>