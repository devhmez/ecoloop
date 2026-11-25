<section class="card narrow">
    <h1>Changer mon mot de passe</h1>
    <p class="muted">Pour ta sécurité, entre d'abord ton mot de passe actuel.</p>
    <form method="post" action="index.php?r=password" novalidate>
        <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">

        <label>Mot de passe actuel
            <input type="password" name="current" data-rules="required">
            <small class="error"><?= e($errors['current'] ?? '') ?></small>
        </label>

        <label>Nouveau mot de passe
            <input type="password" name="password" data-rules="required|password">
            <small class="error"><?= e($errors['password'] ?? '') ?></small>
        </label>

        <label>Confirmer
            <input type="password" name="confirm" data-rules="required|same:password">
            <small class="error"><?= e($errors['confirm'] ?? '') ?></small>
        </label>

        <button class="btn">Changer</button>
        <a class="btn btn-light" href="index.php?r=profile">Annuler</a>
    </form>
</section>
