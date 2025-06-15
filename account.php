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
    <link rel="stylesheet" href="css/nav.css" />
    <link rel="stylesheet" href="css/account.css" />

</head>
<body>

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
                <a href="#" class="nav-link active pulse-effect">
                    <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                    <span class="nav-text">Accueil</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link pulse-effect">
                    <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="nav-text">Projets</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link pulse-effect">
                    <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                    </svg>
                    <span class="nav-text">Équipe</span>
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
                    <span class="nav-text">Acheter des crédits</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="account.php" class="nav-link pulse-effect">
                    <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                    </svg>
                    <span class="nav-text">Mon Compte</span>
                </a>
            </li>

        </ul>
    </nav>
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
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16,17 21,12 16,7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                        <span class="logout-text">Se déconnecter</span>
                    </button>
                </div>
    
                <div class="logout-container">
                    <button class="logout-btn logout-btn-profile" id="profileBtn" onclick="window.location.href = 'auth.php?mode=edit_profile';">
                        <svg class="logout-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 6 9 17l-5-5"/>
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M12 1v6m0 6v6"/>
                            <path d="m21 12-6 0m-6 0-6 0"/>
                        </svg>
                        <span class="logout-text">Modifier profil</span>
                    </button>
                </div>
    
                <div class="logout-container">
                    <button class="logout-btn logout-btn-delete" id="deleteBtn" onclick="window.location.href = 'delete_account.php';">
                        <svg class="logout-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3,6 5,6 21,6"/>
                            <path d="m19,6v14a2,2 0 0,1 -2,2H7a2,2 0 0,1 -2,-2V6m3,0V4a2,2 0 0,1 2,-2h4a2,2 0 0,1 2,2v2"/>
                            <line x1="10" y1="11" x2="10" y2="17"/>
                            <line x1="14" y1="11" x2="14" y2="17"/>
                        </svg>
                        <span class="logout-text">Supprimer compte</span>
                    </button>
                </div>
            </div>
    </div>
    </body>
    </html>
    <script type="text/javascript" src="scripts/nav.js"></script>
    <script type="text/javascript" src="scripts/account.js"></script>
