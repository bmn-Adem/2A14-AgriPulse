<?php require __DIR__ . '/../layouts/back_header.php'; ?>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm border-start border-success border-4">
            <div class="card-body">
                <h6 class="text-muted text-uppercase">Total Utilisateurs</h6>
                <h2 class="fw-bold mb-0"><?= count($users) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm border-start border-primary border-4">
            <div class="card-body">
                <h6 class="text-muted text-uppercase">Rôles Configurés</h6>
                <h2 class="fw-bold mb-0"><?= count($roles) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm border-start border-warning border-4">
            <div class="card-body">
                <h6 class="text-muted text-uppercase">Statut Socle</h6>
                <h5 class="fw-bold text-success mb-0 mt-2">Opérationnel</h5>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-1">Gestion des comptes & contrôle d'accès</h5>
            <p class="text-muted mb-0">Consultez la liste des inscrits, modifiez leurs rôles (Admin, Agriculteur, Client) ou supprimez un compte.</p>
        </div>
        <a href="index.php?page=users" class="btn btn-success">Gérer les utilisateurs</a>
    </div>
</div>

<?php require __DIR__ . '/../layouts/back_footer.php'; ?>