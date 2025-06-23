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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/animations.css">
    <link rel="stylesheet" href="css/plans.css">
</head>
<body class="body_plans">
    <?php require_once 'nav.php'; ?>

    <div class="animated-bg" id="animatedBg"></div>

    <div class="main-content">
        
        <h1>Plans</h1>
        <p class="subtitle">Choose the right plan for you</p>

        <div class="container">

        <div id="loading" class="loading" style="display: none;">
            <i class="bi bi-arrow-clockwise" style="animation: spin 1s linear infinite;"></i>
            Chargement de votre plan actuel...
        </div>

        <div id="error" class="error" style="display: none;">
            Impossible de charger les informations du plan actuel.
        </div>

        <div class="cards-container" id="cards-container">
            <!-- Card 1 - starter -->
            <div class="card" data-plan="starter">
                <h2>Starter</h2>
                <h3>
                    <span>9€</span>
                    <span>/mois</span>
                </h3>
                <p>Outils IA essentiels pour un usage quotidien</p>
                <hr />
                <ul>
                    <li><i class="bi bi-check-circle-fill"></i><span>100 000 Crédits par mois</span></li>
                    <li><i class="bi bi-check-circle-fill"></i><span>Accès aux meilleurs LLM</span></li>
                    <li><i class="bi bi-check-circle-fill"></i><span>&#126;2 000 messages de chat IA par mois</span></li>
                    <li><i class="bi bi-check-circle-fill"></i><span>&#126;50 générations d'images par mois</span></li>
                    <li><i class="bi bi-check-circle-fill"></i><span>Accès aux Personas</span></li>
                    <li class="disabled"><i class="bi bi-check-circle-fill"></i><span>Personalisation de Personas</span></li>
                    <li class="disabled"><i class="bi bi-check-circle-fill"></i><span>Accès anticipé aux nouvelles fonctionnalités</span></li>
                </ul>
                <a href="#" class="btn">Choisir ce plan</a>
            </div>

            <!-- Card 2 - Premium -->
            <div class="card" data-plan="premium">
                <h2>Premium</h2>
                <h3>
                    <span>17€</span>
                    <span>/mois</span>
                </h3>
                <p>Fonctionnalités avancées pour les passionnés d'IA</p>
                <hr />
                <ul>
                <li><i class="bi bi-check-circle-fill"></i><span>200 000 Crédits par mois</span></li>
                    <li><i class="bi bi-check-circle-fill"></i><span>Accès aux meilleurs LLM</span></li>
                    <li><i class="bi bi-check-circle-fill"></i><span>&#126;4 000 messages de chat IA par mois</span></li>
                    <li><i class="bi bi-check-circle-fill"></i><span>&#126;100 générations d'images par mois</span></li>
                    <li><i class="bi bi-check-circle-fill"></i><span>Accès aux Personas</span></li>
                    <li><i class="bi bi-check-circle-fill"></i><span>Personalisation de Personas</span></li>
                    <li class="disabled"><i class="bi bi-check-circle-fill"></i><span>Accès anticipé aux nouvelles fonctionnalités</span></li>
                </ul>
                <a href="#" class="btn">Choisir ce plan</a>
            </div>

            <!-- Card 3 - Professional -->
            <div class="card" data-plan="professional">
                <h2>Professional</h2>
                <h3>
                    <span>29€</span>
                    <span>/mois</span>
                </h3>
                <p>Potentiel illimité pour les utilisateurs experts</p>
                <hr />
                <ul>
                <li><i class="bi bi-check-circle-fill"></i><span>400 000 Crédits par mois</span></li>
                    <li><i class="bi bi-check-circle-fill"></i><span>Accès aux meilleurs LLM</span></li>
                    <li><i class="bi bi-check-circle-fill"></i><span>&#126;8 000 messages de chat IA par mois</span></li>
                    <li><i class="bi bi-check-circle-fill"></i><span>&#126;200 générations d'images par mois</span></li>
                    <li><i class="bi bi-check-circle-fill"></i><span>Accès aux Personas</span></li>
                    <li><i class="bi bi-check-circle-fill"></i><span>Personalisation de Personas</span></li>
                    <li><i class="bi bi-check-circle-fill"></i><span>Accès anticipé aux nouvelles fonctionnalités</span></li>
                </ul>
                <a href="#" class="btn">Choisir ce plan</a>
            </div>
        </div>
    </div>

    </div>
    <script type="text/javascript" src="scripts/nav.js"></script>
    <script type="text/javascript" src="scripts/animated-bg.js"></script>
    <script>
        // Animation de rotation pour le loading
        const style = document.createElement('style');
        style.textContent = `
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);

        // Fonction pour récupérer le plan utilisateur depuis la base de données
        async function fetchUserPlan() {
            const loadingEl = document.getElementById('loading');
            const errorEl = document.getElementById('error');
            const cardsContainer = document.getElementById('cards-container');
            
            try {
                loadingEl.style.display = 'block';
                cardsContainer.style.opacity = '0.5';
                
                // Remplacez cette URL par votre endpoint API
                const response = await fetch('/api/user-plan', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        // Ajoutez vos headers d'authentification si nécessaire
                        // 'Authorization': 'Bearer ' + token
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                const userPlan = data.plan || data.user_plan || data.userPlan; // Support de différents formats de réponse
                
                if (userPlan) {
                    highlightCurrentPlan(userPlan);
                }
                
            } catch (error) {
                console.error('Erreur lors de la récupération du plan utilisateur:', error);
                errorEl.style.display = 'block';
                
                // Fallback: essayer de récupérer depuis localStorage ou cookies
                const fallbackPlan = localStorage.getItem('user-plan') || getCookie('user-plan');
                if (fallbackPlan) {
                    highlightCurrentPlan(fallbackPlan);
                }
                
            } finally {
                loadingEl.style.display = 'none';
                cardsContainer.style.opacity = '1';
            }
        }

        // Fonction pour mettre en évidence le plan actuel
        function highlightCurrentPlan(planName) {
            // Nettoyer les classes existantes
            document.querySelectorAll('.card').forEach(card => {
                card.classList.remove('current-plan');
            });
            
            // Normaliser le nom du plan (en minuscules, sans espaces)
            const normalizedPlan = planName.toLowerCase().replace(/[^a-z0-9]/g, '');
            
            // Mapping des noms de plans possibles
            const planMapping = {
                'starter': 'starter',
                'premium': 'premium',
                'professional': 'professional',
            };
            
            const mappedPlan = planMapping[normalizedPlan] || normalizedPlan;
            
            // Trouver et mettre en évidence la carte correspondante
            const currentCard = document.querySelector(`[data-plan="${mappedPlan}"]`);
            if (currentCard) {
                currentCard.classList.add('current-plan');
                
                // Changer le texte du bouton pour le plan actuel
                const btn = currentCard.querySelector('.btn');
                if (btn) {
                    btn.textContent = 'Plan actuel';
                    btn.onclick = (e) => {
                        e.preventDefault();
                        alert('Vous utilisez déjà ce plan!');
                    };
                }
            }
        }

        // Fonction utilitaire pour lire les cookies
        function getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
            return null;
        }

        // Gestion des clics sur les boutons de plans
        document.querySelectorAll('.btn').forEach(btn => {
            if (!btn.onclick) { // Ne pas écraser le onclick du plan actuel
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    
                    const card = btn.closest('.card');
                    const plan = card.getAttribute('data-plan');
                    
                    // Animation de clic
                    btn.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        btn.style.transform = '';
                    }, 150);
                    
                    // Ici vous pouvez ajouter la logique pour changer de plan
                    console.log('Plan sélectionné:', plan);
                    
                    // Exemple d'appel API pour changer de plan
                    // changePlan(plan);
                });
            }
        });

        // Fonction pour changer de plan (exemple)
        async function changePlan(newPlan) {
            try {
                const response = await fetch('/api/change-plan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        // 'Authorization': 'Bearer ' + token
                    },
                    body: JSON.stringify({ plan: newPlan })
                });
                
                if (response.ok) {
                    // Recharger les informations du plan
                    await fetchUserPlan();
                    alert('Plan changé avec succès!');
                }
            } catch (error) {
                console.error('Erreur lors du changement de plan:', error);
                alert('Erreur lors du changement de plan');
            }
        }

        // Charger le plan utilisateur au chargement de la page
        document.addEventListener('DOMContentLoaded', () => {
            fetchUserPlan();
        });

        // Pour les tests en développement - simuler un plan utilisateur
        // Décommentez la ligne suivante pour tester avec un plan spécifique
        // setTimeout(() => highlightCurrentPlan('pro'), 1000);
    </script>
</body>
</html>