<?php require __DIR__ . '/../layouts/back_header.php'; ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-success"><i class="bi bi-people-fill me-2"></i>Liste des Utilisateurs</h5>
        <span class="badge bg-success"><?= count($users) ?> inscrit(s)</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nom complet</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Date d'inscription</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td>#<?= (int)$u['id'] ?></td>
                            <td class="fw-semibold"><?= htmlspecialchars($u['nom']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <form method="POST" action="index.php?page=users_role" class="d-flex gap-2 align-items-center" style="max-width: 240px;">
                                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                    <select name="role_id" class="form-select form-select-sm" <?= ($u['id'] === $_SESSION['user']['id']) ? 'disabled' : '' ?>>
                                        <?php foreach ($roles as $r): ?>
                                            <option value="<?= (int)$r['id'] ?>" <?= ((int)$r['id'] === (int)$u['role_id']) ? 'selected' : '' ?>>
                                                <?= ucfirst(htmlspecialchars($r['nom'])) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if ($u['id'] !== $_SESSION['user']['id']): ?>
                                        <button type="submit" class="btn btn-sm btn-outline-success">OK</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                            <td class="small text-muted"><?= htmlspecialchars($u['date_inscription']) ?></td>
                            <td class="text-end">
                                <?php if ($u['id'] !== $_SESSION['user']['id']): ?>
                                    <a href="index.php?page=users_delete&id=<?= (int)$u['id'] ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Supprimer définitivement cet utilisateur ?');">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">Vous</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/back_footer.php'; ?>