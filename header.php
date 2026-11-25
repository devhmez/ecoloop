<?php
/** Variables attendues : $pageTitle, $area ('app'|'admin'), $active (clé du menu) */
require_once __DIR__ . '/functions.php';
$user = current_user();
$area = $area ?? 'app';
$active = $active ?? '';
$nav = $area === 'admin'
  ? [['backoffice/index.php','dash','Tableau de bord','grid'],['backoffice/recompenses.php','rec','Récompenses','gift'],['backoffice/echanges.php','ech','Échanges','truck']]
  : [['frontoffice/recompenses.php','rec','EcoPoints & récompenses','gift'],['frontoffice/mes_echanges.php','ech','Mes échanges','truck']];
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle ?? 'EcoLoop') ?> · EcoLoop</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@500;600;700&display=swap">
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body class="<?= $area === 'admin' ? 'is-admin' : '' ?>">
<div class="app">
  <aside class="side" id="side">
    <a class="logo" href="<?= url($area === 'admin' ? 'backoffice/index.php' : 'frontoffice/recompenses.php') ?>">
      <svg width="38" height="38" viewBox="0 0 48 48" aria-hidden="true"><rect width="48" height="48" rx="14" fill="#123D35"/><path d="M24 6A18 18 0 0 1 39.6 15" fill="none" stroke="#A8E063" stroke-width="3.6" stroke-linecap="round"/><polygon points="42.1,19.3 43.1,13 36.1,17" fill="#A8E063"/><path d="M24 42A18 18 0 0 1 8.4 33" fill="none" stroke="#2D8C74" stroke-width="3.6" stroke-linecap="round"/><polygon points="5.9,28.7 4.9,35 11.9,31" fill="#2D8C74"/><path d="M17 31c0-9 5-14 14-15 0 9-4 15-12 15-1 0-2 0-2 0z" fill="#A8E063"/></svg>
      <span class="logo-word">Eco<span>Loop</span></span>
    </a>
    <?php if ($area === 'admin'): ?><span class="tag-admin">Back office</span><?php endif; ?>
    <nav class="side-nav" aria-label="Navigation">
      <?php foreach ($nav as [$href, $key, $label, $ic]): ?>
        <a class="nav-i <?= $active === $key ? 'on' : '' ?>" href="<?= url($href) ?>"><?= icon($ic, 20) ?> <?= e($label) ?></a>
      <?php endforeach; ?>
    </nav>
    <?php if ($user && $area === 'app'): ?>
      <div class="side-pts"><span class="xs">Mes EcoPoints</span><b><?= icon('star', 18) ?> <?= fmt($user['points']) ?></b></div>
    <?php endif; ?>
    <div class="side-foot">
      <?php if ($user && $user['role'] === 'admin'): ?>
        <a class="nav-i sm" href="<?= url($area === 'admin' ? 'frontoffice/recompenses.php' : 'backoffice/index.php') ?>"><?= icon('home', 18) ?> <?= $area === 'admin' ? 'Front office' : 'Back office' ?></a>
      <?php endif; ?>
      <?php if ($user): ?><a class="nav-i sm" href="<?= url('logout.php') ?>"><?= icon('logout', 18) ?> Déconnexion</a><?php endif; ?>
    </div>
  </aside>
  <div class="main">
    <header class="top"><div class="top-in gut">
      <button class="icon-btn menu-btn" type="button" id="menuBtn" aria-label="Ouvrir le menu"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg></button>
      <div class="top-act">
        <?php if ($user): ?>
          <div class="prof"><span class="av"><?= e(initials($user['nom'])) ?></span><span class="prof-t"><b><?= e($user['nom']) ?></b><span><?= $user['role'] === 'admin' ? 'Administrateur' : e($user['quartier'] ?? 'Citoyen') ?></span></span></div>
        <?php endif; ?>
      </div>
    </div></header>
    <main class="content gut">
      <?php foreach (get_flashes() as $f): ?>
        <div class="flash <?= e($f['t']) ?>" role="status"><?= e($f['m']) ?><button type="button" class="flash-x" aria-label="Fermer">×</button></div>
      <?php endforeach; ?>
