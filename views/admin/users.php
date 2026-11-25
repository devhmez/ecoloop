<div class="top">
    <div>
        <h1>Utilisateurs</h1>
        <p class="muted">Gère les comptes des citoyens et des administrateurs.</p>
    </div>
    <a class="btn" href="index.php?r=admin/form">+ Ajouter un utilisateur</a>
</div>

<div class="stats">
    <div class="stat"><span>Comptes</span><strong><?= (int) $stats['total'] ?></strong></div>
    <div class="stat"><span>Citoyens</span><strong><?= (int) $stats['citoyens'] ?></strong></div>
    <div class="stat"><span>Admins</span><strong><?= (int) $stats['admins'] ?></strong></div>
    <div class="stat"><span>EcoPoints</span><strong><?= (int) $stats['points'] ?></strong></div>
</div>

<section class="card">
    <form method="get" action="index.php" class="search">
        <input type="hidden" name="r" value="admin/users">
        <input type="text" name="q" value="<?= e($q) ?>" placeholder="Rechercher par nom ou email">
        <button class="btn">Rechercher</button>
    </form>

    <div class="table-wrap">
        <table>
            <tr><th>Utilisateur</th><th>Rôle</th><th>Points</th><th>Inscrit le</th><th></th></tr>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td class="who">
                        <span class="avatar"><?= e(initials($u['name'])) ?></span>
                        <div><?= e($u['name']) ?><small><?= e($u['email']) ?></small></div>
                    </td>
                    <td><span class="badge <?= e($u['role']) ?>"><?= e($u['role']) ?></span></td>
                    <td><?= (int) $u['points'] ?></td>
                    <td><?= e(date('d/m/Y', strtotime($u['created_at']))) ?></td>
                    <td class="actions">
                        <a href="index.php?r=admin/form&id=<?= (int) $u['id'] ?>">Modifier</a>
                        <form method="post" action="index.php?r=admin/delete" class="inline" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                            <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
                            <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                            <button class="link danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($users)): ?>
                <tr><td colspan="5">Aucun utilisateur trouvé.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</section>
