<?php
include __DIR__ . '/../centres/_style.php';
// Titre de colonne cliquable pour trier
$th = function (string $label, string $key) use ($f, $url): string {
    $dir = ($f['sort'] === $key && $f['dir'] === 'asc') ? 'desc' : 'asc';
    $arrow = $f['sort'] === $key ? ($f['dir'] === 'asc' ? ' ▲' : ' ▼') : '';
    return '<a href="' . e($url(['sort' => $key, 'dir' => $dir, 'page' => null])) . '">' . e($label) . $arrow . '</a>';
};
?>
<div class="top">
    <div>
        <h1>Centres de recyclage</h1>
        <p class="muted">Ajoute, modifie, supprime et exporte les centres du réseau.</p>
    </div>
    <div>
        <a class="btn btn-light" href="<?= e($exportUrl) ?>">Exporter en CSV</a>
        <a class="btn" href="index.php?r=admin/centre-form">+ Ajouter un centre</a>
    </div>
</div>

<section class="card">
    <form method="get" action="index.php" class="search">
        <input type="hidden" name="r" value="admin/centres">
        <input type="text" name="q" value="<?= e($f['q']) ?>" placeholder="Rechercher par nom, ville ou adresse">
        <select name="cat" aria-label="Matière">
            <option value="">Toutes les matières</option>
            <?php foreach (Centre::MATERIALS as $key => $label): ?>
                <option value="<?= e($key) ?>" <?= $f['cat'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="status" aria-label="Statut">
            <option value="">Tous les statuts</option>
            <option value="actif" <?= $f['status'] === 'actif' ? 'selected' : '' ?>>Actifs</option>
            <option value="inactif" <?= $f['status'] === 'inactif' ? 'selected' : '' ?>>Inactifs</option>
        </select>
        <button class="btn">Filtrer</button>
    </form>

    <div class="table-wrap">
        <table>
            <tr>
                <th><?= $th('Centre', 'nom') ?></th>
                <th><?= $th('Ville', 'ville') ?></th>
                <th>Matières</th>
                <th>Statut</th>
                <th></th>
            </tr>
            <?php foreach ($p['items'] as $c): ?>
                <tr>
                    <td><?= e($c['nom']) ?><br><small class="muted"><?= e($c['horaires']) ?></small></td>
                    <td><?= e($c['ville']) ?></td>
                    <td><div class="chips" style="margin:0">
                        <?php foreach ($c['materials'] as $m): ?><span class="chip"><?= e(Centre::MATERIALS[$m] ?? $m) ?></span><?php endforeach; ?>
                    </div></td>
                    <td><span class="badge <?= $c['actif'] ? 'actif' : 'inactif' ?>"><?= $c['actif'] ? 'Actif' : 'Inactif' ?></span></td>
                    <td class="actions">
                        <a href="index.php?r=admin/centre-form&amp;id=<?= (int) $c['id'] ?>">Modifier</a>
                        <form method="post" action="index.php?r=admin/centre-delete" class="inline" onsubmit="return confirm('Supprimer ce centre ?')">
                            <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
                            <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                            <button class="link danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($p['items'])): ?>
                <tr><td colspan="5">Aucun centre trouvé.</td></tr>
            <?php endif; ?>
        </table>
    </div>
    <p class="muted" style="margin:12px 0 0"><?= (int) $p['total'] ?> centre(s) · page <?= (int) $p['page'] ?> sur <?= (int) $p['pages'] ?></p>
    <?php include __DIR__ . '/../centres/_pager.php'; ?>
</section>
