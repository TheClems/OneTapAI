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
    // Initialiser Lucide icons
lucide.createIcons();

// Variables PHP pour JS
const userUsername = '<?php echo htmlspecialchars($user['username']); ?>';
const userFullName = '<?php echo htmlspecialchars($user['full_name']); ?>';



// Gestion des boutons avec effets
function handleButtonClick(button, loadingText, successText, successColor) {
    button.classList.add('loading');
    const originalText = button.querySelector('span') ? button.querySelector('span').textContent : button.textContent;

    if (button.querySelector('span')) {
        button.querySelector('span').textContent = loadingText;
    } else {
        button.textContent = loadingText;
    }

    createParticles(button);

    setTimeout(() => {
        button.classList.remove('loading');
        if (button.querySelector('span')) {
            button.querySelector('span').textContent = successText;
        } else {
            button.textContent = successText;
        }
        if (successColor) {
            button.style.background = successColor;
        }

        setTimeout(() => {
            if (button.querySelector('span')) {
                button.querySelector('span').textContent = originalText;
            } else {
                button.textContent = originalText;
            }
            button.style.background = '';
        }, 2000);
    }, 1500);
}

// Modal de suppression
function showDeleteModal() {
    document.getElementById('deleteModal').style.display = 'block';
    document.getElementById('confirmUsername').value = '';
    document.getElementById('deleteConfirmBtn').disabled = true;
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

function confirmDelete() {
    window.location.href = 'delete_account.php';
}

// Vérifier la saisie du nom d'utilisateur
document.getElementById('confirmUsername').addEventListener('input', function () {
    const deleteBtn = document.getElementById('deleteConfirmBtn');
    if (this.value === userUsername) {
        deleteBtn.disabled = false;
        deleteBtn.style.opacity = '1';
    } else {
        deleteBtn.disabled = true;
        deleteBtn.style.opacity = '0.5';
    }
});

// Fermer la modal en cliquant à l'extérieur
window.onclick = function (event) {
    const modal = document.getElementById('deleteModal');
    if (event.target === modal) {
        closeDeleteModal();
    }
}

// Event listeners pour les boutons
document.getElementById('profileBtn').addEventListener('click', function (e) {
    e.preventDefault();
    handleButtonClick(this, 'Chargement...', 'Profil ouvert !', 'linear-gradient(135deg, #48bb78, #38a169)');
    setTimeout(() => {
        window.location.href = 'auth.php?mode=edit_profile';
    }, 1000);
});

document.getElementById('logoutBtn').addEventListener('click', function (e) {
    e.preventDefault();
    handleButtonClick(this, 'Déconnexion...', 'Déconnecté !', 'linear-gradient(135deg, #48bb78, #38a169)');
    setTimeout(() => {
        window.location.href = 'logout.php';
    }, 1500);
});
</script>
    <script type="text/javascript" src="scripts/nav.js"></script>
    <script src="scripts/animated-bg.js"></script>
</body>
</html>