<?php
require_once 'config.php';

// Vérifier si l'utilisateur est déjà connecté
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Credits - Chat avec IA</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="welcome">Bienvenue sur AI Credits</h1>
        <p class="description">Commencez à discuter avec des IA avancées dès maintenant !</p>
        <a href="auth.php?mode=login" class="btn btn-primary">Se connecter</a>
        <a href="auth.php?mode=register" class="btn btn-success">S'inscrire</a>
        <div class="text-center">
            <a href="register.php" class="btn">S'inscrire</a>
            <a href="login.php" class="btn">Se connecter</a>
        </div>
    </div>
</body>
</html>