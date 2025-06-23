<?php
require_once 'config.php';
requireLogin();

$user = getCurrentUser(); // Une seule déclaration

$success = '';
$error = '';

// Gestion des messages de session si nécessaire
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Récupérer les packages d'abonnement avec gestion d'erreurs
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM paiement WHERE type = 'credit' ORDER BY prix ASC");
    $stmt->execute();
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Erreur lors du chargement des packages.";
    $packages = [];
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/animations.css">
    <link rel="stylesheet" href="css/buy_credits.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <title>Buy credits - OneTapAI</title>
</head>

<body class="body_buy_credits">
    <?php require_once 'nav.php'; ?>

    <!-- Animated background -->
    <div class="animated-bg" id="animatedBg"></div>

    <div class="container">
        <div class="header">
            <h1>Buy credits</h1>
            <p class="subtitle">Boost your creativity with our AI credit packs</p>
        </div>

        <div class="demo-notice">
            <strong>⚡ Demo mode</strong> : Purchases are simulated, credits will be added immediately without real payment.
        </div>

        <div class="current-credits">
            <div class="current-credits-content">
                <p>Your current credits</p>
                <div class="credits-number"><?php echo number_format($user['credits']); ?></div>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="success">✅ <?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error">❌ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="packages">
            <?php foreach ($packages as $index => $package): ?>
                <div class="package">
                    <h3><?php echo htmlspecialchars($package['nom']); ?></h3>
                    <div class="credits"><?php echo number_format($package['nb_credits']); ?> crédits</div>
                    <div class="price"><?php echo number_format($package['prix'], 2); ?>€</div>
                    <button class="btn acheter-btn" onclick="window.location.href='<?php echo $package['url_stripe']; ?><?php echo $user['id']; ?>'">
                        Buy
                    </button>
                </div>
            <?php endforeach; ?>
        </div>

    </div>

    <script>
        // Passer les données utilisateur de manière sécurisée
        window.userData = {
            username: <?= json_encode($user['username']) ?>,
            credits: <?= json_encode($user['credits']) ?>
        };
    </script>

    <script src="scripts/animated-bg.js"></script>
    <script type="text/javascript" src="scripts/nav.js"></script>
</body>

</html>