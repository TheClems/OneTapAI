<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Métiers IA - Portfolio Professionnel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 50%, #0f0f0f 100%);
            color: #ffffff;
            min-height: 100vh;
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header {
            text-align: center;
            margin-bottom: 3rem;
            padding: 2rem 0;
        }

        .header h1 {
            font-size: 3.5rem;
            font-weight: 700;
            background: linear-gradient(45deg, #00d4ff, #ff00ff, #00ff88);
            background-size: 300% 300%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: gradient 3s ease infinite;
            margin-bottom: 1rem;
        }

        .header p {
            font-size: 1.2rem;
            color: #cccccc;
            max-width: 600px;
            margin: 0 auto;
        }

        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .filter-tabs {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 3rem;
        }

        .filter-tab {
            padding: 0.8rem 1.5rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 25px;
            color: #ffffff;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .filter-tab:hover, .filter-tab.active {
            background: linear-gradient(45deg, #00d4ff, #ff00ff);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 212, 255, 0.3);
        }

        .careers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .career-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2rem;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .career-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #00d4ff, #ff00ff, #00ff88);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .career-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .career-card:hover::before {
            opacity: 1;
        }

        .career-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            display: block;
        }

        .career-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #ffffff;
        }

        .career-description {
            color: #cccccc;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }

        .career-category {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            background: rgba(0, 212, 255, 0.2);
            border: 1px solid rgba(0, 212, 255, 0.3);
            border-radius: 15px;
            font-size: 0.8rem;
            color: #00d4ff;
            margin-bottom: 1rem;
        }

        .career-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .tag {
            padding: 0.3rem 0.8rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            font-size: 0.75rem;
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .highlighted {
            background: rgba(255, 0, 255, 0.2);
            border-color: rgba(255, 0, 255, 0.3);
            color: #ff00ff;
        }

        .premium {
            background: rgba(255, 215, 0, 0.2);
            border-color: rgba(255, 215, 0, 0.3);
            color: #ffd700;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 2.5rem;
            }
            
            .careers-grid {
                grid-template-columns: 1fr;
            }
            
            .container {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <div class="header">
            <h1>Portfolio Métiers IA</h1>
            <p>Découvrez une sélection d'experts IA spécialisés dans différents domaines du marketing digital et de l'entrepreneuriat</p>
        </div>

        <div class="filter-tabs">
            <div class="filter-tab active" data-category="tous">Tous</div>
            <div class="filter-tab" data-category="marketing">Marketing</div>
            <div class="filter-tab" data-category="contenu">Contenu</div>
            <div class="filter-tab" data-category="technique">Technique</div>
            <div class="filter-tab" data-category="business">Business</div>
            <div class="filter-tab" data-category="media">Média</div>
        </div>

        <div class="careers-grid">
            <div class="career-card" data-category="contenu">
                <div class="career-icon">📝</div>
                <div class="career-category">Contenu & Communication</div>
                <h3 class="career-title">Rédacteur Discours Politique</h3>
                <p class="career-description">Expert en rédaction de discours politiques percutants et persuasifs. Maîtrise de la rhétorique et de l'art oratoire pour créer des messages impactants.</p>
                <div class="career-tags">
                    <span class="tag">Rhétorique</span>
                    <span class="tag">Persuasion</span>
                    <span class="tag">Communication</span>
                    <span class="tag highlighted">Politique</span>
                </div>
            </div>

            <div class="career-card" data-category="contenu">
                <div class="career-icon">📰</div>
                <div class="career-category">Média & Presse</div>
                <h3 class="career-title">Rédacteur Éditorial</h3>
                <p class="career-description">Spécialiste de la rédaction éditoriale pour la presse. Création de contenus journalistiques de qualité et gestion de ligne éditoriale.</p>
                <div class="career-tags">
                    <span class="tag">Journalisme</span>
                    <span class="tag">Éditorial</span>
                    <span class="tag">Presse</span>
                    <span class="tag">Investigation</span>
                </div>
            </div>

            <div class="career-card" data-category="technique">
                <div class="career-icon">⚙️</div>
                <div class="career-category">IA & Technologie</div>
                <h3 class="career-title">Prompt Engineer</h3>
                <p class="career-description">Expert en ingénierie de prompts pour optimiser les interactions avec les IA. Conception de stratégies de prompting avancées.</p>
                <div class="career-tags">
                    <span class="tag premium">IA</span>
                    <span class="tag premium">Prompting</span>
                    <span class="tag">Optimisation</span>
                    <span class="tag highlighted">Innovation</span>
                </div>
            </div>

            <div class="career-card" data-category="media">
                <div class="career-icon">📺</div>
                <div class="career-category">Média & Broadcasting</div>
                <h3 class="career-title">Broadcaster/Journalist</h3>
                <p class="career-description">Journaliste et animateur professionnel. Expert en communication audiovisuelle et présentation de contenus informatifs.</p>
                <div class="career-tags">
                    <span class="tag highlighted">Broadcasting</span>
                    <span class="tag">Animation</span>
                    <span class="tag">Audiovisuel</span>
                    <span class="tag">Live</span>
                </div>
            </div>

            <div class="career-card" data-category="business">
                <div class="career-icon">📊</div>
                <div class="career-category">Produit & Stratégie</div>
                <h3 class="career-title">Chef de Produit</h3>
                <p class="career-description">Responsable de la stratégie produit et de la roadmap. Coordination entre équipes techniques et business pour optimiser les performances.</p>
                <div class="career-tags">
                    <span class="tag">Stratégie</span>
                    <span class="tag">Roadmap</span>
                    <span class="tag">Analytics</span>
                    <span class="tag premium">Leadership</span>
                </div>
            </div>

            <div class="career-card" data-category="marketing">
                <div class="career-icon">🚀</div>
                <div class="career-category">Croissance & Performance</div>
                <h3 class="career-title">Growth Hacker</h3>
                <p class="career-description">Spécialiste de la croissance rapide et de l'optimisation des performances. Stratégies innovantes pour maximiser l'acquisition et la rétention.</p>
                <div class="career-tags">
                    <span class="tag highlighted">Growth</span>
                    <span class="tag">Acquisition</span>
                    <span class="tag">Rétention</span>
                    <span class="tag premium">Innovation</span>
                </div>
            </div>

            <div class="career-card" data-category="marketing">
                <div class="career-icon">📱</div>
                <div class="career-category">Réseaux Sociaux</div>
                <h3 class="career-title">Social Media Manager</h3>
                <p class="career-description">Expert en gestion des réseaux sociaux et création de communautés engagées. Stratégies de contenu viral et influence digitale.</p>
                <div class="career-tags">
                    <span class="tag">Social Media</span>
                    <span class="tag highlighted">Community</span>
                    <span class="tag">Engagement</span>
                    <span class="tag">Viral</span>
                </div>
            </div>

            <div class="career-card" data-category="marketing">
                <div class="career-icon">📈</div>
                <div class="career-category">Études & Recherche</div>
                <h3 class="career-title">Chargé d'Étude de Marché</h3>
                <p class="career-description">Analyste spécialisé dans l'étude des marchés et des comportements consommateurs. Insights stratégiques pour la prise de décision.</p>
                <div class="career-tags">
                    <span class="tag">Analyse</span>
                    <span class="tag">Marché</span>
                    <span class="tag">Insights</span>
                    <span class="tag premium">Data</span>
                </div>
            </div>

            <div class="career-card" data-category="marketing">
                <div class="career-icon">🎯</div>
                <div class="career-category">Influence & Partenariats</div>
                <h3 class="career-title">Influence Marketing</h3>
                <p class="career-description">Spécialiste du marketing d'influence et de la création de partenariats stratégiques avec les créateurs de contenu et influenceurs.</p>
                <div class="career-tags">
                    <span class="tag highlighted">Influence</span>
                    <span class="tag">Partenariats</span>
                    <span class="tag">Créateurs</span>
                    <span class="tag">ROI</span>
                </div>
            </div>

            <div class="career-card" data-category="technique">
                <div class="career-icon">🤖</div>
                <div class="career-category">Automatisation</div>
                <h3 class="career-title">Expert Marketing Automation</h3>
                <p class="career-description">Spécialiste de l'automatisation des processus marketing. Optimisation des workflows et personnalisation à grande échelle.</p>
                <div class="career-tags">
                    <span class="tag premium">Automation</span>
                    <span class="tag">Workflows</span>
                    <span class="tag">CRM</span>
                    <span class="tag highlighted">Efficacité</span>
                </div>
            </div>

            <div class="career-card" data-category="marketing">
                <div class="career-icon">📧</div>
                <div class="career-category">Email & Communication</div>
                <h3 class="career-title">Expert E-mail Marketing</h3>
                <p class="career-description">Maître de l'email marketing et des campagnes de communication personnalisées. Optimisation des taux d'ouverture et de conversion.</p>
                <div class="career-tags">
                    <span class="tag highlighted">Email</span>
                    <span class="tag">Conversion</span>
                    <span class="tag">Personnalisation</span>
                    <span class="tag">A/B Testing</span>
                </div>
            </div>

            <div class="career-card" data-category="technique">
                <div class="career-icon">🎨</div>
                <div class="career-category">Design & UX</div>
                <h3 class="career-title">UI/UX Designer</h3>
                <p class="career-description">Designer d'interfaces utilisateur et d'expériences numériques. Création de designs intuitifs et esthétiquement parfaits.</p>
                <div class="career-tags">
                    <span class="tag premium">UI/UX</span>
                    <span class="tag">Design</span>
                    <span class="tag highlighted">User-Centric</span>
                    <span class="tag">Prototyping</span>
                </div>
            </div>

            <div class="career-card" data-category="marketing">
                <div class="career-icon">🛒</div>
                <div class="career-category">E-commerce</div>
                <h3 class="career-title">Marketing E-commerce</h3>
                <p class="career-description">Expert en marketing pour plateformes e-commerce. Optimisation des conversions et stratégies de vente en ligne.</p>
                <div class="career-tags">
                    <span class="tag highlighted">E-commerce</span>
                    <span class="tag">Conversion</span>
                    <span class="tag">Marketplace</span>
                    <span class="tag premium">ROI</span>
                </div>
            </div>

            <div class="career-card" data-category="technique">
                <div class="career-icon">🔍</div>
                <div class="career-category">SEO & Référencement</div>
                <h3 class="career-title">Expert SEO</h3>
                <p class="career-description">Spécialiste du référencement naturel et de l'optimisation pour les moteurs de recherche. Stratégies de visibilité organique.</p>
                <div class="career-tags">
                    <span class="tag premium">SEO</span>
                    <span class="tag">Référencement</span>
                    <span class="tag highlighted">Organic</span>
                    <span class="tag">Keywords</span>
                </div>
            </div>

            <div class="career-card" data-category="business">
                <div class="career-icon">🎯</div>
                <div class="career-category">Entrepreneuriat</div>
                <h3 class="career-title">Serial Entrepreneur</h3>
                <p class="career-description">Entrepreneur expérimenté avec multiple créations d'entreprises. Vision stratégique et expertise en développement business.</p>
                <div class="career-tags">
                    <span class="tag premium">Entrepreneur</span>
                    <span class="tag highlighted">Innovation</span>
                    <span class="tag">Startup</span>
                    <span class="tag">Vision</span>
                </div>
            </div>

            <div class="career-card" data-category="business">
                <div class="career-icon">👔</div>
                <div class="career-category">Direction</div>
                <h3 class="career-title">Directeur Marketing & Commercial</h3>
                <p class="career-description">Direction stratégique des équipes marketing et commerciales. Leadership et vision globale pour la croissance de l'entreprise.</p>
                <div class="career-tags">
                    <span class="tag premium">Direction</span>
                    <span class="tag highlighted">Leadership</span>
                    <span class="tag">Stratégie</span>
                    <span class="tag">Management</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Filtrage des cartes par catégorie
        const filterTabs = document.querySelectorAll('.filter-tab');
        const careerCards = document.querySelectorAll('.career-card');

        filterTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                // Retirer la classe active de tous les onglets
                filterTabs.forEach(t => t.classList.remove('active'));
                // Ajouter la classe active à l'onglet cliqué
                tab.classList.add('active');

                const category = tab.getAttribute('data-category');

                careerCards.forEach(card => {
                    if (category === 'tous' || card.getAttribute('data-category') === category) {
                        card.style.display = 'block';
                        setTimeout(() => {
                            card.style.opacity = '1';
                            card.style.transform = 'translateY(0)';
                        }, 100);
                    } else {
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(20px)';
                        setTimeout(() => {
                            card.style.display = 'none';
                        }, 300);
                    }
                });
            });
        });

        // Animation d'apparition au scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        careerCards.forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'all 0.6s ease';
            observer.observe(card);
        });

        // Animation du titre au chargement
        window.addEventListener('load', () => {
            const title = document.querySelector('.header h1');
            title.style.animation = 'gradient 3s ease infinite';
        });
    </script>
</body>
</html>
<script src="scripts/nav.js"></script>