<?php include __DIR__ . '/../centres/_style.php'; ?>
<div class="top">
    <div>
        <h1><?= $id > 0 ? 'Modifier le centre' : 'Ajouter un centre' ?></h1>
        <p class="muted">Tous les champs sont contrôlés avant l'enregistrement.</p>
    </div>
    <a class="btn btn-light" href="index.php?r=admin/centres">Retour à la liste</a>
</div>

<section class="card">
    <form method="post" action="index.php?r=admin/centre-form<?= $id > 0 ? '&amp;id=' . (int) $id : '' ?>" novalidate>
        <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">

        <label>Nom
            <input type="text" name="nom" value="<?= e($c['nom']) ?>" maxlength="100">
            <span class="error"><?= e($errors['nom'] ?? '') ?></span>
        </label>

        <div class="form-grid">
            <label>Adresse
                <input type="text" name="adresse" value="<?= e($c['adresse']) ?>" maxlength="255">
                <span class="error"><?= e($errors['adresse'] ?? '') ?></span>
            </label>
            <label>Ville
                <input type="text" name="ville" value="<?= e($c['ville']) ?>" maxlength="80">
                <span class="error"><?= e($errors['ville'] ?? '') ?></span>
            </label>
            <label>Horaires
                <input type="text" name="horaires" value="<?= e($c['horaires']) ?>" maxlength="80">
                <span class="error"><?= e($errors['horaires'] ?? '') ?></span>
            </label>
            <label>Téléphone (facultatif)
                <input type="text" name="telephone" value="<?= e($c['telephone'] ?? '') ?>" placeholder="+216 71 000 000">
                <span class="error"><?= e($errors['telephone'] ?? '') ?></span>
            </label>
            <label>Latitude
                <input type="text" name="latitude" value="<?= e((string) $c['latitude']) ?>" inputmode="decimal" placeholder="36.8065">
                <span class="error"><?= e($errors['latitude'] ?? '') ?></span>
            </label>
            <label>Longitude
                <input type="text" name="longitude" value="<?= e((string) $c['longitude']) ?>" inputmode="decimal" placeholder="10.1815">
                <span class="error"><?= e($errors['longitude'] ?? '') ?></span>
            </label>
        </div>

        <div style="margin-bottom:16px">
            <strong>Matières acceptées</strong>
            <div style="margin-top:8px">
                <?php foreach (Centre::MATERIALS as $key => $label): ?>
                    <label class="chip-check">
                        <input type="checkbox" name="materials[]" value="<?= e($key) ?>" <?= in_array($key, $c['materials'], true) ? 'checked' : '' ?>>
                        <?= e($label) ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <span class="error"><?= e($errors['materials'] ?? '') ?></span>
        </div>

        <label class="chip-check">
            <input type="checkbox" name="actif" <?= !empty($c['actif']) ? 'checked' : '' ?>> Centre actif (visible par les citoyens)
        </label>

        <div style="margin-top:12px">
            <button class="btn"><?= $id > 0 ? 'Enregistrer' : 'Ajouter le centre' ?></button>
            <a class="btn btn-light" href="index.php?r=admin/centres">Annuler</a>
        </div>
    </form>
</section>
