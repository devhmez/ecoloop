<?php
$groups = [
    'Operations' => [['reports', url('admin.reports'), 'Waste Reports', 'flag'], ['collections', '#', 'Collections', 'truck'], ['centers', '#', 'Recycling Centers', 'recycle']],
    'Community'  => [['users', '#', 'Users', 'users'], ['rewards', '#', 'Rewards', 'gift']],
    'Insights'   => [['analytics', '#', 'AI Analytics', 'cpu'], ['env', '#', 'Environmental Reports', 'chart']],
];
$active = 'reports';
?>
<aside class="a-side" id="sideNav" aria-label="Back Office navigation">
  <div class="a-brand"><a class="logo" href="<?= url('admin.reports') ?>"><?= logo(34, true) ?></a>
    <button class="icon-btn menu-only on-dark" id="menuClose" type="button" aria-label="Close menu"><?= icon('x') ?></button></div>
  <span class="a-env">Back office</span>
  <nav class="a-nav">
    <?php foreach ($groups as $g => $items): ?>
      <div><p class="a-g"><?= e($g) ?></p>
        <?php foreach ($items as [$key, $href, $label, $ic]): ?>
          <a class="a-i <?= $active === $key ? 'on' : '' ?>" href="<?= e($href) ?>"<?= $active === $key ? ' aria-current="page"' : '' ?>><?= icon($ic, 18) ?><?= e($label) ?></a>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  </nav>
  <div class="a-foot">
    <a class="a-i" href="<?= url('report') ?>"><?= icon('user', 18) ?> Citizen app</a>
    <a class="a-i" href="#"><?= icon('logout', 18) ?> Exit to website</a>
  </div>
</aside>
