<?php
require_once 'config.php';
requireLogin();

$user = getCurrentUser();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account - OneTapAI</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lucide/0.263.1/umd/lucide.js"></script>
    <link rel="stylesheet" href="css/tailwind-build.css">
    <link rel="stylesheet" href="css/plans.css">
    <link rel="stylesheet" href="css/animations.css">
</head>
<body class="body_plans">
    <?php require_once 'nav.php'; ?>

    <div class="animated-bg" id="animatedBg"></div>

    <div class="main-content">
        
        <h1>Plans</h1>
        <p class="subtitle">Choose the right plan for you</p>

        <div class="container">
    <!-- Card 1 -->
    <div class="card">
      <h3>
        <span>$9</span>
        <span>/mo</span>
      </h3>
      <p>Essential AI tools for everyday use</p>
      <hr />
      <ul>
        <li><i class="bi bi-check-circle-fill"></i><span>1,000 AI powered chat messages</span></li>
        <li><i class="bi bi-check-circle-fill"></i><span>30 premium image generations</span></li>
        <li><i class="bi bi-check-circle-fill"></i><span>10 premium music generation</span></li>
        <li class="disabled"><i class="bi bi-check-circle-fill"></i><span>Access to all premium AI models</span></li>
        <li class="disabled"><i class="bi bi-check-circle-fill"></i><span>Early access to new features</span></li>
      </ul>
      <a href="#" class="btn">Choose plan</a>
    </div>

    <!-- Card 2 -->
    <div class="card highlight">
      <h3>
        <span>$17</span>
        <span>/mo</span>
      </h3>
      <p>Advanced features for serious AI enthusiasts.</p>
      <hr />
      <ul>
        <li><i class="bi bi-check-circle-fill"></i><span>5,000 AI powered chat messages</span></li>
        <li><i class="bi bi-check-circle-fill"></i><span>100 premium image generations</span></li>
        <li><i class="bi bi-check-circle-fill"></i><span>40 premium music generation</span></li>
        <li><i class="bi bi-check-circle-fill"></i><span>Access to all premium AI models</span></li>
        <li class="disabled"><i class="bi bi-check-circle-fill"></i><span>Early access to new features</span></li>
      </ul>
      <a href="#" class="btn">Choose plan</a>
    </div>

    <!-- Card 3 -->
    <div class="card">
      <h3>
        <span>$29</span>
        <span>/mo</span>
      </h3>
      <p>Unlimited potential for power users</p>
      <hr />
      <ul>
        <li><i class="bi bi-check-circle-fill"></i><span>10,000 AI powered chat messages</span></li>
        <li><i class="bi bi-check-circle-fill"></i><span>300 premium image generations</span></li>
        <li><i class="bi bi-check-circle-fill"></i><span>100 premium music generations</span></li>
        <li><i class="bi bi-check-circle-fill"></i><span>Access to all premium AI models</span></li>
        <li><i class="bi bi-check-circle-fill"></i><span>Early access to new features</span></li>
      </ul>
      <a href="#" class="btn">Choose plan</a>
    </div>
  </div>

    </div>
    <script type="text/javascript" src="scripts/nav.js"></script>
    <script type="text/javascript" src="scripts/animated-bg.js"></script>
</body>
</html>