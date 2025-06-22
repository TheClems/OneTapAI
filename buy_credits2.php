<?php
require_once 'config.php';
requireLogin();

$user = getCurrentUser();

$success = '';
$error = '';

if ($_SESSION['user_id']) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $abonnement_id = $user['abonnement_id'];
    if ($abonnement_id == null) {
        $container_visibility_no_abonnement = "none";
        $container_visibility_abonnement = "block";
        $has_subscription = true;
    } else {
        $container_visibility_no_abonnement = "block";
        $container_visibility_abonnement = "none";
        $has_subscription = false;
    }
}



$user = getCurrentUser();

// Récupérer les packages d'abonnement
$pdo = getDBConnection();
$stmt = $pdo->query("SELECT * FROM paiement WHERE type = 'credit' ORDER BY prix ASC");
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/buy_credits.css" />
    <link rel="stylesheet" href="css/animations.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <title>Buy credits - OneTapAI</title>
</head>

<body class="body_buy_credits">
    <?php require_once 'nav.php'; ?>

    <!-- Animated background -->
    <div class="animated-bg" id="animatedBg"></div>

    <!-- Container pour achat individuel (sans abonnement) -->
    <div class="container_no_abonnement" style="display: <?php echo $container_visibility_no_abonnement; ?>;">
        <div class="header">
            <h1>Add Credits</h1>
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
            <?php foreach ($packages as $i => $package): ?>
                <div class="package">
                    <h3><?php echo htmlspecialchars($package['nom']) ?></h3>
                    <div class="credits"><?php echo number_format($package['nb_credits']); ?> crédits</div>
                    <div class="price"><?php echo number_format($package['prix'], 2); ?>€</div>
                    <button class="btn acheter-btn-no-abonnement" onclick="window.location.href='<?php echo $package['url_stripe']; ?>'">
                        Buy
                    </button>
                </div>
                
            <?php endforeach; ?>
        </div>

        <!-- Zone pour le bouton PayPal des achats individuels -->
        <div class="paypal-render-area" id="paypal-render-area-individual"></div>
    </div>

    <!-- Container pour abonnements -->
    <div class="container_abonnement" style="display: <?php echo $container_visibility_abonnement; ?>;">
        <div class="header">
            <h1>Buy credits subscription</h1>
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
            <?php foreach ($packages as $i => $package): ?>
                <div class="package <?= $i === 1 ? 'featured' : '' ?>">
                    <h3><?= htmlspecialchars($package['nom']) ?></h3>
                    <div class="credits"><?= number_format($package['credits_offerts']) ?> crédits/mois</div>
                    <div class="price"><?= number_format($package['prix'], 2) ?>€</div>
                    <button class="btn acheter-btn-abonnement">
                        Buy
                    </button>
                </div>
            <?php endforeach; ?>
        </div>


    </div>

    <script>
        const pseudoPHP = <?= json_encode($user['username']) ?>;

       
    </script>
    <script type="text/javascript" src="scripts/nav.js"></script>
    <script src="scripts/animated-bg.js"></script>
</body>
</html>