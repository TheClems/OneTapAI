<?php
require 'config.php';
require 'functions.php';

// Vérifier si l'utilisateur est connecté
requireLogin();

// Récupérer les informations de l'utilisateur
$user = getCurrentUser();

// Récupérer l'historique des achats
$pdo = getDBConnection();
$stmt = $pdo->prepare("
    SELECT 
        a.nom as package_nom,
        a.prix as package_prix,
        a.credits_offerts,
        p.date_achat,
        p.montant,
        p.statut
    FROM paiements p
    LEFT JOIN abonnements a ON p.package_id = a.id
    WHERE p.user_id = ?
    ORDER BY p.date_achat DESC
    LIMIT 5
");
$stmt->execute([$user['id']]);
$historique = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Compte - OneTapAI</title>
    <link rel="stylesheet" href="css/tailwind-build.css">
    <link rel="stylesheet" href="css/index.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/nav.css" />
</head>
<body class="tw-flex tw-min-h-[100vh] tw-flex-col">
    <!-- Header -->
    <nav class="sidebar" id="sidebar">
        <div class="floating-particles" id="particles"></div>
        
        <button class="toggle-btn" id="toggleBtn"></button>
        
        <div class="nav-header">
            <div class="logo">OneTapAI</div>
            <button class="theme-toggle" id="themeToggle">
                <svg class="theme-icon" id="themeIcon" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                </svg>
            </button>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="index.php" class="nav-link pulse-effect">
                    <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                    </svg>
                    <span class="nav-text">Accueil</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link pulse-effect">
                    <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                    </svg>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="buy_credits.php" class="nav-link pulse-effect">
                    <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                    </svg>
                    <span class="nav-text">Crédits</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="account.php" class="nav-link active pulse-effect">
                    <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                    </svg>
                    <span class="nav-text">Mon Compte</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <main class="tw-flex tw-flex-col tw-flex-1 tw-p-4 tw-max-w-7xl tw-mx-auto tw-mt-20">
        <div class="tw-flex tw-flex-col tw-gap-8">
            <div class="tw-flex tw-flex-col tw-gap-6">
                <div class="tw-flex tw-justify-between tw-items-center">
                    <h1 class="tw-text-4xl tw-font-bold">Mon Compte</h1>
                    <a href="dashboard.php" class="tw-text-blue-600 dark:tw-text-blue-400 hover:tw-underline">
                        <span class="tw-flex tw-items-center tw-gap-2">
                            <i class="bi bi-arrow-left"></i>
                            Retour au dashboard
                        </span>
                    </a>
                </div>

                <!-- Informations de base -->
                <div class="tw-bg-white dark:tw-bg-[#17181B] tw-rounded-xl tw-p-6 tw-shadow-md dark:tw-shadow-gray-700">
                    <div class="tw-flex tw-flex-col md:tw-flex-row tw-gap-6">
                        <div class="tw-flex tw-flex-col tw-flex-1 tw-gap-4">
                            <div class="tw-flex tw-flex-col">
                                <span class="tw-text-sm tw-text-gray-500 dark:tw-text-gray-400">Nom d'utilisateur</span>
                                <span class="tw-text-xl tw-font-medium"><?php echo htmlspecialchars($user['username']); ?></span>
                            </div>
                            <div class="tw-flex tw-flex-col">
                                <span class="tw-text-sm tw-text-gray-500 dark:tw-text-gray-400">Email</span>
                                <span class="tw-text-xl tw-font-medium"><?php echo htmlspecialchars($user['email']); ?></span>
                            </div>
                            <div class="tw-flex tw-flex-col">
                                <span class="tw-text-sm tw-text-gray-500 dark:tw-text-gray-400">Crédits actuels</span>
                                <span class="tw-text-xl tw-font-medium"><?php echo number_format($user['credits']); ?></span>
                            </div>
                        </div>
                        <div class="tw-flex tw-flex-col tw-flex-1 tw-gap-4">
                            <div class="tw-flex tw-flex-col">
                                <span class="tw-text-sm tw-text-gray-500 dark:tw-text-gray-400">Date d'inscription</span>
                                <span class="tw-text-xl tw-font-medium"><?php echo date('d/m/Y', strtotime($user['date_inscription'])); ?></span>
                            </div>
                            <div class="tw-flex tw-flex-col">
                                <span class="tw-text-sm tw-text-gray-500 dark:tw-text-gray-400">Dernière connexion</span>
                                <span class="tw-text-xl tw-font-medium"><?php echo date('d/m/Y H:i', strtotime($user['last_login'])); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Historique des achats -->
                <div class="tw-bg-white dark:tw-bg-[#17181B] tw-rounded-xl tw-p-6 tw-shadow-md dark:tw-shadow-gray-700">
                    <h2 class="tw-text-2xl tw-font-bold mb-4">Historique des achats</h2>
                    <div class="tw-overflow-x-auto">
                        <table class="tw-w-full tw-min-w-full">
                            <thead>
                                <tr class="tw-border-b tw-border-gray-200 dark:tw-border-gray-700">
                                    <th class="tw-py-3 tw-text-left tw-text-sm tw-font-medium tw-text-gray-500 dark:tw-text-gray-400">Package</th>
                                    <th class="tw-py-3 tw-text-left tw-text-sm tw-font-medium tw-text-gray-500 dark:tw-text-gray-400">Prix</th>
                                    <th class="tw-py-3 tw-text-left tw-text-sm tw-font-medium tw-text-gray-500 dark:tw-text-gray-400">Crédits</th>
                                    <th class="tw-py-3 tw-text-left tw-text-sm tw-font-medium tw-text-gray-500 dark:tw-text-gray-400">Date</th>
                                    <th class="tw-py-3 tw-text-left tw-text-sm tw-font-medium tw-text-gray-500 dark:tw-text-gray-400">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historique as $achat): ?>
                                    <tr class="tw-border-b tw-border-gray-200 dark:tw-border-gray-700">
                                        <td class="tw-py-4 tw-text-sm tw-font-medium tw-text-gray-900 dark:tw-text-gray-100"><?php echo htmlspecialchars($achat['package_nom']); ?></td>
                                        <td class="tw-py-4 tw-text-sm tw-text-gray-500 dark:tw-text-gray-400"><?php echo number_format($achat['package_prix'], 2); ?>€</td>
                                        <td class="tw-py-4 tw-text-sm tw-text-gray-500 dark:tw-text-gray-400"><?php echo number_format($achat['credits_offerts']); ?></td>
                                        <td class="tw-py-4 tw-text-sm tw-text-gray-500 dark:tw-text-gray-400"><?php echo date('d/m/Y H:i', strtotime($achat['date_achat'])); ?></td>
                                        <td class="tw-py-4 tw-text-sm">
                                            <span class="tw-px-2 tw-py-1 tw-rounded-full <?php 
                                                echo $achat['statut'] == 'completed' ? 'tw-bg-green-100 dark:tw-bg-green-900/20 tw-text-green-800 dark:tw-text-green-200' : 
                                                      'tw-bg-gray-100 dark:tw-bg-gray-900/20 tw-text-gray-800 dark:tw-text-gray-200';
                                            ?>">
                                                <?php echo ucfirst($achat['statut']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Options de compte -->
                <div class="tw-bg-white dark:tw-bg-[#17181B] tw-rounded-xl tw-p-6 tw-shadow-md dark:tw-shadow-gray-700">
                    <h2 class="tw-text-2xl tw-font-bold mb-4">Options</h2>
                    <div class="tw-flex tw-flex-col tw-gap-4">
                        <a href="#" class="tw-flex tw-items-center tw-gap-3 tw-p-3 tw-rounded-lg tw-transition-colors tw-duration-200 hover:tw-bg-blue-50 dark:hover:tw-bg-blue-900/20">
                            <i class="bi bi-pencil-square tw-text-blue-600 dark:tw-text-blue-400"></i>
                            <span class="tw-flex tw-flex-col">
                                <span class="tw-font-medium">Modifier le profil</span>
                                <span class="tw-text-sm tw-text-gray-500 dark:tw-text-gray-400">Modifier vos informations personnelles</span>
                            </span>
                        </a>
                        <a href="#" class="tw-flex tw-items-center tw-gap-3 tw-p-3 tw-rounded-lg tw-transition-colors tw-duration-200 hover:tw-bg-blue-50 dark:hover:tw-bg-blue-900/20">
                            <i class="bi bi-credit-card-2-front tw-text-blue-600 dark:tw-text-blue-400"></i>
                            <span class="tw-flex tw-flex-col">
                                <span class="tw-font-medium">Acheter des crédits</span>
                                <span class="tw-text-sm tw-text-gray-500 dark:tw-text-gray-400">Recharger votre compte en crédits</span>
                            </span>
                        </a>
                        <a href="#" class="tw-flex tw-items-center tw-gap-3 tw-p-3 tw-rounded-lg tw-transition-colors tw-duration-200 hover:tw-bg-blue-50 dark:hover:tw-bg-blue-900/20">
                            <i class="bi bi-shield-lock tw-text-blue-600 dark:tw-text-blue-400"></i>
                            <span class="tw-flex tw-flex-col">
                                <span class="tw-font-medium">Sécurité</span>
                                <span class="tw-text-sm tw-text-gray-500 dark:tw-text-gray-400">Gérer vos préférences de sécurité</span>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script type="text/javascript" src="assets/js/nav.js"></script>
</body>
</html>
