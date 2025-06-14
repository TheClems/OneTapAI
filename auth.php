<?php
require_once 'config.php';

$mode = isset($_GET['mode']) ? $_GET['mode'] : 'login';
$error = '';
$success = '';
$user = getCurrentUser();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'login') {
        $mode_panel="container"

        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        
        if (empty($email) || empty($password)) {
            $error = 'Tous les champs sont obligatoires.';
        } else {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT id, email, password, username FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && $user['password'] === $password) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_username'] = $user['username'];
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Email ou mot de passe incorrect.';
            }
        }
    } 
    else if ($mode == 'edit_profile') {
        $mode_panel="container right-panel-active"

        $error = '';
        $success = '';
        requireLogin();

        // Vérifier si l'utilisateur existe
        if (!$user) {
            $error = 'Utilisateur non trouvé.';
            header('Location: dashboard.php');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $confirm_password = trim($_POST['confirm_password']);
            $username = trim($_POST['username']);
            
            // Validation
            if (empty($email) || (empty($password) && !empty($confirm_password)) || empty($username)) {
                $error = 'Tous les champs obligatoires doivent être remplis.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Email invalide.';
            } elseif (!empty($password) && $password !== $confirm_password) {
                $error = 'Les mots de passe ne correspondent pas.';
            } else {
                $pdo = getDBConnection();
                
                // Vérifier si l'email ou le username sont déjà utilisés par un autre utilisateur
                $stmt = $pdo->prepare("SELECT id FROM users WHERE (email = ? OR username = ?) AND id != ?");
                $stmt->execute([$email, $username, $user['id']]);
                
                if ($stmt->fetch()) {
                    $error = 'Cet email ou ce nom d\'utilisateur est déjà utilisé par un autre compte.';
                } else {
                    // Update user information
                    $stmt = $pdo->prepare("UPDATE users SET email = ?, username = ?" . (!empty($password) ? ", password = ?" : "") . " WHERE id = ?");
                    $params = [$email, $username, $user['id']];
                    
                    if (!empty($password)) {
                        array_splice($params, 2, 0, $password);
                    }
                    
                    if ($stmt->execute($params)) {
                        $success = 'Informations mises à jour avec succès !';
                        // Refresh user data
                        $user = getCurrentUser();
                        // Mettre à jour les données de session
                        $_SESSION['user_email'] = $email;
                        $_SESSION['user_username'] = $username;
                    } else {
                        $error = 'Erreur lors de la mise à jour du profil.';
                    }
                }
            }
        }
    
    }
    else { // register

        $mode_panel="container right-panel-active"
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);
        $username = trim($_POST['username']);
        
        if (empty($email) || empty($password) || empty($confirm_password) || empty($username)) {
            $error = 'Tous les champs sont obligatoires.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Email invalide.';
        } elseif ($password !== $confirm_password) {
            $error = 'Les mots de passe ne correspondent pas.';
        } elseif (strlen($password) < 6) {
            $error = 'Le mot de passe doit contenir au moins 6 caractères.';
        } else {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $stmt2 = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt2->execute([$username]);
            
            if ($stmt->fetch()) {
                $error = 'Cet email est déjà utilisé.';
            } elseif ($stmt2->fetch()) {
                $error = 'Ce nom d\'utilisateur est déjà utilisé.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (email, password, username, credits) VALUES (?, ?, ?, 500)");
                if ($stmt->execute([$email, $password, $username])) {
                    $success = 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.';
                } else {
                    $error = 'Erreur lors de la création du compte.';
                }
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
    <title>
        <?php
            if ($mode == 'login') {
                echo 'Connexion';
            } elseif ($mode == 'register') {
                echo 'Inscription';
            } elseif ($mode == 'edit_profile') {
                echo 'Modifier le profil';
            }
        ?> - AI Credits
    </title>
    <link rel="stylesheet" href="css/auth.css">
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="js/auth.js"></script>
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                
                <div class="auth-mode-switch">
                    <?php if ($mode != 'edit_profile'): ?>
                        <button class="mode-btn <?php echo $mode == 'login' ? 'active' : ''; ?>" onclick="window.location.href='auth.php?mode=login'">Connexion</button>
                        <button class="mode-btn <?php echo $mode == 'register' ? 'active' : ''; ?>" onclick="window.location.href='auth.php?mode=register'">Inscription</button>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <?php if ($mode == 'register' || $mode == 'edit_profile'): ?>
                    <div class="form-group">
                        <label for="username">Nom d'utilisateur</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <?php if ($mode == 'register' || $mode == 'edit_profile'): ?>
                    <div class="form-group">
                        <label for="confirm_password">Confirmer le mot de passe</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                <?php endif; ?>

                <button type="submit" class="btn <?php echo $mode == 'login' ? 'btn-primary' : 'btn-success'; ?>">
                <?php
                    if ($mode == 'login') {
                        echo 'Se connecter';
                    } elseif ($mode == 'register') {
                        echo 'S\'inscrire';
                    } elseif ($mode == 'edit_profile') {
                        echo 'Modifier';
                    }
                ?>
                </button>
            </form>
        </div>
    </div>
</body>
</html>
