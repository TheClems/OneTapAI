// Initialiser Lucide icons
lucide.createIcons();




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