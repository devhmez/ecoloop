<?php
// View: login / register. Variables: $mode, $title, $values, $errors, $csrf, $flash
$login = $mode === 'login';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?> · EcoLoop</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@500;600;700&display=swap">
  <link rel="stylesheet" href="assets/css/ecoloop.css">
  <link rel="stylesheet" href="assets/css/extra.css">
</head>
<body>
<main style="min-height:100vh;display:grid;place-items:center;padding:24px">
  <div class="card pad stack" style="width:min(420px,100%)">
    <a class="logo" href="<?= url('login') ?>"><?= logo(40) ?></a>
    <div>
      <h1 style="font-size:26px"><?= $login ? 'Welcome back' : 'Create your account' ?></h1>
      <p class="muted"><?= $login ? 'Log in to report waste and follow your reports.' : 'Join EcoLoop and start reporting waste in your city.' ?></p>
    </div>
    <?php if ($flash): ?><div class="flash" role="status"><?= e($flash) ?></div><?php endif; ?>
    <?php if (isset($errors['form'])): ?><div class="flash bad" role="alert"><?= e($errors['form']) ?></div><?php endif; ?>

    <form class="stack" method="post" action="<?= url($mode) ?>" novalidate>
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <?php if (!$login): ?>
        <div class="field"><label for="name">Name</label>
          <input id="name" name="name" value="<?= e($values['name']) ?>" autocomplete="name">
          <?php if (isset($errors['name'])): ?><p class="err" role="alert"><?= e($errors['name']) ?></p><?php endif; ?></div>
      <?php endif; ?>
      <div class="field"><label for="email">Email</label>
        <input id="email" name="email" type="email" value="<?= e($values['email']) ?>" autocomplete="email">
        <?php if (isset($errors['email'])): ?><p class="err" role="alert"><?= e($errors['email']) ?></p><?php endif; ?></div>
      <div class="field"><label for="password">Password</label>
        <input id="password" name="password" type="password" autocomplete="<?= $login ? 'current-password' : 'new-password' ?>">
        <?php if (isset($errors['password'])): ?><p class="err" role="alert"><?= e($errors['password']) ?></p><?php endif; ?></div>
      <?php if (!$login): ?>
        <div class="field"><label for="confirm">Confirm password</label>
          <input id="confirm" name="confirm" type="password" autocomplete="new-password">
          <?php if (isset($errors['confirm'])): ?><p class="err" role="alert"><?= e($errors['confirm']) ?></p><?php endif; ?></div>
      <?php endif; ?>
      <button class="btn btn-primary btn-block" type="submit"><?= $login ? 'Log in' : 'Create account' ?></button>
    </form>

    <p class="muted sm center">
      <?php if ($login): ?>No account yet? <a class="link" href="<?= url('register') ?>">Create one</a>
      <?php else: ?>Already registered? <a class="link" href="<?= url('login') ?>">Log in</a><?php endif; ?>
    </p>
  </div>
</main>
</body>
</html>