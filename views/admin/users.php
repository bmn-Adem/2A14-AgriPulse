<?php
$pageTitle = 'Administration des Utilisateurs — AgriPulse';
require __DIR__ . '/../layouts/header.php';

$gouvernorats = [
    'Ariana', 'Béja', 'Ben Arous', 'Bizerte', 'Gabès', 'Gafsa', 'Jendouba', 'Kairouan',
    'Kasserine', 'Kébili', 'Le Kef', 'Mahdia', 'La Manouba', 'Médenine', 'Monastir',
    'Nabeul', 'Sfax', 'Sidi Bouzid', 'Siliana', 'Sousse', 'Tataouine', 'Tozeur', 'Tunis', 'Zaghouan'
];
?>
<div class="container-fluid px-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h2 class="fw-bold text-success mb-0"><i class="bi bi-people-fill me-2"></i>Back-Office — Gestion des Utilisateurs</h2>
            <p class="text-muted small mb-0">Validation des dossiers Agriculteurs & Clients B2B, contrôle des rôles et modération</p>
        </div>
        <button class="btn btn-agri fw-semibold" data-bs-toggle="modal" data-bs-target="#modalCreateUser">
            <i class="bi bi-person-plus-fill me-1"></i>Nouvel Utilisateur
        </button>
    </div>

    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= htmlspecialchars($_SESSION['flash_success'], ENT_QUOTES, 'UTF-8') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <strong><i class="bi bi-exclamation-octagon-fill me-1"></i>Erreur lors de l'opération :</strong>
            <ul class="mb-0 ps-3 small mt-1">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Cartes Statistiques -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card card-agri p-3 border-start border-4 border-success">
                <div class="text-muted small fw-semibold">Total Utilisateurs</div>
                <div class="fs-3 fw-bold text-dark"><?= (int) ($stats['total'] ?? 0) ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card card-agri p-3 border-start border-4 border-warning">
                <div class="text-muted small fw-semibold">Dossiers en Attente</div>
                <div class="fs-3 fw-bold text-warning"><?= (int) ($stats['pending'] ?? 0) ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card card-agri p-3 border-start border-4 border-primary">
                <div class="text-muted small fw-semibold">Agriculteurs</div>
                <div class="fs-3 fw-bold text-primary"><?= (int) ($stats['agriculteurs'] ?? 0) ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card card-agri p-3 border-start border-4 border-info">
                <div class="text-muted small fw-semibold">Clients B2B / B2C</div>
                <div class="fs-3 fw-bold text-info"><?= (int) (($stats['b2b'] ?? 0) + ($stats['b2c'] ?? 0)) ?></div>
            </div>
        </div>
    </div>

    <!-- Section Dossiers en attente de vérification -->
    <?php if (!empty($pendingUsers)): ?>
        <div class="card card-agri mb-4 border border-warning">
            <div class="card-header bg-warning bg-opacity-25 fw-bold text-dark d-flex align-items-center justify-content-between">
                <span><i class="bi bi-hourglass-split me-2"></i>Dossiers Professionnels en Attente de Validation (<?= count($pendingUsers) ?>)</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>Candidat</th>
                            <th>Profil</th>
                            <th>Identifiants Légaux</th>
                            <th>Localisation</th>
                            <th>Justificatifs</th>
                            <th class="text-end">Décision</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingUsers as $p): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($p['prenom'] . ' ' . $p['nom'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="text-muted"><?= htmlspecialchars($p['email'], ENT_QUOTES, 'UTF-8') ?> | <?= htmlspecialchars($p['telephone'], ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                                <td><span class="badge bg-dark"><?= htmlspecialchars($p['role_libelle'], ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td>
                                    <?php if ($p['role_nom'] === 'agriculteur'): ?>
                                        <div><strong>CIN :</strong> <?= htmlspecialchars($p['cin'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="text-muted"><?= htmlspecialchars($p['type_activite'] ?? 'Agriculture', ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars((string) ($p['superficie_ha'] ?? '0'), ENT_QUOTES, 'UTF-8') ?> ha)</div>
                                    <?php else: ?>
                                        <div><strong>Société :</strong> <?= htmlspecialchars($p['raison_sociale'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="text-muted"><strong>MF :</strong> <?= htmlspecialchars($p['matricule_fiscal'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($p['gouvernorat'] . ' — ' . $p['delegation'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?php if (!empty($p['doc_cin_path'])): ?>
                                        <a href="<?= htmlspecialchars($p['doc_cin_path'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="btn btn-outline-secondary btn-sm me-1">
                                            <i class="bi bi-file-earmark-person"></i> CIN
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($p['doc_justificatif_path'])): ?>
                                        <a href="<?= htmlspecialchars($p['doc_justificatif_path'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-file-earmark-check"></i> Justificatif
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <form method="POST" action="index.php?action=admin_verify_user" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                        <button type="submit" class="btn btn-success btn-sm fw-semibold">
                                            <i class="bi bi-check-lg me-1"></i>Valider le compte
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- Barre de Recherche & Filtres -->
    <div class="card card-agri p-3 mb-4">
        <form method="GET" action="index.php" class="row g-2 align-items-end">
            <input type="hidden" name="action" value="admin_users">
            <div class="col-md-5">
                <label class="form-label small fw-semibold mb-1">Recherche (Nom, Email, CIN, Raison Sociale, MF)</label>
                <input type="text" name="search" class="form-control" placeholder="Rechercher un utilisateur..."
                       value="<?= htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Filtrer par Rôle</label>
                <select name="role" class="form-select">
                    <option value="">Tous les rôles</option>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= htmlspecialchars($r['nom'], ENT_QUOTES, 'UTF-8') ?>"
                            <?= (($_GET['role'] ?? '') === $r['nom']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($r['libelle'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Filtrer par Statut</label>
                <select name="statut" class="form-select">
                    <option value="">Tous les statuts</option>
                    <option value="verifie" <?= (($_GET['statut'] ?? '') === 'verifie') ? 'selected' : '' ?>>Vérifié</option>
                    <option value="en_attente" <?= (($_GET['statut'] ?? '') === 'en_attente') ? 'selected' : '' ?>>En attente</option>
                    <option value="suspendu" <?= (($_GET['statut'] ?? '') === 'suspendu') ? 'selected' : '' ?>>Suspendu</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-agri w-100"><i class="bi bi-search"></i> Filtrer</button>
                <a href="index.php?action=admin_users" class="btn btn-outline-secondary" title="Réinitialiser"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>

    <!-- Tableau Principal CRUD -->
    <div class="card card-agri overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-success">
                    <tr>
                        <th>#</th>
                        <th>Utilisateur</th>
                        <th>Rôle</th>
                        <th>Détails Métier</th>
                        <th>Localisation</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Aucun utilisateur trouvé pour ces critères.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td class="fw-bold text-muted"><?= (int) $u['id'] ?></td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?> | <?= htmlspecialchars($u['telephone'], ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($u['role_libelle'], ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td class="small">
                                    <?php if ($u['role_nom'] === 'agriculteur'): ?>
                                        CIN: <strong><?= htmlspecialchars($u['cin'] ?? '-', ENT_QUOTES, 'UTF-8') ?></strong>
                                    <?php elseif ($u['role_nom'] === 'client_b2b'): ?>
                                        <?= htmlspecialchars($u['raison_sociale'] ?? '-', ENT_QUOTES, 'UTF-8') ?> (MF: <?= htmlspecialchars($u['matricule_fiscal'] ?? '-', ENT_QUOTES, 'UTF-8') ?>)
                                    <?php else: ?>
                                        <span class="text-muted">Compte standard</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small"><?= htmlspecialchars($u['gouvernorat'] . ' (' . $u['delegation'] . ')', ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?php if ($u['statut'] === 'verifie'): ?>
                                        <span class="badge bg-success">Vérifié</span>
                                    <?php elseif ($u['statut'] === 'en_attente'): ?>
                                        <span class="badge bg-warning text-dark">En attente</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Suspendu</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-outline-primary btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditUser<?= (int) $u['id'] ?>"
                                            title="Modifier">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <?php if ((int) $u['id'] !== (int) ($_SESSION['user_id'] ?? 0)): ?>
                                        <form method="POST" action="index.php?action=admin_delete_user" class="d-inline"
                                              onsubmit="return confirm('Confirmer la suppression définitive de cet utilisateur ?');">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Supprimer">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <!-- Modal Modification Utilisateur -->
                            <div class="modal fade" id="modalEditUser<?= (int) $u['id'] ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <form method="POST" action="index.php?action=admin_update_user" class="modal-content">
                                        <div class="modal-header bg-success text-white">
                                            <h5 class="modal-title">Modifier : <?= htmlspecialchars($u['prenom'] . ' ' . $u['nom'], ENT_QUOTES, 'UTF-8') ?></h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold">Nom</label>
                                                    <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($u['nom'], ENT_QUOTES, 'UTF-8') ?>" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold">Prénom</label>
                                                    <input type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($u['prenom'], ENT_QUOTES, 'UTF-8') ?>" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold">Email</label>
                                                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?>" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold">Téléphone</label>
                                                    <input type="text" name="telephone" class="form-control" maxlength="8" value="<?= htmlspecialchars($u['telephone'], ENT_QUOTES, 'UTF-8') ?>" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-semibold">Rôle</label>
                                                    <select name="role_id" class="form-select">
                                                        <?php foreach ($roles as $r): ?>
                                                            <option value="<?= (int) $r['id'] ?>" <?= ((int) $u['role_id'] === (int) $r['id']) ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($r['libelle'], ENT_QUOTES, 'UTF-8') ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-semibold">Statut</label>
                                                    <select name="statut" class="form-select">
                                                        <option value="verifie" <?= $u['statut'] === 'verifie' ? 'selected' : '' ?>>Vérifié</option>
                                                        <option value="en_attente" <?= $u['statut'] === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                                                        <option value="suspendu" <?= $u['statut'] === 'suspendu' ? 'selected' : '' ?>>Suspendu</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-semibold">Nouveau mot de passe (optionnel)</label>
                                                    <input type="password" name="password" class="form-control" placeholder="Laisser vide si inchangé">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold">Gouvernorat</label>
                                                    <select name="gouvernorat" class="form-select">
                                                        <?php foreach ($gouvernorats as $gov): ?>
                                                            <option value="<?= $gov ?>" <?= $u['gouvernorat'] === $gov ? 'selected' : '' ?>><?= $gov ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold">Délégation</label>
                                                    <input type="text" name="delegation" class="form-control" value="<?= htmlspecialchars($u['delegation'], ENT_QUOTES, 'UTF-8') ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-agri">Enregistrer les modifications</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Création Utilisateur par Admin -->
<div class="modal fade" id="modalCreateUser" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="index.php?action=admin_create_user" class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-person-plus-fill me-2"></i>Ajouter un Utilisateur</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Rôle *</label>
                        <select name="role_id" class="form-select" required>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= (int) $r['id'] ?>"><?= htmlspecialchars($r['libelle'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Statut initial *</label>
                        <select name="statut" class="form-select" required>
                            <option value="verifie">Vérifié (Actif immédiatement)</option>
                            <option value="en_attente">En attente</option>
                            <option value="suspendu">Suspendu</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Nom *</label>
                        <input type="text" name="nom" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Prénom *</label>
                        <input type="text" name="prenom" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Email *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Téléphone (8 chiffres) *</label>
                        <input type="text" name="telephone" class="form-control" maxlength="8" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Gouvernorat *</label>
                        <select name="gouvernorat" class="form-select" required>
                            <?php foreach ($gouvernorats as $gov): ?>
                                <option value="<?= $gov ?>"><?= $gov ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Délégation *</label>
                        <input type="text" name="delegation" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">CIN (si Agriculteur)</label>
                        <input type="text" name="cin" class="form-control" maxlength="8">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Raison Sociale (si Client B2B)</label>
                        <input type="text" name="raison_sociale" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Matricule Fiscal (si Client B2B)</label>
                        <input type="text" name="matricule_fiscal" class="form-control" placeholder="1548920/A/M/000">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Mot de passe fort *</label>
                        <input type="password" name="password" class="form-control" placeholder="Min 8 car., Maj, min, chiffre, spécial" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-agri">Créer le compte</button>
            </div>
        </form>
    </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>