<?php
require_once 'config.php';

$mode = isset($_GET['mode']) ? $_GET['mode'] : 'login';
$error = '';
$success = '';
$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'login') {
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
    } elseif ($mode == 'register') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);

        if (empty($name) || empty($email) || empty($username) || empty($password) || empty($confirm_password)) {
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
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, username, credits) VALUES (?, ?, ?, ?, 500)");
                if ($stmt->execute([$name, $email, $password, $username])) {
                    $success = 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.';
                    $mode = 'login'; // Bascule vers connexion
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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Auth - AI Credits</title>
    <link rel="stylesheet" href="css/auth.css" />
    <script defer>
        document.addEventListener("DOMContentLoaded", function () {
            const container = document.getElementById('container');
            const mode = '<?php echo $mode; ?>';
            if (mode === 'register') container.classList.add("right-panel-active");
            else container.classList.remove("right-panel-active");

            document.getElementById("signUp").addEventListener("click", () => {
                container.classList.add("right-panel-active");
            });

            document.getElementById("signIn").addEventListener("click", () => {
                container.classList.remove("right-panel-active");
            });
        });
    </script>
    <script src="scripts/auth.js"></script>
</head>
<body>
<?php if ($error): ?>
    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="container" id="container">
    <div class="form-container sign-up-container">
        <form method="POST" action="?mode=register">
            <h1>Create Account</h1>
            <div class="social-container">
                <a href="#" class="social"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="social"><i class="fab fa-google-plus-g"></i></a>
                <a href="#" class="social"><i class="fab fa-linkedin-in"></i></a>
            </div>
            <span>or use your email for registration</span>
            <input type="text" id="name" name="name" placeholder="Name" required />
            <input type="text" id="username" name="username" placeholder="Username" required />
            <input type="email" id="email" name="email" placeholder="Email" required/>
            <input type="password" id="password" name="password" placeholder="Password" required />
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm password" required />
            <button type="submit">Sign Up</button>
        </form>
    </div>

    <div class="form-container sign-in-container">
        <form method="POST" action="?mode=login">
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
            <button type="submit">Sign In</button>
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
</body>
</html>
