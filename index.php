<?php
require_once 'config.php';

// Si l'utilisateur est déjà connecté, rediriger vers le dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Credits - Accueil</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px;
        }
        .btn:hover {
            background:rgb(75, 0, 0);
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Bienvenue sur AI Credits</h1>
        <p>Votre plateforme de chat avec des intelligences artificielles avancées.</p>
        
        <h2>Fonctionnalités</h2>
        <ul>
            <li>Chat avec des IA performantes</li>
            <li>Système de crédits flexible</li>
            <li>Interface simple et intuitive</li>
            <li>Gestion de vos conversations</li>
        </ul>
        
        <div class="text-center">
            <a href="register.php" class="btn">S'inscrire</a>
            <a href="login.php" class="btn">Se connecter</a>
        </div>
    </div>
</body>
</html>