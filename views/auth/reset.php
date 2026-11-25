<div class="auth">
    <?php require __DIR__ . '/../layout/brand.php'; ?>
    <section class="auth-form">
        <h1>Nouveau mot de passe</h1>
        <p class="muted">Choisis un mot de passe d'au moins 8 caractères, dont un chiffre.</p>
        <form method="post" action="index.php?r=reset&token=<?= e($token) ?>" novalidate>
            <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">

            <label>Nouveau mot de passe
                <input type="password" name="password" data-rules="required|password">
                <small class="error"><?= e($errors['password'] ?? '') ?></small>
            </label>

            <label>Confirmer
                <input type="password" name="confirm" data-rules="required|same:password">
                <small class="error"><?= e($errors['confirm'] ?? '') ?></small>
            </label>

            <button class="btn btn-full">Enregistrer</button>
        </form>
    </section>
</div>
