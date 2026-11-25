<div class="auth">
    <?php require __DIR__ . '/../layout/brand.php'; ?>
    <section class="auth-form">
        <h1>Bon retour !</h1>
        <p class="muted">Connecte-toi pour voir tes EcoPoints.</p>
        <form method="post" action="index.php?r=login" novalidate>
            <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
            <?php if (isset($errors['login'])): ?>
                <p class="alert"><?= e($errors['login']) ?></p>
            <?php endif; ?>

            <label>Email
                <input type="text" name="email" value="<?= e($email) ?>" placeholder="toi@exemple.tn" data-rules="required|email">
                <small class="error"></small>
            </label>

            <label>Mot de passe
                <input type="password" name="password" data-rules="required">
                <small class="error"></small>
            </label>

            <button class="btn btn-full">Se connecter</button>
        </form>
        <p><a href="index.php?r=forgot">Mot de passe oublié ?</a></p>
        <p>Pas encore de compte ? <a href="index.php?r=register">S'inscrire</a></p>
    </section>
</div>
