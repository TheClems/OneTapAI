<?php
require_once 'config.php';
requireLogin();

$user = getCurrentUser();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account - OneTapAI</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lucide/0.263.1/umd/lucide.js"></script>
    <link rel="stylesheet" href="css/account.css">
    <link rel="stylesheet" href="css/animated-bg.css">
</head>
<body class="body_account">
    <?php require_once 'nav.php'; ?>

    <div class="animated-bg" id="animatedBg"></div>

    <div class="main-content">
        
        <h1>Account</h1>
        <p class="subtitle">Manage your profile and settings</p>

        <div class="account-container">
            <div class="profile-header">
                <div class="profile-picture" id="profilePicture">
                    <?php echo htmlspecialchars(strtoupper($user['full_name'][0])); ?>
                </div>
                <div class="profile-info">
                    <h2 class="h2_name"><?php echo htmlspecialchars($user['full_name']); ?></h2>
                    <div class="username">@<?php echo htmlspecialchars($user['username']); ?></div>
                    <div class="email"><?php echo htmlspecialchars($user['email']); ?></div>
                    <div class="status">Premium member</div>
                </div>
            </div>

            <div class="info-grid">
                <div class="info-card">
                    <div class="info-row">
                        <div class="info-label">
                            Current credits
                        </div>
                        <div class="info-value credits"><?php echo number_format($user['credits']); ?></div>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-row">
                        <div class="info-label">
                            Date of registration
                        </div>
                        <div class="info-value"><?php echo date('d/m/Y à H:i', strtotime($user['date_inscription'])); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">
                            Last connection
                        </div>
                        <div class="info-value">14/06/2025 14:32</div>
                    </div>
                </div>
            </div>

            <div class="actions-section">
                <h3>Account actions</h3>
                <div class="actions-grid">
                    <button class="btn btn-primary" id="profileBtn" onclick="window.location.href = 'auth.php?mode=edit_profile';">
                        Edit profile
                    </button>
                    <button class="btn btn-secondary" id="logoutBtn" onclick="window.location.href = 'logout.php';">
                        Logout
                    </button>
                    <button class="btn btn-danger" id="deleteBtn" onclick="showDeleteModal()">
                        Delete account
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3>⚠️ Delete account</h3>
            <p>This action is irreversible. All your data will be permanently deleted.</p>
            <p>To confirm the deletion, type your username : <strong><?php echo htmlspecialchars($user['username']); ?></strong></p>
            <input type="text" id="confirmUsername" placeholder="Username">
            <div class="modal-actions">
                <button class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                <button class="btn btn-danger" onclick="confirmDelete()" disabled id="deleteConfirmBtn">
                    Delete permanently
                </button>
            </div>
        </div>
    </div>
    <script>
        const userUsername = '<?php echo htmlspecialchars($user['username']); ?>';
        const userFullName = '<?php echo htmlspecialchars($user['full_name']); ?>';
    </script>
    <script type="text/javascript" src="scripts/account.js"></script>
    <script type="text/javascript" src="scripts/nav.js"></script>
    <script src="scripts/animated-bg.js"></script>
</body>
</html>