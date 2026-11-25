<?php require __DIR__ . '/../layouts/front_header.php'; ?>

<section class="bg-success bg-gradient text-white py-5">
    <div class="container py-4 text-center">
        <h1 class="display-5 fw-bold">Cultivez l'avenir avec AgriPulse</h1>
        <p class="lead mx-auto" style="max-width: 680px;">
            Plateforme intégrée de gestion des récoltes, suivi des plantations, partage d'équipements agricoles, ateliers écologiques et diagnostic des sols.
        </p>
        <div class="mt-4 d-flex justify-content-center gap-3">
            <?php if (!isset($_SESSION['user'])): ?>
                <a href="index.php?page=register" class="btn btn-light text-success btn-lg fw-semibold">Rejoindre AgriPulse</a>
                <a href="index.php?page=login" class="btn btn-outline-light btn-lg">Se connecter</a>
            <?php else: ?>
                <a href="index.php?page=dashboard" class="btn btn-warning btn-lg fw-semibold">Accéder à mon espace</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="container my-5">
    <h2 class="text-center fw-bold mb-4 text-success">Nos 5 Pôles Agricoles</h2>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-success"><i class="bi bi-basket2-fill me-2"></i>Récoltes & Rendements</h5>
                    <p class="card-text text-muted">Suivi des quantités récoltées, qualité des produits et rendements par saison agricole.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-success"><i class="bi bi-tree-fill me-2"></i>Cultures & Plantations</h5>
                    <p class="card-text text-muted">Gestion complète du cycle de vie depuis le semis jusqu'à la maturation.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-success"><i class="bi bi-tools me-2"></i>Partage d'Équipements</h5>
                    <p class="card-text text-muted">Location d'outils agricoles et calendrier partagé en économie circulaire.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-success"><i class="bi bi-people-fill me-2"></i>Sensibilisation & Ateliers</h5>
                    <p class="card-text text-muted">Organisation d'ateliers de compostage et de formations agricoles.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-success"><i class="bi bi-clipboard2-pulse-fill me-2"></i>Conseil & Diagnostic Sol</h5>
                    <p class="card-text text-muted">Fiches d'analyses chimiques (pH, humidité, NPK) et préconisations agronomiques.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../layouts/front_footer.php'; ?>