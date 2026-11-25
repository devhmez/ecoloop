<?php
// View: all reports (Back Office). Variables: $list, $q, $status
require __DIR__ . '/../layout/header.php';
?>
<div class="a-head"><div><h1>Waste Reports</h1><p class="muted">Review citizen reports and move them through the workflow.</p></div></div>

<div class="acard">
  <div class="acard-h"><h3>All reports</h3>
    <div class="a-filters">
      <form class="search sm-s" method="get" action="index.php">
        <input type="hidden" name="r" value="admin.reports">
        <?= icon('search', 16) ?><input name="q" aria-label="Search reports" placeholder="Search reports" value="<?= e($q) ?>">
        <?php if ($status !== 'All'): ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?>
      </form>
      <div class="fchips" role="group" aria-label="Filter by status">
        <?php foreach (array_merge(['All'], WasteReport::STATUSES) as $s): ?>
          <a class="fchip <?= $status === $s ? 'on' : '' ?>" href="<?= url('admin.reports', ['status' => $s, 'q' => $q]) ?>"><?= e($s) ?> <i><?= WasteReport::countBy($s) ?></i></a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="tbl-wrap">
    <table class="tbl dense">
      <thead><tr><th>ID</th><th>Category</th><th>Location</th><th>Reported by</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($list as $r): ?>
        <tr>
          <td><b><?= e($r['id']) ?></b><div class="muted xs"><?= e($r['date']) ?></div></td>
          <td><?= cat_badge($r['cat'], true) ?></td>
          <td><?= e($r['loc']) ?></td>
          <td><?= e($r['by']) ?></td>
          <td><?= status_badge($r['status']) ?></td>
          <td><a class="btn btn-sm btn-ghost" href="<?= url('admin.report', ['id' => $r['id']]) ?>">Details</a></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$list): ?><tr><td colspan="6" class="muted center pad-s">No reports match this filter.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../layout/footer.php'; ?>
