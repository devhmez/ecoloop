<div class="auth">
    <?php require __DIR__ . '/../layout/brand.php'; ?>
    <section class="auth-form">
        <h1>Mot de passe oublié</h1>
        <?php if ($link): ?>
            <p class="muted">Voici ton lien de réinitialisation, valable 30 minutes.</p>
            <a class="btn btn-full" href="<?= e($link) ?>">Choisir un nouveau mot de passe</a>
            <p class="muted">Démo : sur un vrai site, ce lien serait envoyé par email.</p>
        <?php else: ?>
            <p class="muted">Entre ton email, on te donne un lien pour en choisir un nouveau.</p>
            <form method="post" action="index.php?r=forgot" novalidate>
                <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
                <label>Email
                    <input type="text" name="email" placeholder="toi@exemple.tn" data-rules="required|email">
                    <small class="error"><?= e($errors['email'] ?? '') ?></small>
                </label>
                <button class="btn btn-full">Recevoir un lien</button>
            </form>
        <?php endif; ?>
        <p><a href="index.php?r=login">← Retour à la connexion</a></p>
    </section>
</div>
