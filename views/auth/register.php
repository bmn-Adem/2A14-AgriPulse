?php require _DIR_ . '/../layouts/front_header.php'; ?>

<div class="container my-5" style="max-width: 500px;">
    <div class="card border-0 shadow">
        <div class="card-body p-4">
            <h3 class="text-center fw-bold text-success mb-4">Créer un compte</h3>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="index.php?page=register">
                <div class="mb-3">
                    <label class="form-label">Nom complet</label>
                    <input type="text" name="nom" class="form-control" required placeholder="Ex: Adem Ben Mimoun">
                </div>
                <div class="mb-3">
                    <label class="form-label">Adresse Email</label>
                    <input type="email" name="email" class="form-control" required placeholder="exemple@agripulse.tn">
                </div>
                <div class="mb-3">
                    <label class="form-label">Rôle</label>
                    <select name="role_id" class="form-select" required>
                        <option value="2">Agriculteur</option>
                        <option value="3" selected>Client</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="mot_de_passe" class="form-control" minlength="6" required>
                </div>
                <button type="submit" class="btn btn-success w-100 fw-semibold">S'inscrire</button>
            </form>

            <p class="text-center mt-3 mb-0 small">
                Déjà inscrit ? <a href="index.php?page=login" class="text-success fw-semibold">Se connecter</a>
            </p>
        </div>
    </div>
</div>

<?php require _DIR_ . '/../layouts/front_footer.php'; ?>