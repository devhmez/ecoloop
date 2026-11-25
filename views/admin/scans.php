<div class="top">
    <div>
        <h1>Scans</h1>
        <p class="muted">Tous les déchets scannés par les citoyens.</p>
    </div>
</div>

<div class="stats">
    <?php foreach ($materiaux as $m): ?>
        <div class="stat"><span><span class="dot" style="background: <?= e($m['couleur']) ?>"></span> <?= e($m['nom']) ?></span><strong><?= (int) $m['nb_scans'] ?></strong></div>
    <?php endforeach; ?>
</div>

<section class="card">
    <form method="get" action="index.php" class="search">
        <input type="hidden" name="r" value="admin/scans">
        <select name="materiau">
            <option value="0">Tous les matériaux</option>
            <?php foreach ($materiaux as $m): ?>
                <option value="<?= (int) $m['id'] ?>" <?= $filtre === (int) $m['id'] ? 'selected' : '' ?>><?= e($m['nom']) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn">Filtrer</button>
    </form>

    <div class="table-wrap">
        <table>
            <tr><th>Citoyen</th><th>Objet</th><th>Matériau</th><th>Confiance</th><th>Points</th><th>Date</th><th></th></tr>
            <?php foreach ($scans as $s): ?>
                <tr>
                    <td><?= e($s['citoyen']) ?></td>
                    <td><?= e($s['objet']) ?></td>
                    <td><span class="dot" style="background: <?= e($s['couleur']) ?>"></span> <?= e($s['materiau']) ?></td>
                    <td><?= (int) $s['confiance'] ?> %</td>
                    <td>+<?= (int) $s['points'] ?></td>
                    <td><?= e(date('d/m/Y H:i', strtotime($s['created_at']))) ?></td>
                    <td class="actions">
                        <form method="post" action="index.php?r=admin/scan-delete" class="inline" onsubmit="return confirm('Supprimer ce scan et retirer ses points ?')">
                            <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
                            <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                            <button class="link danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($scans)): ?>
                <tr><td colspan="7">Aucun scan.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</section>
