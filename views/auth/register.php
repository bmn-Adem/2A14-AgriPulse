<?php
$pageTitle = 'Inscription Multi-Profils — AgriPulse';
require __DIR__ . '/../layouts/header.php';

$selectedProfile = $_POST['profile_type'] ?? 'client_b2c';
$gouvernorats = [
    'Ariana', 'Béja', 'Ben Arous', 'Bizerte', 'Gabès', 'Gafsa', 'Jendouba', 'Kairouan',
    'Kasserine', 'Kébili', 'Le Kef', 'Mahdia', 'La Manouba', 'Médenine', 'Monastir',
    'Nabeul', 'Sfax', 'Sidi Bouzid', 'Siliana', 'Sousse', 'Tataouine', 'Tozeur', 'Tunis', 'Zaghouan'
];
?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card card-agri p-4 p-md-5">
                <div class="text-center mb-4">
                    <h2 class="fw-bold text-success mb-1"><i class="bi bi-person-plus-fill me-2"></i>Création de Compte AgriPulse</h2>
                    <p class="text-muted">Choisissez votre profil métier pour adapter les pièces justificatives requises</p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <strong class="d-block mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i>Veuillez corriger les erreurs suivantes :</strong>
                        <ul class="mb-0 ps-3 small">
                            <?php foreach ($errors as $err): ?>
                                <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="index.php?action=register" enctype="multipart/form-data" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                    <!-- Étape 1 : Choix du profil -->
                    <label class="form-label fw-bold mb-2">1. Sélectionnez votre profil <span class="text-danger">*</span></label>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="profile-option-card p-3 w-100 h-100 d-block">
                                <input type="radio" name="profile_type" value="client_b2c" class="form-check-input me-2"
                                       <?= $selectedProfile === 'client_b2c' ? 'checked' : '' ?>>
                                <strong class="text-primary"><i class="bi bi-bag-heart me-1"></i>Client B2C</strong>
                                <div class="small text-muted mt-1">Particulier (Activation immédiate)</div>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <label class="profile-option-card p-3 w-100 h-100 d-block">
                                <input type="radio" name="profile_type" value="agriculteur" class="form-check-input me-2"
                                       <?= $selectedProfile === 'agriculteur' ? 'checked' : '' ?>>
                                <strong class="text-success"><i class="bi bi-tree me-1"></i>Agriculteur</strong>
                                <div class="small text-muted mt-1">CIN + Attestation UTAP/CRDA</div>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <label class="profile-option-card p-3 w-100 h-100 d-block">
                                <input type="radio" name="profile_type" value="client_b2b" class="form-check-input me-2"
                                       <?= $selectedProfile === 'client_b2b' ? 'checked' : '' ?>>
                                <strong class="text-warning text-dark"><i class="bi bi-building me-1"></i>Client Pro (B2B)</strong>
                                <div class="small text-muted mt-1">Matricule Fiscal + Patente/RNE</div>
                            </label>
                        </div>
                    </div>

                    <!-- Étape 2 : Identité & Contact -->
                    <h5 class="fw-bold border-bottom pb-2 mb-3">2. Informations personnelles & Localisation</h5>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6 form-group">
                            <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
                            <input type="text" name="nom" class="form-control" data-validate="name"
                                   value="<?= htmlspecialchars($_POST['nom'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label fw-semibold">Prénom <span class="text-danger">*</span></label>
                            <input type="text" name="prenom" class="form-control" data-validate="name"
                                   value="<?= htmlspecialchars($_POST['prenom'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" data-validate="email"
                                   value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label fw-semibold">Téléphone Tunisien (8 chiffres) <span class="text-danger">*</span></label>
                            <input type="text" name="telephone" class="form-control" data-validate="phone" maxlength="8"
                                   placeholder="Ex: 22123456"
                                   value="<?= htmlspecialchars($_POST['telephone'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label fw-semibold">Gouvernorat <span class="text-danger">*</span></label>
                            <select name="gouvernorat" class="form-select" required>
                                <option value="">-- Choisir un gouvernorat --</option>
                                <?php foreach ($gouvernorats as $gov): ?>
                                    <option value="<?= $gov ?>" <?= (($_POST['gouvernorat'] ?? '') === $gov) ? 'selected' : '' ?>>
                                        <?= $gov ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label fw-semibold">Délégation <span class="text-danger">*</span></label>
                            <input type="text" name="delegation" class="form-control"
                                   placeholder="Ex: Houmt Souk, La Marsa, Sfax Ville..."
                                   value="<?= htmlspecialchars($_POST['delegation'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>
                    </div>

                    <!-- Section Dynamique : Client B2C -->
                    <div id="section-client_b2c" class="p-3 rounded-3 bg-primary bg-opacity-10 border border-primary-subtle mb-4">
                        <h6 class="fw-bold text-primary mb-2"><i class="bi bi-geo-alt me-1"></i>Livraison Particulier (B2C)</h6>
                        <div class="form-group">
                            <label class="form-label fw-semibold">Adresse exacte de livraison <span class="text-danger">*</span></label>
                            <input type="text" name="adresse_exacte" class="form-control" data-required-for="client_b2c"
                                   placeholder="Rue, numéro, résidence, code postal..."
                                   value="<?= htmlspecialchars($_POST['adresse_exacte'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>

                    <!-- Section Dynamique : Agriculteur -->
                    <div id="section-agriculteur" class="p-3 rounded-3 bg-success bg-opacity-10 border border-success-subtle mb-4" style="display:none;">
                        <h6 class="fw-bold text-success mb-3"><i class="bi bi-shield-check me-1"></i>Dossier de Vérification Agriculteur (Validation Admin requise)</h6>
                        <div class="row g-3">
                            <div class="col-md-4 form-group">
                                <label class="form-label fw-semibold">N° CIN Tunisienne (8 chiffres) <span class="text-danger">*</span></label>
                                <input type="text" name="cin" class="form-control" data-validate="cin" data-required-for="agriculteur" maxlength="8"
                                       placeholder="0 ou 1 + 7 chiffres"
                                       value="<?= htmlspecialchars($_POST['cin'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="form-label fw-semibold">Type d'activité agricole</label>
                                <input type="text" name="type_activite" class="form-control"
                                       placeholder="Ex: Oléiculture, Maraîchage, Agrumes..."
                                       value="<?= htmlspecialchars($_POST['type_activite'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="form-label fw-semibold">Superficie exploitée (Hectares)</label>
                                <input type="number" step="0.01" min="0" name="superficie_ha" class="form-control"
                                       placeholder="Ex: 4.50"
                                       value="<?= htmlspecialchars($_POST['superficie_ha'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label fw-semibold">Copie CIN (JPG, PNG ou PDF, max 5 Mo) <span class="text-danger">*</span></label>
                                <input type="file" name="doc_cin" class="form-control" accept=".jpg,.jpeg,.png,.pdf"
                                       data-validate="justificatif" data-required-for="agriculteur">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label fw-semibold">Attestation UTAP / CRDA ou Patente (JPG/PNG/PDF) <span class="text-danger">*</span></label>
                                <input type="file" name="doc_justificatif_agri" class="form-control" accept=".jpg,.jpeg,.png,.pdf"
                                       data-validate="justificatif" data-required-for="agriculteur">
                            </div>
                        </div>
                    </div>

                    <!-- Section Dynamique : Client B2B -->
                    <div id="section-client_b2b" class="p-3 rounded-3 bg-warning bg-opacity-10 border border-warning-subtle mb-4" style="display:none;">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-briefcase-fill me-1"></i>Dossier Professionnel B2B (Validation Admin requise)</h6>
                        <div class="row g-3">
                            <div class="col-md-6 form-group">
                                <label class="form-label fw-semibold">Raison Sociale <span class="text-danger">*</span></label>
                                <input type="text" name="raison_sociale" class="form-control" data-required-for="client_b2b"
                                       placeholder="Ex: Société Carthage Food SARL"
                                       value="<?= htmlspecialchars($_POST['raison_sociale'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label fw-semibold">Matricule Fiscal Tunisien <span class="text-danger">*</span></label>
                                <input type="text" name="matricule_fiscal" class="form-control" data-validate="mf" data-required-for="client_b2b"
                                       placeholder="Ex: 1548920/A/M/000"
                                       value="<?= htmlspecialchars($_POST['matricule_fiscal'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label fw-semibold">Adresse du siège / local <span class="text-danger">*</span></label>
                                <input type="text" name="adresse_exacte" class="form-control" data-required-for="client_b2b"
                                       placeholder="Zone industrielle / Rue..."
                                       value="<?= htmlspecialchars($_POST['adresse_exacte'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label fw-semibold">Patente ou Extrait RNE (JPG, PNG ou PDF, max 5 Mo) <span class="text-danger">*</span></label>
                                <input type="file" name="doc_justificatif_b2b" class="form-control" accept=".jpg,.jpeg,.png,.pdf"
                                       data-validate="justificatif" data-required-for="client_b2b">
                            </div>
                        </div>
                    </div>

                    <!-- Étape 3 : Sécurité -->
                    <h5 class="fw-bold border-bottom pb-2 mb-3">3. Sécurité du compte</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6 form-group">
                            <label class="form-label fw-semibold">Mot de passe fort <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" data-validate="password" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label fw-semibold">Confirmation du mot de passe <span class="text-danger">*</span></label>
                            <input type="password" name="confirm_password" class="form-control" data-validate="confirm_password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-agri btn-lg w-100 fw-bold">
                        <i class="bi bi-check2-circle me-1"></i>Finaliser mon inscription
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
