<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriPulse — Agriculture Intelligente & Durable</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php?page=home"><i class="bi bi-flower1 me-2"></i>AgriPulse</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#frontNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="frontNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link active" href="index.php?page=home">Accueil</a></li>
            </ul>
            <div class="d-flex gap-2 align-items-center">
                <?php if (isset($_SESSION['user'])): ?>
                    <span class="text-white small me-2">Bonjour, <strong><?= htmlspecialchars($_SESSION['user']['nom']) ?></strong> (<?= htmlspecialchars($_SESSION['user']['role_nom']) ?>)</span>
                    <?php if ($_SESSION['user']['role_nom'] === 'admin'): ?>
                        <a href="index.php?page=dashboard" class="btn btn-warning btn-sm fw-semibold">Back-Office</a>
                    <?php endif; ?>
                    <a href="index.php?page=logout" class="btn btn-outline-light btn-sm">Déconnexion</a>
                <?php else: ?>
                    <a href="index.php?page=login" class="btn btn-outline-light btn-sm">Connexion</a>
                    <a href="index.php?page=register" class="btn btn-light text-success btn-sm fw-semibold">Inscription</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<main class="flex-grow-1">