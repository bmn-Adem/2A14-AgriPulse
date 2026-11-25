?php require _DIR_ . '/../layouts/front_header.php'; ?>

<div class="container my-5" style="max-width: 460px;">
    <div class="card border-0 shadow">
        <div class="card-body p-4">
            <h3 class="text-center fw-bold text-success mb-4">Connexion</h3>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (isset($_GET['registered'])): ?>
                <div class="alert alert-success">Inscription réussie ! Vous pouvez vous connecter.</div>
            <?php endif; ?>

            <form method="POST" action="index.php?page=login">
                <div class="mb-3">
                    <label class="form-label">Adresse Email</label>
                    <input type="email" name="email" class="form-control" required placeholder="exemple@agripulse.tn">
                </div>
                <div class="mb-3">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="mot_de_passe" class="form-control" required placeholder="****">
                </div>
                <button type="submit" class="btn btn-success w-100 fw-semibold">Se connecter</button>
            </form>

            <p class="text-center mt-3 mb-0 small">
                Pas encore de compte ? <a href="index.php?page=register" class="text-success fw-semibold">Créer un compte</a>
            </p>
        </div>
    </div>
</div>

<?php require _DIR_ . '/../layouts/front_footer.php'; ?>