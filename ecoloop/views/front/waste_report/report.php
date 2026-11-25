<?php
// View: Report Waste (Front Office). Variables: $values, $errors, $mine, $csrf, $flash
$active = 'report';
require __DIR__ . '/../layout/header.php';
?>
<?php if ($flash): ?><div class="flash" role="status"><?= e($flash) ?></div><?php endif; ?>

<div class="ph"><div><h1>Report Waste</h1><p class="muted">Spotted an overflowing bin or dumped items? Tell the city team.</p></div></div>

<form class="grid two gap-top" method="post" action="<?= url('report') ?>" novalidate>
  <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
  <div class="card pad stack">
    <div class="field">
      <span class="label" style="margin:0">Waste category</span>
      <div class="chips">
        <?php foreach (WasteReport::CATEGORIES as $key => $c): ?>
          <label class="cat" style="--c:<?= $c['color'] ?>">
            <input type="radio" name="cat" value="<?= $key ?>" <?= $values['cat'] === $key ? 'checked' : '' ?>><?= icon($c['icon'], 16) ?> <?= e($c['label']) ?>
          </label>
        <?php endforeach; ?>
      </div>
      <?php if (isset($errors['cat'])): ?><p class="err" role="alert"><?= e($errors['cat']) ?></p><?php endif; ?>
    </div>
    <div class="field">
      <label for="photo">Photo</label>
      <input id="photo" type="file" accept="image/*" data-preview="reportPreview">
      <p class="hint">Optional. Preview only, not uploaded in this demo.</p>
      <div class="preview-box" id="reportPreview" hidden><img alt="Selected waste preview" src=""></div>
    </div>
    <div class="field">
      <label for="desc">Description</label>
      <textarea id="desc" name="desc" rows="3" placeholder="What did you see, and how urgent is it?"><?= e($values['desc']) ?></textarea>
      <?php if (isset($errors['desc'])): ?><p class="err" role="alert"><?= e($errors['desc']) ?></p><?php endif; ?>
    </div>
  </div>

  <div class="card pad stack">
    <div class="field">
      <label for="loc">Location</label>
      <input id="loc" name="loc" value="<?= e($values['loc']) ?>" placeholder="Street, landmark or district">
      <?php if (isset($errors['loc'])): ?><p class="err" role="alert"><?= e($errors['loc']) ?></p><?php endif; ?>
    </div>
    <div class="field">
      <span class="label" style="margin:0">Collection preference</span>
      <div class="radios">
        <?php foreach (WasteReport::PREFERENCES as $p): ?>
          <label class="radio <?= $values['pref'] === $p ? 'on' : '' ?>">
            <input type="radio" name="pref" value="<?= e($p) ?>" <?= $values['pref'] === $p ? 'checked' : '' ?>><?= e($p) ?>
          </label>
        <?php endforeach; ?>
      </div>
      <?php if (isset($errors['pref'])): ?><p class="err" role="alert"><?= e($errors['pref']) ?></p><?php endif; ?>
    </div>
    <button class="btn btn-primary btn-block" type="submit"><?= icon('flag', 18) ?> Submit Report</button>
  </div>
</form>

<div class="card pad gap-top">
  <div class="card-h"><h3>My reports</h3><span class="muted sm"><?= count($mine) ?> in this demo session</span></div>
  <div class="tbl-wrap">
    <table class="tbl">
      <thead><tr><th>ID</th><th>Category</th><th>Location</th><th>Preference</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach ($mine as $r): ?>
        <tr>
          <td><b><?= e($r['id']) ?></b><div class="muted xs"><?= e($r['date']) ?></div></td>
          <td><?= cat_badge($r['cat'], true) ?></td>
          <td><?= e($r['loc']) ?></td>
          <td><?= e($r['pref']) ?></td>
          <td><?= status_badge($r['status']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../layout/footer.php'; ?>
