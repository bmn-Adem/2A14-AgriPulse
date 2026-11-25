<?php
$pageTitle = 'Accueil — AgriPulse';
require __DIR__ . '/layouts/header.php';
?>
<div class="container">
    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= htmlspecialchars($_SESSION['flash_success'], ENT_QUOTES, 'UTF-8') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <div class="p-5 mb-4 rounded-4 text-white shadow" style="background: linear-gradient(120deg, #1b5e20, #388e3c);">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="badge bg-warning text-dark mb-2 px-3 py-2 fw-semibold">
                    <i class="bi bi-geo-alt-fill me-1"></i>24 Gouvernorats — Tunisie
                </span>
                <h1 class="display-5 fw-bold mb-3">Connecter les Agriculteurs Tunisiens aux Marchés B2C & B2B</h1>
                <p class="lead mb-4 opacity-90">
                    AgriPulse centralise la gestion des exploitations agricoles, la vente directe aux particuliers et l'approvisionnement professionnel vérifié.
                </p>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="index.php?action=register" class="btn btn-warning btn-lg fw-bold text-dark">
                            <i class="bi bi-person-plus-fill me-1"></i>Rejoindre AgriPulse
                        </a>
                        <a href="index.php?action=login" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Se connecter
                        </a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-light text-dark d-inline-block mb-0">
                        <i class="bi bi-person-check-fill text-success me-2"></i>
                        Bienvenue <strong><?= htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom'], ENT_QUOTES, 'UTF-8') ?></strong>
                        — Espace <strong><?= htmlspecialchars($_SESSION['role_libelle'] ?? $_SESSION['role'], ENT_QUOTES, 'UTF-8') ?></strong> actif.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card card-agri h-100 p-4">
                <div class="text-success fs-1 mb-2"><i class="bi bi-tree-fill"></i></div>
                <h5 class="fw-bold">Espace Agriculteur</h5>
                <p class="text-muted small mb-0">
                    Inscription vérifiée par CIN et attestation UTAP / CRDA. Gérez vos parcelles, récoltes et ventes directes sans intermédiaires.
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-agri h-100 p-4">
                <div class="text-primary fs-1 mb-2"><i class="bi bi-bag-heart-fill"></i></div>
                <h5 class="fw-bold">Client Particulier (B2C)</h5>
                <p class="text-muted small mb-0">
                    Activation immédiate de votre compte pour commander des produits frais du terroir livrés directement à votre adresse en Tunisie.
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-agri h-100 p-4">
                <div class="text-warning fs-1 mb-2"><i class="bi bi-building-check"></i></div>
                <h5 class="fw-bold">Client Professionnel (B2B)</h5>
                <p class="text-muted small mb-0">
                    Hôtels, restaurants et grossistes : inscription avec Matricule Fiscal et Patente/RNE pour des commandes en gros sécurisées.
                </p>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/layouts/footer.php'; ?>
