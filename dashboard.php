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
    <title>Dashboard - AI Credits</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/animations.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

</head>

<body>
    <?php require_once 'bootstrap.php'; ?>

    <!-- Animated background -->
    <div class="animated-bg" id="animatedBg"></div>

    <div class="floating-elements">
        <div class="floating-element"></div>
        <div class="floating-element"></div>
        <div class="floating-element"></div>
    </div>
    <div class="container">
        <div class="header">
            <div class="user-info">
                <h1 class="welcome">Welcome <?php echo htmlspecialchars($user['full_name']); ?></h1>
            </div>
        </div>

        <div class="section features">
            <h3>Features</h3>
            <ul>
                <li>Chat with advanced AI</li>
                <li>Conversation history</li>
                <li>Flexible credit management</li>
                <li>Technical support</li>
            </ul>
        </div>
    </div>
    <script type="text/javascript" src="scripts/nav.js"></script>
    <script type="text/javascript" src="scripts/floating-element.js"></script>
</body>

</html>