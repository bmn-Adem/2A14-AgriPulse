<?php require _DIR_ . '/../layouts/back_header.php'; ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold">Gestion des Utilisateurs (<?= count($users) ?>)</h5>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle actuel</th>
                    <th>Modifier Rôle</th>
                    <th>Inscription</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td>#<?= $u['id'] ?></td>
                        <td class="fw-semibold"><?= htmlspecialchars($u['nom']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <span class="badge bg-<?= $u['role_nom'] === 'admin' ? 'danger' : ($u['role_nom'] === 'agriculteur' ? 'success' : 'secondary') ?>">
                                <?= htmlspecialchars($u['role_nom']) ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" action="index.php?page=user-role" class="d-flex gap-2">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <select name="role_id" class="form-select form-select-sm" style="width: 140px;">
                                    <?php foreach ($roles as $r): ?>
                                        <option value="<?= $r['id'] ?>" <?= $r['id'] == $u['role_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($r['nom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-outline-primary btn-sm">OK</button>
                            </form>
                        </td>
                        <td class="small text-muted"><?= htmlspecialchars($u['date_inscription']) ?></td>
                        <td>
                            <?php if ($u['id'] != $_SESSION['user']['id']): ?>
                                <a href="index.php?page=user-delete&id=<?= $u['id'] ?>"
                                   class="btn btn-outline-danger btn-sm"
                                   onclick="return confirm('Supprimer cet utilisateur ?');">
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

<?php require _DIR_ . '/../layouts/back_footer.php'; ?>