<div class="top">
    <div>
        <h1>Matériaux</h1>
        <p class="muted">Les mots-clés relient ce que voit l'IA au bon matériau.</p>
    </div>
    <a class="btn" href="index.php?r=admin/materiau-form">+ Ajouter un matériau</a>
</div>

<section class="card">
    <div class="table-wrap">
        <table>
            <tr><th>Matériau</th><th>Poubelle</th><th>Points</th><th>Mots-clés de l'IA</th><th>Scans</th><th></th></tr>
            <?php foreach ($materiaux as $m): ?>
                <tr>
                    <td><span class="dot" style="background: <?= e($m['couleur']) ?>"></span> <strong><?= e($m['nom']) ?></strong></td>
                    <td><?= e($m['poubelle']) ?></td>
                    <td><?= (int) $m['points'] ?></td>
                    <td class="keywords"><?= e($m['mots_cles']) ?></td>
                    <td><?= (int) $m['nb_scans'] ?></td>
                    <td class="actions">
                        <a href="index.php?r=admin/materiau-form&id=<?= (int) $m['id'] ?>">Modifier</a>
                        <form method="post" action="index.php?r=admin/materiau-delete" class="inline" onsubmit="return confirm('Supprimer ce matériau ?')">
                            <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
                            <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                            <button class="link danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</section>
