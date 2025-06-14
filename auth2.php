<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';

$mode = isset($_GET['mode']) ? $_GET['mode'] : 'login';
$error = '';
$success = '';
$user = getCurrentUser();

// Définir le mode du panel en fonction du mode ou des actions POST
$mode_panel = 'container'; // défaut pour login

if ($mode == 'register' || $mode == 'edit_profile') {
    $mode_panel = 'container right-panel-active';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'login') {
        $mode_panel = "container"; // garder le panel de gauche

        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        
        if (empty($email) || empty($password)) {
            $error = 'Tous les champs sont obligatoires.';
        } else {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT id, email, password, username FROM users WHERE email = ? OR username = ?");
            $stmt->execute([$email, $email]);
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
        $mode_panel = "container right-panel-active"; // garder le panel de droite
        $passwd_required="";
    
        // Vérifier si l'utilisateur existe
        if (!$user) {
            $error = 'Utilisateur non trouvé.';
            header('Location: dashboard.php');
            exit();
        }
    
        // Récupérer et nettoyer les données
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);
        $username = trim($_POST['username']);
        $name = trim($_POST['name']);
    
        // Validation
        if (empty($email) || empty($username) || empty($name)) {
            $error = 'Tous les champs obligatoires doivent être remplis.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Email invalide.';
        } elseif (!empty($password) && $password !== $confirm_password) {
            $error = 'Les mots de passe ne correspondent pas.';
        } else {
            $pdo = getDBConnection();
    
            // Vérifier si email ou username sont déjà utilisés par un autre utilisateur
            $stmt = $pdo->prepare("SELECT id FROM users WHERE (email = ? OR username = ?) AND id != ?");
            $stmt->execute([$email, $username, $user['id']]);
    
            if ($stmt->fetch()) {
                $error = 'Cet email ou ce nom d\'utilisateur est déjà utilisé par un autre compte.';
            } else {
                // Construire la requête SQL en fonction du mot de passe
                if (!empty($password)) {
                    // Nouveau mot de passe, on le hash
                    $stmt = $pdo->prepare("UPDATE users SET email = ?, username = ?, full_name = ?, password = ? WHERE id = ?");
                    $params = [$email, $username, $name, $password, $user['id']];
                } else {
                    // Pas de mot de passe modifié
                    $stmt = $pdo->prepare("UPDATE users SET email = ?, username = ?, full_name = ? WHERE id = ?");
                    $params = [$email, $username, $name, $user['id']];
                }
    
                // Exécution
                if ($stmt->execute($params)) {
                    $success = 'Informations mises à jour avec succès !';
    
                    // Rafraîchir les données utilisateur
                    $user = getCurrentUser();
    
                    // Mettre à jour la session
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_username'] = $username;
                    $_SESSION['user_full_name'] = $name;
                } else {
                    $error = 'Erreur lors de la mise à jour du profil.';
                }
            }
        }
    }    
    else if ($mode == 'register') {
        $mode_panel = "container right-panel-active"; // garder le panel de droite
        $passwd_required="required";

        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);
        $username = trim($_POST['username']);
        $name = trim($_POST['name']);

        
        if (empty($email) || empty($password) || empty($confirm_password) || empty($username) || empty($name)) {
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
                $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, username, credits) VALUES (?, ?, ?, ?, 500)");
                if ($stmt->execute([$name, $email, $password, $username])) {
                    $success = 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.';
                    $mode_panel = "container"; // Retour au panel de connexion après succès
                    header('Location: auth2.php?mode=login');
                } else {
                    $error = 'Erreur lors de la création du compte.';
                }
            }
        }
    }
}
?>
<link rel="stylesheet" href="css/auth.css" />

<?php if ($error): ?>
    <div class="alert alert-error" style="color: red; font-weight: bold; font-size: 16px; margin-bottom: 10px; text-align: center; padding: 10px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success" style="color: green; font-weight: bold; font-size: 16px; margin-bottom: 10px; text-align: center; padding: 10px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="<?php echo $mode_panel; ?>" id="container">
	<div class="form-container sign-up-container">
		<form method="POST" class="auth-form">
			<h1>Create Account</h1>
			<div class="social-container">
				<a href="#" class="social"><i class="fab fa-facebook-f"></i></a>
				<a href="#" class="social"><i class="fab fa-google-plus-g"></i></a>
				<a href="#" class="social"><i class="fab fa-linkedin-in"></i></a>
			</div>
			<span>or use your email for registration</span>
            <input type="text" id="name" name="name" placeholder="Name" 
                value="<?php echo isset($user) ? htmlspecialchars($user['full_name']) : ''; ?>" required />

            <input type="text" id="username" name="username" placeholder="Username" 
                value="<?php echo isset($user) ? htmlspecialchars($user['username']) : ''; ?>" required />

            <input type="email" id="email" name="email" placeholder="Email" 
                value="<?php echo isset($user) ? htmlspecialchars($user['email']) : ''; ?>" required />

            <input type="password" id="password" name="password"  placeholder="Password" <?php echo $passwd_required; ?>/>

            <input type="password" id="confirm_password" name="confirm_password"  placeholder="Confirm password" <?php echo $passwd_required; ?>/>

            <button type="submit">Sign Up</button>
		</form>
	</div>
	<div class="form-container sign-in-container">
		<form method="POST">
			<h1>Sign in</h1>
			<div class="social-container">
				<a href="#" class="social"><i class="fab fa-facebook-f"></i></a>
				<a href="#" class="social"><i class="fab fa-google-plus-g"></i></a>
				<a href="#" class="social"><i class="fab fa-linkedin-in"></i></a>
			</div>
			<span>or use your account</span>
			<input id="email" name="email" placeholder="Email" required/>
			<input type="password" id="password" name="password" placeholder="Password" required/>
			<a href="#">Forgot your password?</a>
			<button>Sign In</button>
		</form>
	</div>
	<div class="overlay-container">
		<div class="overlay">
			<div class="overlay-panel overlay-left">
				<h1>Welcome Back!</h1>
				<p>To keep connected with us please login with your personal info</p>
				<button class="ghost" id="signIn" onclick="window.location.href='auth2.php?mode=login'">Sign In</button>
			</div>
			<div class="overlay-panel overlay-right">
				<h1>Hello, Friend!</h1>
				<p>Enter your personal details and start journey with us</p>
				<button class="ghost" id="signUp" onclick="window.location.href='auth2.php?mode=register'">Sign Up</button>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript" src="scripts/auth.js"></script>