<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Validation
    if (empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Tous les champs sont obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email invalide.';
    } elseif ($password !== $confirm_password) {
        $error = 'Les mots de passe ne correspondent pas.';
    } elseif (strlen($password) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères.';
    } else {
        // Vérifier si l'email existe déjà
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = 'Cet email est déjà utilisé.';
        } else {
            // Créer le compte (mot de passe non chiffré comme demandé)
            $stmt = $pdo->prepare("INSERT INTO users (email, password, credits) VALUES (?, ?, 500)");
            if ($stmt->execute([$email, $password])) {
                $success = 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.';
            } else {
                $error = 'Erreur lors de la création du compte.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - AI Credits</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 400px;
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
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background: #0056b3;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 20px;
        }
        .success {
            color: green;
            text-align: center;
            margin-bottom: 20px;
        }
        .text-center {
            text-align: center;
            margin-top: 20px;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Inscription</h1>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="email">Email :</label>
                <input type="email" name="email" id="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <input type="password" name="password" id="password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe :</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>
            
            <button type="submit" class="btn">S'inscrire</button>
        </form>
        
        <div class="text-center">
            <p>Déjà un compte ? <a href="login.php">Se connecter</a></p>
            <p><a href="index.php">Retour à l'accueil</a></p>
        </div>
    </div>
</body>
</html>