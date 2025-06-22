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
    } else {
        $container_visibility_no_abonnement = "block";
        $container_visibility_abonnement = "none";
    }
}

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
    <!-- ONLY ONE PayPal SDK script - load it without intent to handle both cases -->
    <script src="https://www.paypal.com/sdk/js?client-id=ATNKqjfci0KXJor6txjMz8qIWbAmbhXL1JWgKnmGl108_QSR3K_zKzUFHaNsIroR5D7tudYo4X1yZOaV&currency=EUR&vault=true"></script>
    <link rel="stylesheet" href="css/buy_credits.css" />
    <link rel="stylesheet" href="css/animations.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <title>Buy credits - OneTapAI</title>
</head>

<body class="body_buy_credits">
    <?php require_once 'nav.php'; ?>

    <!-- Animated background -->
    <div class="animated-bg" id="animatedBg"></div>

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
                    <div class="credits"><?php echo number_format($package['credits_offerts']); ?> crédits</div>
                    <div class="price"><?php echo number_format($package['prix'], 2); ?>€</div>
                    <button class="btn acheter-btn-no-abonnement" data-id="<?= $i ?>" data-nom="<?= htmlspecialchars($package['nom']) ?>" data-prix="<?= $package['prix'] ?>" data-credits="<?= $package['credits_offerts'] ?>">
                        Buy
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

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

        <?php
        $pdo = getDBConnection();
        $abonnements_stmt = $pdo->query("SELECT * FROM abonnements");
        $subscription_packages = $abonnements_stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <div class="packages">
            <?php foreach ($subscription_packages as $i => $package): ?>
                <div class="package <?= $i === 1 ? 'featured' : '' ?>">
                    <h3><?= htmlspecialchars($package['nom']) ?></h3>
                    <div class="credits"><?= number_format($package['credits_offerts']) ?> crédits/mois</div>
                    <div class="price"><?= number_format($package['prix'], 2) ?>€</div>
                    <button
                        class="btn acheter-btn-abonnement"
                        data-id="<?= htmlspecialchars($package['id']) ?>"
                        data-nom="<?= htmlspecialchars($package['nom']) ?>"
                        data-prix="<?= htmlspecialchars($package['prix']) ?>"
                        data-credits="<?= htmlspecialchars($package['credits_offerts']) ?>"
                        data-plan="<?= htmlspecialchars($package['abonement_id_paypal']) ?>">
                        Buy
                    </button>
                </div>
            <?php endforeach; ?>
        </div>

        <h3 id="selected-package-name" style="text-align: center; margin-top: 20px;"></h3>
        <div style="display: flex; justify-content: center; margin-top: 20px;">
            <div id="paypal-button-container-subscription"></div>
        </div>
    </div>

    <?php $pseudo = htmlspecialchars($user['username']); ?>

    <!-- Zone pour rendre le bouton PayPal en dehors des cartes -->
    <div class="paypal-render-area" id="paypal-render-area"></div>

    <script>
        const pseudoPHP = <?= json_encode($user['username']) ?>;
        
        // SUBSCRIPTION BUTTONS (for recurring payments)
        let selectedPlanId = null;

        document.querySelectorAll('.acheter-btn-abonnement').forEach(button => {
            button.addEventListener('click', function() {
                const planId = this.dataset.plan;
                const nom = this.dataset.nom;
                const prix = this.dataset.prix;

                selectedPlanId = planId;

                document.getElementById('selected-package-name').innerText = `Abonnement sélectionné : ${nom} (${prix} € / mois)`;

                // Clear previous button
                const container = document.getElementById('paypal-button-container-subscription');
                container.innerHTML = '';

                // Render subscription button with createSubscription
                paypal.Buttons({
                    style: {
                        shape: 'rect',
                        color: 'blue',
                        layout: 'vertical',
                        label: 'subscribe',
                        center: true
                    },
                    createSubscription: function(data, actions) {
                        return actions.subscription.create({
                            plan_id: selectedPlanId
                        });
                    },
                    onApprove: function(data, actions) {
                        alert("Abonnement validé : " + data.subscriptionID);
                        // Handle subscription approval here
                        console.log('Subscription ID:', data.subscriptionID);
                    },
                    onError: function(err) {
                        console.error("Erreur d'abonnement:", err);
                        alert("Une erreur est survenue avec l'abonnement PayPal.");
                    }
                }).render('#paypal-button-container-subscription');
            });
        });

        // ONE-TIME PAYMENT BUTTONS (for credit purchases)
        document.querySelectorAll('.acheter-btn-no-abonnement').forEach(function(button) {
            button.addEventListener('click', function() {
                const nom = this.getAttribute('data-nom');
                const prix = this.getAttribute('data-prix');
                const credits = this.getAttribute('data-credits');

                // Disable clicked button and enable others
                document.querySelectorAll('.acheter-btn-no-abonnement').forEach(btn => btn.disabled = false);
                this.disabled = true;

                // Find or create PayPal container
                let renderArea = document.getElementById('paypal-render-area');
                if (!renderArea) {
                    renderArea = document.createElement('div');
                    renderArea.id = 'paypal-render-area';
                    document.body.appendChild(renderArea);
                }

                renderArea.innerHTML = '<div id="paypal-button-container-onetime"></div>';

                // Render one-time payment button with createOrder
                paypal.Buttons({
                    createOrder: function(data, actions) {
                        return actions.order.create({
                            purchase_units: [{
                                description: nom + " - " + credits + " crédits",
                                custom_id: pseudoPHP + "-" + nom,
                                invoice_id: "FACTURE-" + pseudoPHP + "-" + nom + "-" + Date.now(),
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
                    onApprove: function(data, actions) {
                        return actions.order.capture().then(function(details) {
                            alert("✅ Paiement réussi par " + details.payer.name.given_name + " !");
                            console.log("Détails : ", details);

                            // Call PHP to add credits
                            fetch('payment_verified.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        credits: credits
                                    })
                                })
                                .then(response => response.json())
                                .then(result => {
                                    if (result.status === 'success') {
                                        alert("🎉 Vos crédits ont été ajoutés avec succès !");
                                        window.location.reload();
                                    } else {
                                        alert("❌ Une erreur est survenue : " + result.message);
                                    }
                                })
                                .catch(error => {
                                    console.error("Erreur lors de l'ajout des crédits :", error);
                                    alert("❌ Erreur lors de l'envoi des crédits.");
                                });
                        });
                    },
                    onError: function(err) {
                        console.error("Erreur PayPal:", err);
                        alert("Une erreur est survenue avec PayPal.");
                    }
                }).render('#paypal-button-container-onetime');

                // Scroll to PayPal button
                renderArea.scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
    <script type="text/javascript" src="scripts/nav.js"></script>
    <script src="scripts/animated-bg.js"></script>
</body>
</html>