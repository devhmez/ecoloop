<?php
/* Front Office layout (top). Variables: $title, $active. Swap in your team's template here. */
$active = $active ?? 'report';
$nav = [
    ['overview',  '#',                 'Overview',            'grid'],
    ['scanner',   '#',                 'AI Waste Scanner',    'scan'],
    ['report',    url('report'),       'Report Waste',        'flag'],
    ['collections','#',                'My Collections',      'truck'],
    ['centers',   '#',                 'Recycling Centers',   'pin'],
    ['rewards',   '#',                 'EcoPoints & Rewards', 'gift'],
    ['impact',    '#',                 'Environmental Impact','chart'],
    ['settings',  '#',                 'Settings',            'sliders'],
];
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
<div class="app">
  <?php require __DIR__ . '/sidebar.php'; ?>
  <div class="side-scrim" id="scrim" hidden></div>
  <div class="main">
    <header class="top">
      <div class="top-in gut">
        <button class="icon-btn menu-only" id="menuBtn" type="button" aria-label="Open menu" aria-controls="sideNav"><?= icon('menu') ?></button>
        <div class="top-act">
          <button class="icon-btn" id="themeBtn" type="button" aria-label="Toggle light and dark theme"><?= icon('moon') ?></button>
          <span class="av" title="Amira">AM</span>
        </div>
      </div>
    </header>
    <main class="content gut">
