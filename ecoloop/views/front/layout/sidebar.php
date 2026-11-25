<?php /* Citizen sidebar. Variables: $nav, $active */ ?>
<aside class="side" id="sideNav" aria-label="Main navigation">
  <div class="side-top">
    <a class="logo" href="<?= url('report') ?>"><?= logo(40) ?></a>
    <button class="icon-btn menu-only" id="menuClose" type="button" aria-label="Close menu"><?= icon('x') ?></button>
  </div>
  <nav class="side-nav">
    <?php foreach ($nav as [$key, $href, $label, $ic]): ?>
      <a class="nav-i <?= $active === $key ? 'on' : '' ?>" href="<?= e($href) ?>"<?= $active === $key ? ' aria-current="page"' : '' ?>><?= icon($ic, 20) ?><?= e($label) ?></a>
    <?php endforeach; ?>
  </nav>
  <div class="side-pts">
    <span class="muted sm">EcoPoints</span>
    <b><?= icon('star', 22) ?> 850</b>
    <a class="link" href="#">Rewards <?= icon('arrowRight', 16) ?></a>
  </div>
  <div class="side-foot">
    <a class="nav-i sm" href="#"><?= icon('home', 18) ?> Back to website</a>
    <a class="nav-i sm" href="<?= url('admin.reports') ?>"><?= icon('building', 18) ?> Admin back office</a>
  </div>
</aside>
