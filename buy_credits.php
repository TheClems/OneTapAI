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
<!doctype html>
<html lang="en" class="tw-dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Acheter des crédits - AI Credits</title>
    <link rel="stylesheet" href="css/tailwind-build.css">
    <link rel="stylesheet" href="css/index.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <script src="https://www.paypal.com/sdk/js?client-id=ATNKqjfci0KXJor6txjMz8qIWbAmbhXL1JWgKnmGl108_QSR3K_zKzUFHaNsIroR5D7tudYo4X1yZOaV&currency=EUR"></script>
</head>
<body class="tw-flex tw-min-h-[100vh] tw-flex-col tw-bg-[#fcfcfc] tw-text-black dark:tw-bg-black dark:tw-text-white">
    <!-- Header -->
    <header class="lg:tw-px-4 tw-max-w-[100vw] tw-max-w-lg:tw-mr-auto max-lg:tw-top-0 tw-fixed tw-top-4 lg:tw-left-1/2 lg:tw--translate-x-1/2 tw-z-20 tw-flex tw-h-[60px] tw-w-full tw-text-gray-700 tw-bg-white dark:tw-text-gray-200 dark:tw-bg-[#17181b] tw-px-[3%] tw-rounded-md lg:tw-max-w-5xl tw-shadow-md dark:tw-shadow-gray-700 lg:tw-justify-around lg:!tw-backdrop-blur-lg lg:tw-opacity-[0.99]">
        <a class="tw-flex tw-p-[4px] tw-gap-2 tw-place-items-center" href="#">
            <div class="tw-h-[30px] tw-max-w-[100px]">
                <img src="./assets/logo/logo.png" alt="logo" class="tw-object-contain tw-h-full tw-w-full dark:tw-invert" />
            </div>
            <span class="tw-text-base tw-font-medium">AI Credits</span>
        </a>
        <div class="tw-relative tw-flex tw-flex-col tw-place-items-center">
            <div id="nav-dropdown-toggle-0" class="max-lg:tw-max-w-fit tw-flex header-links tw-gap-1 tw-place-items-center">
                <span class="">Features</span>
                <i class="tw-text-sm bi bi-chevron-down"></i>
            </div>
            <nav id="nav-dropdown-list-0" data-open="false" class="tw-scale-0 tw-opacity-0 lg:tw-fixed tw-flex lg:tw-top-[80px] lg:tw-left-1/2 lg:tw--translate-x-1/2 tw-w-[90%] tw-rounded-lg max-lg:tw-h-0 max-lg:tw-w-0 lg:tw-h-[450px] tw-overflow-hidden tw-bg-white dark:tw-bg-[#17181B] tw-duration-300 tw-transition-opacity tw-transition-height tw-shadow-lg tw-p-4">
                <div class="tw-grid max-xl:tw-flex max-xl:tw-flex-col tw-justify-around tw-grid-cols-2 tw-w-full">
                    <a class="header-links tw-flex tw-text-left tw-gap-4 !tw-p-4" href="#">
                        <div class="tw-font-semibold tw-text-3xl">
                            <i class="bi bi-list-columns-reverse"></i>
                        </div>
                        <div class="tw-flex tw-flex-col tw-gap-2">
                            <div class="tw-text-lg tw-text-black dark:tw-text-white tw-font-medium">Prompt library</div>
                            <p>Comes packed with pre-made prompt templates</p>
                        </div> 
                    </a>
                    <a class="header-links tw-flex tw-text-left tw-gap-4 !tw-p-4" href="#">
                        <div class="tw-font-semibold tw-text-3xl">
                            <i class="bi bi-grid-1x2-fill"></i>
                        </div>
                        <div class="tw-flex tw-flex-col tw-gap-2">
                            <div class="tw-text-lg tw-text-black dark:tw-text-white tw-font-medium">Unified Interface</div>
                            <p class="">Test multiple AI models in one interface</p>
                        </div> 
                    </a>
                    <a class="header-links tw-flex tw-text-left tw-gap-4 !tw-p-4" href="#">
                        <div class="tw-font-semibold tw-text-3xl">
                            <i class="bi bi-globe"></i>
                        </div>
                        <div class="tw-flex tw-flex-col tw-gap-2">
                            <div class="tw-text-lg tw-text-black dark:tw-text-white tw-font-medium">Realtime web search</div>
                            <p class="">Search the internet in realtime</p>
                        </div> 
                    </a>
                    <a class="header-links tw-flex tw-text-left tw-gap-4 !tw-p-4" href="#">
                        <div class="tw-font-semibold tw-text-3xl">
                            <i class="bi bi-image-fill"></i>
                        </div>
                        <div class="tw-flex tw-flex-col tw-gap-2">
                            <div class="tw-text-lg tw-text-black dark:tw-text-white tw-font-medium">Image generation</div>
                            <p class="">Generate images from prompts</p>
                        </div> 
                    </a>
                    <a class="header-links tw-flex tw-text-left tw-gap-4 !tw-p-4" href="#">
                        <div class="tw-font-semibold tw-text-3xl">
                            <i class="bi bi-calendar-range"></i>
                        </div>
                        <div class="tw-flex tw-flex-col tw-gap-2">
                            <div class="tw-text-lg tw-text-black dark:tw-text-white tw-font-medium">History</div>
                            <p class="">Continue from where you left off</p>
                        </div> 
                    </a>
                    <a class="header-links tw-flex tw-text-left tw-gap-4 !tw-p-4" href="#">
                        <div class="tw-font-semibold tw-text-3xl">
                            <i class="bi bi-translate"></i>
                        </div>
                        <div class="tw-flex tw-flex-col tw-gap-2">
                            <div class="tw-text-lg tw-text-black dark:tw-text-white tw-font-medium">Multilingual</div>
                            <p class="">Converse in multiple languages</p>
                        </div> 
                    </a>
                </div>           
            </nav>
        </div>
        <div class="lg:tw-mx-4 tw-flex tw-place-items-center tw-gap-[20px] tw-text-base max-md:tw-w-full max-md:tw-flex-col max-md:tw-place-content-center">
            <button type="button" onclick="toggleMode()" class="header-links tw-text-gray-600 dark:tw-text-gray-300" title="toggle-theme" id="theme-toggle">
                <i class="bi bi-sun" id="toggle-mode-icon"></i>
            </button>
            <a href="#" aria-label="Dashboard" class="btn tw-flex tw-gap-3 tw-px-3 tw-py-2 tw-transition-transform tw-duration-[0.3s] hover:tw-translate-x-2">
                <span>Dashboard</span>
                <i class="bi bi-arrow-right"></i>
            </a>
        <div class="current-credits">
            <strong>Vos crédits actuels : <?php echo number_format($user['credits']); ?></strong>
        </div>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="packages">
            <?php foreach ($packages as $i => $package): ?>
                <div class="package">
                    <h3><?php echo htmlspecialchars($package['nom']); ?></h3>
                    <div class="price"><?php echo number_format($package['prix'], 2); ?>€</div>
                    <div class="credits"><?php echo number_format($package['credits_offerts']); ?> crédits</div>

                    <button class="btn acheter-btn" data-id="<?= $i ?>" data-nom="<?= htmlspecialchars($package['nom']) ?>" data-prix="<?= $package['prix'] ?>" data-credits="<?= $package['credits_offerts'] ?>">Acheter avec PayPal</button>
                    <div class="paypal-boutons" id="paypal-boutons-<?= $i ?>"></div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="back-link">
            <a href="dashboard.php">← Retour au tableau de bord</a>
        </div>
    </div>
</body>
</html>
<?php
$pseudo = htmlspecialchars($user['username']); // Supposons que ce soit "alex_du_78"
?>
<script>
    const pseudoPHP = <?= json_encode($user['username']) ?>;

    document.querySelectorAll('.acheter-btn').forEach(function(button) {
        button.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const nom = this.getAttribute('data-nom');
            const prix = this.getAttribute('data-prix');
            const credits = this.getAttribute('data-credits');

            // Empêche le double affichage
            this.disabled = true;

            paypal.Buttons({
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
                        alert("✅ Paiement réussi par " + details.payer.name.given_name + " !");
                        console.log("Détails : ", details);

                        // Optionnel : envoie AJAX pour créditer automatiquement l’utilisateur
                        // fetch('crediter.php', { method: 'POST', body: JSON.stringify({ ... }) })
                    });
                }
            }).render("#paypal-boutons-" + id);
        });
    });
</script>