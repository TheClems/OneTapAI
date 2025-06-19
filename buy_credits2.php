<?php
require_once 'config.php';
requireLogin();

$user = getCurrentUser();

$success = '';
$error = '';

// Traiter l'achat fictif (mode démo)
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

// Traiter le paiement PayPal réussi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['paypal_success'])) {
    $credits_to_add = intval($_POST['credits'] ?? 0);
    
    if ($credits_to_add > 0) {
        $pdo = getDBConnection();
        
        try {
            // Ajouter les crédits à l'utilisateur
            $stmt = $pdo->prepare("UPDATE users SET credits = credits + ? WHERE id = ?");
            if ($stmt->execute([$credits_to_add, $_SESSION['user_id']])) {
                // Réponse JSON pour le JavaScript
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => "Paiement réussi ! " . number_format($credits_to_add) . " crédits ajoutés à votre compte.",
                    'new_credits' => getCurrentUser()['credits']
                ]);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => "Erreur lors de l'ajout des crédits."
                ]);
                exit;
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => "Erreur lors de l'ajout des crédits : " . $e->getMessage()
            ]);
            exit;
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => "Données de transaction invalides."
        ]);
        exit;
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
                <div class="credits-number" id="current-credits-display"><?php echo number_format($user['credits']); ?></div>
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
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php $pseudo = htmlspecialchars($user['username']); ?>

    <!-- Zone pour rendre le bouton PayPal en dehors des cartes -->
    <div class="paypal-render-area" id="paypal-render-area"></div>

    <script>
    const pseudoPHP = <?= json_encode($user['username']) ?>;
    
    // Fonction pour envoyer les données de paiement au serveur
    function addCreditsToDatabase(credits) {
        const formData = new FormData();
        formData.append('paypal_success', '1');
        formData.append('credits', credits);
        
        return fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json());
    }
    
    document.querySelectorAll('.acheter-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            const nom = this.getAttribute('data-nom');
            const prix = parseFloat(this.getAttribute('data-prix'));
            const credits = parseInt(this.getAttribute('data-credits'));

            // Réactive tous les boutons et désactive celui cliqué
            document.querySelectorAll('.acheter-btn').forEach(btn => {
                btn.disabled = false;
                btn.textContent = 'Buy';
            });
            this.disabled = true;
            this.textContent = 'Processing...';

            // Trouver ou créer le conteneur pour PayPal
            let renderArea = document.getElementById('paypal-render-area');
            if (!renderArea) {
                renderArea = document.createElement('div');
                renderArea.id = 'paypal-render-area';
                document.body.appendChild(renderArea);
            }

            renderArea.innerHTML = '<div id="paypal-button-container"></div>';

            paypal.Buttons({
                createOrder: function (data, actions) {
                    return actions.order.create({
                        purchase_units: [{
                            description: nom + " - " + credits + " crédits",
                            custom_id: pseudoPHP + "-" + nom,
                            invoice_id: "FACTURE-" + pseudoPHP + "-" + nom + "-" + Date.now(),
                            amount: {
                                value: prix.toFixed(2),
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
                        console.log("Paiement capturé:", details);
                        
                        // Envoyer les données au serveur pour ajouter les crédits
                        addCreditsToDatabase(credits)
                            .then(response => {
                                if (response.success) {
                                    // Afficher le message de succès
                                    alert("✅ " + response.message);
                                    
                                    // Mettre à jour l'affichage des crédits
                                    document.getElementById('current-credits-display').textContent = 
                                        new Intl.NumberFormat().format(response.new_credits);
                                    
                                    // Réactiver tous les boutons
                                    document.querySelectorAll('.acheter-btn').forEach(btn => {
                                        btn.disabled = false;
                                        btn.textContent = 'Buy';
                                    });
                                    
                                    // Masquer la zone PayPal
                                    renderArea.innerHTML = '';
                                    
                                } else {
                                    alert("❌ " + response.message);
                                    console.error("Erreur serveur:", response);
                                }
                            })
                            .catch(error => {
                                console.error("Erreur lors de l'ajout des crédits:", error);
                                alert("❌ Erreur lors de l'ajout des crédits. Veuillez contacter le support.");
                            });
                    });
                },
                onError: function(err) {
                    console.error("Erreur PayPal:", err);
                    alert("❌ Une erreur est survenue avec PayPal.");
                    
                    // Réactiver le bouton en cas d'erreur
                    document.querySelectorAll('.acheter-btn').forEach(btn => {
                        btn.disabled = false;
                        btn.textContent = 'Buy';
                    });
                },
                onCancel: function(data) {
                    console.log("Paiement annulé:", data);
                    
                    // Réactiver le bouton si l'utilisateur annule
                    document.querySelectorAll('.acheter-btn').forEach(btn => {
                        btn.disabled = false;
                        btn.textContent = 'Buy';
                    });
                    
                    // Masquer la zone PayPal
                    renderArea.innerHTML = '';
                }
            }).render('#paypal-button-container');

            // Optionnel : scroll vers le bouton PayPal
            renderArea.scrollIntoView({ behavior: 'smooth' });
        });
    });
    </script>
    <script type="text/javascript" src="scripts/nav.js"></script>
    <script src="scripts/animated-bg.js"></script>
</body>

</html>