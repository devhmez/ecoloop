<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EcoLoop</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="nav">
    <a class="logo" href="index.php"><span class="logo-mark">♻</span> EcoLoop</a>
    <nav>
        <?php if (currentUser()): ?>
            <?php if (currentUser()['role'] === 'admin'): ?>
                <a href="index.php?r=admin/users">Utilisateurs</a>
            <?php endif; ?>
            <a href="index.php?r=profile">Mon profil</a>
            <form method="post" action="index.php?r=logout" class="inline">
                <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
                <button class="link">Déconnexion</button>
            </form>
            <span class="me"><span class="avatar"><?= e(initials(currentUser()['name'])) ?></span><span class="me-name"><?= e(currentUser()['name']) ?></span></span>
        <?php else: ?>
            <a href="index.php?r=login">Connexion</a>
            <a class="btn" href="index.php?r=register">Inscription</a>
        <?php endif; ?>
    </nav>
</header>
<main>
    <?php if ($message = flash()): ?>
        <p class="flash"><?= e($message) ?></p>
    <?php endif; ?>
