<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Back-Office — AgriPulse Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="d-flex min-vh-100">
    <!-- Sidebar -->
    <aside class="bg-dark text-white p-3 d-flex flex-column" style="width: 260px;">
        <a href="index.php?page=dashboard" class="text-white text-decoration-none fs-4 fw-bold mb-4">
            <i class="bi bi-flower1 text-success me-2"></i>AgriPulse <span class="badge bg-success fs-6">Admin</span>
        </a>
        <ul class="nav nav-pills flex-column gap-1 mb-auto">
            <li class="nav-item">
                <a href="index.php?page=dashboard" class="nav-link text-white <?= ($_GET['page'] ?? '') === 'dashboard' ? 'active bg-success' : '' ?>">
                    <i class="bi bi-speedometer2 me-2"></i>Tableau de bord
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php?page=users" class="nav-link text-white <?= ($_GET['page'] ?? '') === 'users' ? 'active bg-success' : '' ?>">
                    <i class="bi bi-people me-2"></i>Utilisateurs & Rôles
                </a>
            </li>
            <li class="nav-item mt-3 text-uppercase small text-secondary px-3">Modules (À venir)</li>
            <li class="nav-item"><span class="nav-link text-secondary"><i class="bi bi-basket2 me-2"></i>Récoltes</span></li>
            <li class="nav-item"><span class="nav-link text-secondary"><i class="bi bi-tree me-2"></i>Plantations</span></li>
            <li class="nav-item"><span class="nav-link text-secondary"><i class="bi bi-tools me-2"></i>Matériels</span></li>
            <li class="nav-item"><span class="nav-link text-secondary"><i class="bi bi-calendar-event me-2"></i>Ateliers Éco</span></li>
            <li class="nav-item"><span class="nav-link text-secondary"><i class="bi bi-clipboard2-pulse me-2"></i>Diagnostics Sol</span></li>
        </ul>
        <hr>
        <div class="d-flex flex-column gap-2">
            <a href="index.php?page=home" class="btn btn-outline-light btn-sm"><i class="bi bi-globe me-1"></i>Voir le Front-Office</a>
            <a href="index.php?page=logout" class="btn btn-danger btn-sm"><i class="bi bi-box-arrow-right me-1"></i>Déconnexion</a>
        </div>
    </aside>

    <!-- Contenu principal -->
    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
            <h4 class="mb-0 fw-bold text-dark">Espace Administration</h4>
            <span class="text-muted">Connecté : <strong><?= htmlspecialchars($_SESSION['user']['nom'] ?? '') ?></strong></span>
        </div>