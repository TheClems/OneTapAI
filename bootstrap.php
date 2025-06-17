<?php
require_once 'config.php';
requireLogin();

$user = getCurrentUser();
$isDarkMode = $user['dark_mode'] == 1 ? 'true' : 'false';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>OneTapAI</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons (optionnel) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
        padding-left: 250px;
    }
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 250px;
        height: 100vh;
        background-color: #121212;
        padding-top: 1rem;
        color: white;
    }

    .sidebar a {
        color: white;
        text-decoration: none;
        padding: 10px 1rem;
        display: block;
        transition: background 0.2s;
    }

    .sidebar a:hover {
        background-color: #1f1f1f;
    }

    .sidebar .active {
        background-color: #343a40;
        font-weight: bold;
    }

    body.light-mode .sidebar {
        background-color: #f8f9fa;
        color: black;
    }

    body.light-mode .sidebar a {
        color: #212529;
    }

    body.light-mode .sidebar a:hover {
        background-color: #e2e6ea;
    }

    .theme-toggle {
        position: absolute;
        top: 10px;
        right: 10px;
        border: none;
        background: none;
        color: inherit;
    }
  </style>
  <script>
    const isDarkModeFromServer = <?= $isDarkMode ?>;
    document.addEventListener('DOMContentLoaded', () => {
        if (isDarkModeFromServer) {
            document.body.classList.remove('light-mode');
        } else {
            document.body.classList.add('light-mode');
        }

        document.getElementById('themeToggle').addEventListener('click', () => {
            document.body.classList.toggle('light-mode');
        });
    });
  </script>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <div class="d-flex justify-content-between align-items-center px-3 mb-3">
        <h4 class="mb-0">OneTapAI</h4>
        <button class="theme-toggle" id="themeToggle">
            <i class="bi bi-circle-half"></i>
        </button>
    </div>
    <a href="index.php" class="active"><i class="bi bi-house-door"></i> Home</a>
    <a href="#"><i class="bi bi-kanban"></i> Projects</a>
    <a href="chat.php"><i class="bi bi-chat-dots"></i> Chat</a>
    <a href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="buy_credits.php"><i class="bi bi-wallet2"></i> Buy credits</a>
    <a href="account.php"><i class="bi bi-gear"></i> Account</a>
</div>

<!-- Main content area -->
<div class="container mt-4">
    <h1>Welcome to OneTapAI</h1>
    <p>Your main content goes here...</p>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
