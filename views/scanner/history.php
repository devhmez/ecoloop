<div class="top">
    <div>
        <h1>Mes scans</h1>
        <p class="muted">Tous les déchets que tu as scannés et les points gagnés.</p>
    </div>
    <a class="btn" href="index.php?r=scanner">+ Nouveau scan</a>
</div>

<section class="card">
    <div class="table-wrap">
        <table>
            <tr><th>Objet</th><th>Matériau</th><th>Poubelle</th><th>Confiance</th><th>Points</th><th>Date</th></tr>
            <?php foreach ($scans as $s): ?>
                <tr>
                    <td><?= e($s['objet']) ?></td>
                    <td><span class="dot" style="background: <?= e($s['couleur']) ?>"></span> <?= e($s['materiau']) ?></td>
                    <td><?= e($s['poubelle']) ?></td>
                    <td><?= (int) $s['confiance'] ?> %</td>
                    <td>+<?= (int) $s['points'] ?></td>
                    <td><?= e(date('d/m/Y H:i', strtotime($s['created_at']))) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($scans)): ?>
                <tr><td colspan="6">Aucun scan pour l'instant. <a href="index.php?r=scanner">Scanne ton premier déchet</a></td></tr>
            <?php endif; ?>
        </table>
    </div>
</section>
