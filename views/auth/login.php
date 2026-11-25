<?php
$pageTitle = 'Connexion — AgriPulse';
require __DIR__ . '/../layouts/header.php';
?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card card-agri p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-inline-flex p-3 mb-2">
                        <i class="bi bi-shield-lock-fill fs-2"></i>
                    </div>
                    <h3 class="fw-bold mb-1">Connexion AgriPulse</h3>
                    <p class="text-muted small">Accédez à votre espace sécurisé</p>
                </div>

                <?php if (!empty($_SESSION['flash_success'])): ?>
                    <div class="alert alert-success small">
                        <i class="bi bi-check-circle-fill me-1"></i>
                        <?= htmlspecialchars($_SESSION['flash_success'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <?php unset($_SESSION['flash_success']); ?>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger small">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $err): ?>
                                <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="index.php?action=login" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                    <div class="form-group mb-3">
                        <label class="form-label fw-semibold">Adresse Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" data-validate="email"
                               value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="exemple@agripulse.tn" required>
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label fw-semibold">Mot de passe <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>

                    <button type="submit" class="btn btn-agri w-100 py-2 fw-semibold">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Se connecter
                    </button>
                </form>

                <hr class="my-4">
                <p class="text-center small text-muted mb-0">
                    Pas encore de compte ?
                    <a href="index.php?action=register" class="fw-semibold text-success text-decoration-none">Créer un compte multi-profils</a>
                </p>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>



