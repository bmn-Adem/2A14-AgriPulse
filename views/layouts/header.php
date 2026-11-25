<?php
$pageTitle = $pageTitle ?? 'AgriPulse — Plateforme Agricole Tunisienne';
$currentAction = $_GET['action'] ?? 'home';
$isAuth = isset($_SESSION['user_id'], $_SESSION['role']);
$userRole = $_SESSION['role'] ?? '';
$userName = trim(($_SESSION['prenom'] ?? '') . ' ' . ($_SESSION['nom'] ?? ''));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --agri-primary: #1b5e20;
            --agri-secondary: #2e7d32;
            --agri-accent: #f9a825;
            --agri-bg: #f4f7f5;
        }
        body { background-color: var(--agri-bg); font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; }
        .navbar-agri { background: linear-gradient(135deg, var(--agri-primary), var(--agri-secondary)); }
        .btn-agri { background-color: var(--agri-primary); border-color: var(--agri-primary); color: #fff; }
        .btn-agri:hover { background-color: #144718; border-color: #144718; color: #fff; }
        .card-agri { border: none; border-radius: 14px; box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06); }
        .profile-option-card { cursor: pointer; transition: all 0.2s ease; border: 2px solid #dee2e6; border-radius: 12px; }
        .profile-option-card:has(input:checked) { border-color: var(--agri-primary); background-color: #e8f5e9; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-lg navbar-dark navbar-agri shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="index.php?action=home">
            <i class="bi bi-flower1 fs-4 text-warning"></i>
            <span>AgriPulse</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= $currentAction === 'home' ? 'active fw-semibold' : '' ?>" href="index.php?action=home">
                        <i class="bi bi-house-door me-1"></i>Accueil
                    </a>
                </li>
                <?php if ($isAuth && $userRole === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= str_starts_with($currentAction, 'admin') ? 'active fw-semibold' : '' ?>" href="index.php?action=admin_users">
                            <i class="bi bi-shield-lock me-1"></i>Back-Office Admin
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <?php if ($isAuth): ?>
                    <span class="badge bg-light text-dark py-2 px-3">
                        <i class="bi bi-person-circle me-1 text-success"></i>
                        <?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?>
                        <span class="text-muted">(<?= htmlspecialchars($_SESSION['role_libelle'] ?? $userRole, ENT_QUOTES, 'UTF-8') ?>)</span>
                    </span>
                    <a href="index.php?action=logout" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-box-arrow-right me-1"></i>Déconnexion
                    </a>
                <?php else: ?>
                    <a href="index.php?action=login" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Connexion
                    </a>
                    <a href="index.php?action=register" class="btn btn-warning btn-sm fw-semibold text-dark">
                        <i class="bi bi-person-plus me-1"></i>Créer un compte
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<main class="flex-grow-1 py-4">



