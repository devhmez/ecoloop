<section class="card narrow">
    <h1><?= $id > 0 ? 'Modifier un matériau' : 'Ajouter un matériau' ?></h1>
    <p class="muted">Les mots-clés sont en anglais, comme les réponses de l'IA, séparés par des virgules.</p>
    <form method="post" action="index.php?r=admin/materiau-form&id=<?= $id ?>" novalidate>
        <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">

        <label>Nom
            <input type="text" name="nom" value="<?= e($m['nom']) ?>" placeholder="Plastique" data-rules="required|min:2">
            <small class="error"><?= e($errors['nom'] ?? '') ?></small>
        </label>

        <label>Poubelle
            <input type="text" name="poubelle" value="<?= e($m['poubelle']) ?>" placeholder="Poubelle jaune" data-rules="required|min:3">
            <small class="error"><?= e($errors['poubelle'] ?? '') ?></small>
        </label>

        <label>Couleur
            <input type="color" name="couleur" value="<?= e($m['couleur']) ?>">
            <small class="error"><?= e($errors['couleur'] ?? '') ?></small>
        </label>

        <label>Points gagnés par scan
            <input type="text" name="points" value="<?= e((string) $m['points']) ?>" data-rules="required|between:0,50">
            <small class="error"><?= e($errors['points'] ?? '') ?></small>
        </label>

        <label>Mots-clés de l'IA
            <textarea name="mots_cles" rows="3" placeholder="water bottle, plastic bag" data-rules="required|min:3"><?= e($m['mots_cles']) ?></textarea>
            <small class="error"><?= e($errors['mots_cles'] ?? '') ?></small>
        </label>

        <label>Conseil de tri
            <input type="text" name="conseil" value="<?= e($m['conseil']) ?>" data-rules="required|min:5">
            <small class="error"><?= e($errors['conseil'] ?? '') ?></small>
        </label>

        <button class="btn">Enregistrer</button>
        <a class="btn btn-light" href="index.php?r=admin/materiaux">Annuler</a>
    </form>
</section>
