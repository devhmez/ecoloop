<section class="card narrow">
    <div class="profile-head">
        <span class="avatar big"><?= e(initials($me['name'])) ?></span>
        <div>
            <h1><?= e($me['name']) ?></h1>
            <span class="badge <?= e($me['role']) ?>"><?= e($me['role']) ?></span>
        </div>
        <div class="points"><strong><?= (int) $me['points'] ?></strong>EcoPoints</div>
    </div>

    <form method="post" action="index.php?r=profile" novalidate>
        <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">

        <label>Nom complet
            <input type="text" name="name" value="<?= e($me['name']) ?>" data-rules="required|min:3">
            <small class="error"><?= e($errors['name'] ?? '') ?></small>
        </label>

        <label>Email
            <input type="text" name="email" value="<?= e($me['email']) ?>" data-rules="required|email">
            <small class="error"><?= e($errors['email'] ?? '') ?></small>
        </label>

        <button class="btn">Enregistrer</button>
        <a class="btn btn-light" href="index.php?r=password">Changer mon mot de passe</a>
    </form>
</section>
