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
    <title>Dashboard - AI Credits</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .credits-box {
            background: #e7f3ff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }
        .credits-number {
            font-size: 36px;
            font-weight: bold;
            color: #007bff;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn-success {
            background: #28a745;
        }
        .btn-success:hover {
            background: #218838;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .actions {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-bottom: 30px;
        }
        .welcome {
            color: #333;
        }
        .logout {
            color: #dc3545;
            text-decoration: none;
        }
        .logout:hover {
            text-decoration: underline;
        }
        .section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #eee;
            border-radius: 10px;
        }
        .section h3 {
            margin-top: 0;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div style="display: flex; align-items: center; gap: 20px;">
                <h1 class="welcome">Bienvenue, <?php echo htmlspecialchars($user['username']); ?></h1>
                <a href="edit_profile.php" class="btn btn-success">Modifier le profil</a>
                <a href="logout.php" class="logout">Déconnexion</a>
            </div>
        </div>
        
        <div class="credits-box">
            <h2>Vos crédits</h2>
            <div class="credits-number"><?php echo number_format($user['credits']); ?></div>
            <p>crédits disponibles</p>
        </div>
        
        <div class="actions">
            <a href="buy_credits.php" class="btn btn-success">Acheter des crédits</a>
            <a href="new_chat.php" class="btn">Créer un nouveau chat</a>
        </div>
        
        <div class="section">
            <h3>Informations du compte</h3>
            <p><strong>Nom d'utilisateur :</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <p><strong>Email :</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Date d'inscription :</strong> <?php echo date('d/m/Y à H:i', strtotime($user['date_inscription'])); ?></p>
            <p><strong>Crédits :</strong> <?php echo number_format($user['credits']); ?></p>
        </div>
        
        <div class="section">
            <h3>Fonctionnalités disponibles</h3>
            <ul>
                <li>Chat avec des IA avancées</li>
                <li>Historique de vos conversations</li>
                <li>Gestion flexible de vos crédits</li>
                <li>Support technique</li>
            </ul>
        </div>
    </div>
</body>
</html>