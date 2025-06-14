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
        $mode_panel = "container right-panel-active"; // garder le panel de droite

        requireLogin();

        // Vérifier si l'utilisateur existe
        if (!$user) {
            $error = 'Utilisateur non trouvé.';
            header('Location: dashboard.php');
            exit();
        }

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
    else if ($mode == 'register') {
        $mode_panel = "container right-panel-active"; // garder le panel de droite

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
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
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
			<input type="text" id="name" name="name" placeholder="Name" />
			<input type="text" id="username" name="username" placeholder="Username" required />
			<input type="email" id="email" name="email" placeholder="Email" required/>
			<input type="password" id="password" name="password" required placeholder="Password" />
			<input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm password" />
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
			<input type="email" id="email" name="email" placeholder="Email" required/>
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
				<button class="ghost" id="signIn">Sign In</button>
			</div>
			<div class="overlay-panel overlay-right">
				<h1>Hello, Friend!</h1>
				<p>Enter your personal details and start journey with us</p>
				<button class="ghost" id="signUp">Sign Up</button>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript" src="scripts/auth.js"></script>