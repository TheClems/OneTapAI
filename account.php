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
    <title>Mon Compte</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lucide/0.263.1/umd/lucide.js"></script>
    <link rel="stylesheet" href="css/account.css" />

</head>
<body>
<?php require_once 'nav.php'; ?>

    <div class="floating-elements">
        <div class="floating-element"></div>
        <div class="floating-element"></div>
        <div class="floating-element"></div>
    </div>

    <button class="theme-toggle" onclick="toggleTheme()">
        <i data-lucide="sun" id="theme-icon"></i>
    </button>

    <div class="container">
        <div class="header">
            <h1>Mon Compte</h1>
        </div>

        <div class="profile-section">
            <div class="profile-card">
                <div class="avatar">
                    <?php echo htmlspecialchars($user['full_name'][0]); ?>
                </div>
                <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
                <p style="color: #718096; margin-top: 0.5rem;">Membre Premium</p>

            </div>

            <div class="info-cards">
                <div class="info-card">
                    <div class="info-row">
                        <div class="info-label">
                            <i data-lucide="user"></i>
                            Nom d'utilisateur
                        </div>
                        <div class="info-value"><?php echo htmlspecialchars($user['username']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">
                            <i data-lucide="mail"></i>
                            Email
                        </div>
                        <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-row">
                        <div class="info-label">
                            <i data-lucide="coins"></i>
                            Crédits actuels
                        </div>
                        <div class="info-value"><?php echo number_format($user['credits']); ?></div>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-row">
                        <div class="info-label">
                            <i data-lucide="calendar"></i>
                            Date d'inscription
                        </div>
                        <div class="info-value"><?php echo date('d/m/Y à H:i', strtotime($user['date_inscription'])); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">
                            <i data-lucide="clock"></i>
                            Dernière connexion
                        </div>
                        <div class="info-value">14/06/2025 14:32</div>
                    </div>
                </div>
            </div>

        </div>
        <div class="logout-container2">

            <div class="logout-container">
                <button class="logout-btn logout-btn-alt" id="logoutBtnAlt" onclick="window.location.href = 'logout.php';">
                    <svg class="logout-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                        <polyline points="16,17 21,12 16,7" />
                        <line x1="21" y1="12" x2="9" y2="12" />
                    </svg>
                    <span class="logout-text">Se déconnecter</span>
                </button>
            </div>

            <div class="logout-container">
                <button class="logout-btn logout-btn-profile" id="profileBtn" onclick="window.location.href = 'auth.php?mode=edit_profile';">
                    <svg class="logout-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 6 9 17l-5-5" />
                        <circle cx="12" cy="12" r="3" />
                        <path d="M12 1v6m0 6v6" />
                        <path d="m21 12-6 0m-6 0-6 0" />
                    </svg>
                    <span class="logout-text">Modifier profil</span>
                </button>
            </div>

            <div class="logout-container">
                <button class="logout-btn logout-btn-delete" id="deleteBtn" onclick="window.location.href = 'delete_account.php';">
                    <svg class="logout-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3,6 5,6 21,6" />
                        <path d="m19,6v14a2,2 0 0,1 -2,2H7a2,2 0 0,1 -2,-2V6m3,0V4a2,2 0 0,1 2,-2h4a2,2 0 0,1 2,2v2" />
                        <line x1="10" y1="11" x2="10" y2="17" />
                        <line x1="14" y1="11" x2="14" y2="17" />
                    </svg>
                    <span class="logout-text">Supprimer compte</span>
                </button>
            </div>
        </div>
    </div>
</body>

</html>
<script type="text/javascript" src="scripts/account.js"></script>