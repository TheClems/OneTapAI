<?php
require_once 'config.php';
requireLogin();

$user = getCurrentUser();

$success = '';
$error = '';

// Traiter l'achat fictif
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['package'])) {
    $package = $_POST['package'];
    $credits_to_add = 0;

    switch ($package) {
        case 'starter':
            $credits_to_add = 1000;
            break;
        case 'pro':
            $credits_to_add = 2500;
            break;
        case 'premium':
            $credits_to_add = 5500;
            break;
    }

    if ($credits_to_add > 0) {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("UPDATE users SET credits = credits + ? WHERE id = ?");
        if ($stmt->execute([$credits_to_add, $_SESSION['user_id']])) {
            $success = "Félicitations ! Vous avez acheté " . number_format($credits_to_add) . " crédits.";
        } else {
            $error = "Erreur lors de l'achat des crédits.";
        }
    }
}

$user = getCurrentUser();

// Récupérer les packages d'abonnement
$pdo = getDBConnection();
$stmt = $pdo->query("SELECT * FROM abonnements ORDER BY prix ASC");
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://www.paypal.com/sdk/js?client-id=ATNKqjfci0KXJor6txjMz8qIWbAmbhXL1JWgKnmGl108_QSR3K_zKzUFHaNsIroR5D7tudYo4X1yZOaV&currency=EUR"></script>
    <link rel="stylesheet" href="css/buy_credits.css" />
    <link rel="stylesheet" href="css/animations.css">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <title>Buy credits - OneTapAI</title>

</head>


<style>
/* Styles pour les conteneurs PayPal */
.paypal-boutons {
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
    margin-top: 15px;
    padding: 10px;
    min-height: 50px;
}

/* Style pour chaque bouton PayPal individuellement */
[id^="paypal-boutons-"] {
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
    margin-top: 15px;
    padding: 10px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

/* Effet hover sur le conteneur */
[id^="paypal-boutons-"]:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
}

/* Style pour les iframes PayPal */
[id^="paypal-boutons-"] iframe {
    border-radius: 8px !important;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1) !important;
    transition: all 0.3s ease !important;
}

/* Effet hover sur les iframes */
[id^="paypal-boutons-"] iframe:hover {
    transform: scale(1.02) !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
}

/* Style alternatif avec bordure */
.paypal-boutons-wrapper {
    border: 2px solid #0070ba;
    border-radius: 12px;
    padding: 8px;
    background: white;
    margin-top: 15px;
}

/* Animation de chargement */
.paypal-boutons:empty::before {
    content: "Chargement PayPal...";
    display: flex;
    justify-content: center;
    align-items: center;
    height: 50px;
    color: #666;
    font-style: italic;
}
</style>
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
            <?php foreach ($packages as $i => $package): ?>
                <div class="package <?php echo $i === 1 ? 'featured' : ''; ?>">
                    <h3><?php echo htmlspecialchars($package['nom']); ?></h3>
                    <div class="credits"><?php echo number_format($package['credits_offerts']); ?> crédits</div>
                    <div class="price"><?php echo number_format($package['prix'], 2); ?>€</div>
                    <button class="btn acheter-btn" data-id="<?= $i ?>" data-nom="<?= htmlspecialchars($package['nom']) ?>" data-prix="<?= $package['prix'] ?>" data-credits="<?= $package['credits_offerts'] ?>">
                        Buy
                    </button>
                    <div class="paypal-boutons" id="paypal-boutons-<?= $i ?>"></div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>

    <?php $pseudo = htmlspecialchars($user['username']); ?>

    <script src="scripts/animated-bg.js"></script>
    <script>
// PayPal integration améliorée
const pseudoPHP = <?= json_encode($user['username']) ?>;

document.querySelectorAll('.acheter-btn').forEach(function (button) {
    button.addEventListener('click', function () {
        const id = this.getAttribute('data-id');
        const nom = this.getAttribute('data-nom');
        const prix = this.getAttribute('data-prix');
        const credits = this.getAttribute('data-credits');

        // Désactiver le bouton
        this.disabled = true;
        this.textContent = 'Chargement...';

        // Créer le bouton PayPal avec styles personnalisés
        paypal.Buttons({
            style: {
                layout: 'horizontal',
                color: 'blue',
                shape: 'pill',
                label: 'pay',
                height: 45,
                tagline: false
            },
            createOrder: function (data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        description: nom + " - " + credits + " crédits",
                        custom_id: pseudoPHP + "-" + nom,
                        invoice_id: "FACTURE-" + pseudoPHP + "-" + nom,
                        amount: {
                            value: prix,
                            currency_code: 'EUR'
                        }
                    }],
                    application_context: {
                        shipping_preference: "NO_SHIPPING"
                    }
                });
            },
            onApprove: function (data, actions) {
                return actions.order.capture().then(function (details) {
                    // Afficher une notification de succès stylée
                    showSuccessNotification("✅ Paiement réussi par " + details.payer.name.given_name + " !");
                    console.log("Détails : ", details);
                    
                    // Recharger la page après 2 secondes
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                });
            },
            onError: function (err) {
                console.error('Erreur PayPal:', err);
                button.disabled = false;
                button.textContent = 'Buy';
                alert('Erreur lors du paiement. Veuillez réessayer.');
            },
            onCancel: function (data) {
                console.log('Paiement annulé');
                button.disabled = false;
                button.textContent = 'Buy';
            }
        }).render("#paypal-boutons-" + id);

        // Cacher le bouton d'achat une fois PayPal chargé
        setTimeout(() => {
            this.style.display = 'none';
        }, 1000);
    });
});

// Fonction pour afficher une notification de succès
function showSuccessNotification(message) {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
        padding: 15px 20px;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 9999;
        font-weight: 500;
        animation: slideIn 0.3s ease;
    `;
    notification.textContent = message;
    document.body.appendChild(notification);

    // Supprimer la notification après 4 secondes
    setTimeout(() => {
        notification.remove();
    }, 4000);
}
    </script>
    <script type="text/javascript" src="scripts/nav.js"></script>
</body>
