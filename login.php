<?php
require_once __DIR__ . '/includes/auth.php';

if ($u = current_user()) {
    redirect(home_for($u['role']));
}

$error = null;
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pass  = (string)($_POST['password'] ?? '');
    $now   = time();

    // Anti brute-force simple : 5 échecs max par 5 minutes et par session
    $fails = array_values(array_filter($_SESSION['login_fail'] ?? [], fn($t) => $t > $now - 300));
    $_SESSION['login_fail'] = $fails;

    if (count($fails) >= 5) {
        $error = 'Too many attempts. Try again in a few minutes.';
    } else {
        $st = db()->prepare("SELECT id, password_hash, role, active FROM users WHERE email = ?");
        $st->execute([$email]);
        $row = $st->fetch();
        if ($row && $row['active'] && password_verify($pass, $row['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['uid'] = (int)$row['id'];
            unset($_SESSION['login_fail']);
            redirect(home_for($row['role']));
        }
        $_SESSION['login_fail'][] = $now;
        $error = 'Invalid email or password.';
    }
}

$title = 'Log in'; $active = ''; $root = '';
include __DIR__ . '/includes/header.php';
?>
<div class="login-box card">
  <h1>Log in</h1>
  <p class="muted">Citizens, drivers and admins use the same login.</p>
  <?php if ($error): ?><div class="flash bad"><?= e($error) ?></div><?php endif; ?>
  <form method="post" novalidate>
    <?= csrf_field() ?>
    <div class="field"><label for="email">Email</label>
      <input id="email" name="email" type="email" value="<?= e($email) ?>" autocomplete="username" autofocus></div>
    <div class="field"><label for="password">Password</label>
      <input id="password" name="password" type="password" autocomplete="current-password"></div>
    <button class="btn primary" style="width:100%">Log in</button>
  </form>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
