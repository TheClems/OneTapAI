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
    <style>
        .floating-elements {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .floating-element {
            position: absolute;
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .floating-element:nth-child(1) {
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-element:nth-child(2) {
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }

        .floating-element:nth-child(3) {
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
            }

            50% {
                transform: translateY(-20px) rotate(180deg);
            }
        }
    </style>
    <?php require_once 'nav.php'; ?>
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
</body>

</html>
<script>
    // Effet de parallaxe sur les éléments flottants
    document.addEventListener('mousemove', function(e) {
        const floatingElements = document.querySelectorAll('.floating-element');
        const x = e.clientX / window.innerWidth;
        const y = e.clientY / window.innerHeight;

        floatingElements.forEach((element, index) => {
            const speed = (index + 1) * 0.5;
            const xPos = x * speed * 20;
            const yPos = y * speed * 20;

            element.style.transform = `translate(${xPos}px, ${yPos}px)`;
        });
    });
</script>
