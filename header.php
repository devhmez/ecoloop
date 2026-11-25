<?php
// Avant d'inclure : définir $title, $root ('' ou '../') et $active
require_once __DIR__ . '/auth.php';
$root   = $root ?? '';
$active = $active ?? '';
$u      = current_user();
$role   = $u['role'] ?? null;
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'EcoLoop') ?> · EcoLoop</title>
<link rel="stylesheet" href="<?= e($root) ?>assets/style.css">
</head>
<body>
<header class="top">
  <div class="wrap top-in">
    <a class="logo" href="<?= e($root . ($role ? home_for($role) : 'login.php')) ?>">EcoLoop</a>
    <nav>
      <?php if ($role === 'citizen'): ?>
        <a class="<?= $active === 'mine' ? 'on' : '' ?>" href="<?= e($root) ?>index.php">My Collections</a>
        <a class="<?= $active === 'request' ? 'on' : '' ?>" href="<?= e($root) ?>request.php">Request pickup</a>
      <?php elseif ($role === 'driver'): ?>
        <a class="<?= $active === 'route' ? 'on' : '' ?>" href="<?= e($root) ?>driver/index.php">My route</a>
      <?php elseif ($role === 'admin'): ?>
        <a class="<?= $active === 'a-col' ? 'on' : '' ?>" href="<?= e($root) ?>admin/collections.php">Collections</a>
        <a class="<?= $active === 'a-veh' ? 'on' : '' ?>" href="<?= e($root) ?>admin/vehicles.php">Vehicles</a>
        <a class="<?= $active === 'a-usr' ? 'on' : '' ?>" href="<?= e($root) ?>admin/users.php">Users</a>
      <?php endif; ?>
    </nav>
    <?php if ($u): ?>
      <div class="userbox">
        <span><?= e($u['name']) ?> <small>· <?= e($u['role']) ?></small></span>
        <form method="post" action="<?= e($root) ?>logout.php"><?= csrf_field() ?><button class="btn sm ghost light">Log out</button></form>
      </div>
    <?php endif; ?>
  </div>
</header>
<main class="wrap">
<?php foreach (flashes() as $f): ?>
  <div class="flash <?= e($f['kind']) ?>"><?= e($f['msg']) ?></div>
<?php endforeach; ?>
