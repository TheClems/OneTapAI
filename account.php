<?php
require_once 'config.php';
requireLogin();

$user = getCurrentUser();
?>

<!DOCTYPE html>
<html lang="frs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account - OneTapAI</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lucide/0.263.1/umd/lucide.js"></script>
    <link rel="stylesheet" href="css/account.css">
</head>
<body class="body_account">
    <?php require_once 'nav.php'; ?>

    <div class="main-content">
        
        <h1>Mon Compte</h1>
        <p class="subtitle">Gérez votre profil et vos paramètres</p>

        <div class="account-container">
            <div class="profile-header">
                <div class="profile-picture" id="profilePicture">
                    <?php echo htmlspecialchars(strtoupper($user['full_name'][0])); ?>
                </div>
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
                    <div class="username">@<?php echo htmlspecialchars($user['username']); ?></div>
                    <div class="email"><?php echo htmlspecialchars($user['email']); ?></div>
                    <div class="status">Membre Premium</div>
                </div>
            </div>

            <div class="info-grid">
                <div class="info-card">
                    <div class="info-row">
                        <div class="info-label">
                            Nom d'utilisateur
                        </div>
                        <div class="info-value"><?php echo htmlspecialchars($user['username']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">
                            Email
                        </div>
                        <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-row">
                        <div class="info-label">
                            Crédits actuels
                        </div>
                        <div class="info-value credits"><?php echo number_format($user['credits']); ?></div>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-row">
                        <div class="info-label">
                            Date d'inscription
                        </div>
                        <div class="info-value"><?php echo date('d/m/Y à H:i', strtotime($user['date_inscription'])); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">
                            Dernière connexion
                        </div>
                        <div class="info-value">14/06/2025 14:32</div>
                    </div>
                </div>
            </div>

            <div class="actions-section">
                <h3>Actions du compte</h3>
                <div class="actions-grid">
                    <button class="btn btn-primary" id="profileBtn" onclick="window.location.href = 'auth.php?mode=edit_profile';">
                        Modifier profil
                    </button>
                    <button class="btn btn-secondary" id="logoutBtn" onclick="window.location.href = 'logout.php';">
                        Se déconnecter
                    </button>
                    <button class="btn btn-danger" id="deleteBtn" onclick="showDeleteModal()">
                        Supprimer compte
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3>⚠️ Supprimer le compte</h3>
            <p>Cette action est irréversible. Toutes vos données seront définitivement supprimées.</p>
            <p>Pour confirmer la suppression, tapez votre nom d'utilisateur : <strong><?php echo htmlspecialchars($user['username']); ?></strong></p>
            <input type="text" id="confirmUsername" placeholder="Nom d'utilisateur">
            <div class="modal-actions">
                <button class="btn btn-secondary" onclick="closeDeleteModal()">Annuler</button>
                <button class="btn btn-danger" onclick="confirmDelete()" disabled id="deleteConfirmBtn">
                    Supprimer définitivement
                </button>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="scripts/account.js"></script>
    <script type="text/javascript" src="scripts/nav.js"></script>
</body>
</html>