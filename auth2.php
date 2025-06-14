
<link rel="stylesheet" href="css/auth.css" />


<div class="container" id="container">
	<div class="form-container sign-up-container">
		<form method="POST" class="auth-form">
			<h1>Create Account</h1>
			<div class="social-container">
				<a href="#" class="social"><i class="fab fa-facebook-f"></i></a>
				<a href="#" class="social"><i class="fab fa-google-plus-g"></i></a>
				<a href="#" class="social"><i class="fab fa-linkedin-in"></i></a>
			</div>
			<span>or use your email for registration</span>
			<input type="text" id="name" name="name"placeholder="Name" required />
			<input type="text" id="username" name="username" placeholder="Username" required />
			<input type="email" id="email" name="email" placeholder="Email" required/>
			<input type="password" id="password" name="password" required placeholder="Password" />
			<input type="password" id="confirm_password" name="password" required placeholder="Confirm password" />
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

<script type="text/javascript" src="assets/js/nav.js"></script>
