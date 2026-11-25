<section class="card narrow">
    <h1><?= $id > 0 ? 'Modifier un utilisateur' : 'Ajouter un utilisateur' ?></h1>
    <p class="muted"><?= $id > 0 ? 'Change le nom, l\'email ou le rôle.' : 'Crée un compte citoyen ou administrateur.' ?></p>
    <form method="post" action="index.php?r=admin/form&id=<?= $id ?>" novalidate>
        <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">

        <label>Nom complet
            <input type="text" name="name" value="<?= e($u['name']) ?>" data-rules="required|min:3">
            <small class="error"><?= e($errors['name'] ?? '') ?></small>
        </label>

        <label>Email
            <input type="text" name="email" value="<?= e($u['email']) ?>" data-rules="required|email">
            <small class="error"><?= e($errors['email'] ?? '') ?></small>
        </label>

        <label>Rôle
            <select name="role">
                <option value="citoyen" <?= $u['role'] === 'citoyen' ? 'selected' : '' ?>>Citoyen</option>
                <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Administrateur</option>
            </select>
        </label>

        <?php if ($id === 0): ?>
            <label>Mot de passe
                <input type="password" name="password" data-rules="required|password">
                <small class="error"><?= e($errors['password'] ?? '') ?></small>
            </label>
        <?php endif; ?>

        <button class="btn">Enregistrer</button>
        <a class="btn btn-light" href="index.php?r=admin/users">Annuler</a>
    </form>
</section>
