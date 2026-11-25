<div class="auth">
    <?php require __DIR__ . '/../layout/brand.php'; ?>
    <section class="auth-form">
        <h1>Créer un compte</h1>
        <p class="muted">Rejoins EcoLoop en moins d'une minute.</p>
        <form method="post" action="index.php?r=register" novalidate>
            <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">

            <label>Nom complet
                <input type="text" name="name" value="<?= e($name) ?>" placeholder="Sami Ben Ali" data-rules="required|min:3">
                <small class="error"><?= e($errors['name'] ?? '') ?></small>
            </label>

            <label>Email
                <input type="text" name="email" value="<?= e($email) ?>" placeholder="toi@exemple.tn" data-rules="required|email">
                <small class="error"><?= e($errors['email'] ?? '') ?></small>
            </label>

            <label>Mot de passe
                <input type="password" name="password" placeholder="8 caractères dont un chiffre" data-rules="required|password">
                <small class="error"><?= e($errors['password'] ?? '') ?></small>
            </label>

            <label>Confirmer le mot de passe
                <input type="password" name="confirm" data-rules="required|same:password">
                <small class="error"><?= e($errors['confirm'] ?? '') ?></small>
            </label>

            <button class="btn btn-full">Créer mon compte</button>
        </form>
        <p>Déjà inscrit ? <a href="index.php?r=login">Se connecter</a></p>
    </section>
</div>
