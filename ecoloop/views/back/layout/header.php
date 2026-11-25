<?php /* Back Office layout (top). Variables: $title, $flash. Swap in your team's template here. */ ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?> · EcoLoop Back Office</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@500;600;700&display=swap">
  <link rel="stylesheet" href="assets/css/ecoloop.css">
  <link rel="stylesheet" href="assets/css/extra.css">
</head>
<body>
<div class="adm">
  <?php require __DIR__ . '/sidebar.php'; ?>
  <div class="side-scrim" id="scrim" hidden></div>
  <div class="a-main">
    <header class="a-top">
      <div class="a-top-in gut">
        <button class="icon-btn menu-only" id="menuBtn" type="button" aria-label="Open menu" aria-controls="sideNav"><?= icon('menu') ?></button>
        <div class="crumb"><span>Back office</span><?= icon('chevron', 14) ?><b><?= e($title) ?></b></div>
        <div class="top-act"><span class="badge b-warn"><i></i>Demo data</span>
          <button class="icon-btn" id="themeBtn" type="button" aria-label="Toggle light and dark theme"><?= icon('moon') ?></button><span class="av">AD</span></div>
      </div>
    </header>
    <main class="a-content gut">
      <?php if (!empty($flash)): ?><div class="flash" role="status"><?= e($flash) ?></div><?php endif; ?>
