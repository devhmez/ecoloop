<?php
require_once __DIR__ . '/../includes/functions.php';
$user = require_login();
$pdo = db();

/* ---- Filtres du catalogue (GET) ---- */
$q   = trim($_GET['q'] ?? '');
$cat = (int)($_GET['cat'] ?? 0);
$tri = $_GET['tri'] ?? 'cout_asc';
$ordres = ['cout_asc' => 'r.cout_points ASC', 'cout_desc' => 'r.cout_points DESC', 'nom' => 'r.nom ASC'];
$orderBy = $ordres[$tri] ?? $ordres['cout_asc'];

$sql = 'SELECT r.*, c.nom AS categorie FROM recompenses r LEFT JOIN categories_recompenses c ON c.id = r.categorie_id WHERE r.actif = 1';
$params = [];
if ($q !== '')  { $sql .= ' AND (r.nom LIKE ? OR r.description LIKE ?)'; $params[] = "%$q%"; $params[] = "%$q%"; }
if ($cat > 0)   { $sql .= ' AND r.categorie_id = ?'; $params[] = $cat; }
$sql .= " ORDER BY $orderBy";
$stmt = $pdo->prepare($sql); $stmt->execute($params);
$recompenses = $stmt->fetchAll();
$categories = $pdo->query('SELECT id, nom FROM categories_recompenses ORDER BY nom')->fetchAll();

/* ---- Prochaine récompense à débloquer ---- */
$s = $pdo->prepare('SELECT * FROM recompenses WHERE actif = 1 AND stock > 0 AND cout_points > ? ORDER BY cout_points ASC LIMIT 1');
$s->execute([$user['points']]);
$next = $s->fetch();

/* ---- Classement (top 5 + position de l'utilisateur) ---- */
$top = $pdo->query('SELECT id, nom, points, rang FROM v_classement ORDER BY rang, nom LIMIT 5')->fetchAll();
$moi = null;
foreach ($top as $t) { if ((int)$t['id'] === (int)$user['id']) $moi = $t; }
if (!$moi && $user['role'] === 'citoyen') {
    $s = $pdo->prepare('SELECT id, nom, points, rang FROM v_classement WHERE id = ?'); $s->execute([$user['id']]);
    if ($row = $s->fetch()) $top[] = $row;
}
$maxPts = max(1, ...array_map(fn($t) => (int)$t['points'], $top ?: [['points' => 1]]));

/* ---- Badges ---- */
$s = $pdo->prepare('SELECT b.*, COALESCE(ub.progression,0) AS progression, ub.date_obtention
                    FROM badges b LEFT JOIN utilisateurs_badges ub ON ub.badge_id = b.id AND ub.utilisateur_id = ? ORDER BY b.id');
$s->execute([$user['id']]);
$badges = $s->fetchAll();
$obtenus = count(array_filter($badges, fn($b) => (int)$b['progression'] >= 100));

$pageTitle = 'EcoPoints & récompenses'; $area = 'app'; $active = 'rec';
require __DIR__ . '/../includes/header.php';
?>
<div class="ph"><div><h1>EcoPoints & récompenses</h1><p class="muted">Gagne des points en triant bien, échange-les contre des objets utiles.</p></div></div>

<div class="grid two">
  <div class="wallet">
    <div class="wallet-top"><span>Portefeuille EcoPoints</span><?= icon('star', 22) ?></div>
    <div class="wallet-n"><?= fmt($user['points']) ?><small> pts</small></div>
    <?php if ($next): ?>
      <div class="wallet-l"><span>Prochaine récompense : <?= e($next['nom']) ?></span><b><?= fmt($next['cout_points'] - $user['points']) ?> pts restants</b></div>
      <div class="progress lime"><div style="width:<?= min(100, round($user['points'] / $next['cout_points'] * 100)) ?>%"></div></div>
    <?php else: ?><div class="wallet-l"><span>Tu peux t'offrir toutes les récompenses disponibles.</span></div><?php endif; ?>
  </div>
  <div class="card pad">
    <div class="card-h"><h3>Classement</h3><span class="muted sm">Citoyens actifs</span></div>
    <ol class="board">
      <?php foreach ($top as $t): $me = (int)$t['id'] === (int)$user['id']; ?>
        <li class="<?= $me ? 'me' : '' ?>"><span class="rk"><?= (int)$t['rang'] ?></span><span class="av sm"><?= e(initials($t['nom'])) ?></span><b><?= $me ? 'Toi (' . e(explode(' ', $t['nom'])[0]) . ')' : e($t['nom']) ?></b>
          <div class="bar"><div style="width:<?= round($t['points'] / $maxPts * 100) ?>%"></div></div><em><?= fmt($t['points']) ?></em></li>
      <?php endforeach; ?>
    </ol>
  </div>
</div>

<div class="card pad gap-top">
  <div class="card-h"><h3>Succès</h3><span class="muted sm"><?= $obtenus ?> sur <?= count($badges) ?> obtenus</span></div>
  <div class="grid g3">
    <?php foreach ($badges as $b): $done = (int)$b['progression'] >= 100; ?>
      <div class="badge-c <?= $done ? 'earned' : 'locked' ?>">
        <span class="bc-ic"><?= icon($b['icone'], 22) ?></span>
        <div><b><?= e($b['titre']) ?></b><span class="muted sm"><?= e($b['description']) ?></span>
          <?php if (!$done): ?><div class="progress"><div style="width:<?= (int)$b['progression'] ?>%"></div></div><?php endif; ?></div>
        <?php if ($done): ?><span class="bc-ok"><?= icon('check', 18) ?></span><?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<div class="sec-h-s gap-top"><h2>Catalogue de récompenses</h2><p class="muted">Aucun paiement : seuls tes EcoPoints sont utilisés.</p></div>
<form class="filters" method="get" style="margin-bottom:16px">
  <div class="search"><?= icon('search', 18) ?><input type="search" name="q" placeholder="Rechercher une récompense" aria-label="Rechercher" value="<?= e($q) ?>"></div>
  <select name="cat" aria-label="Catégorie"><option value="0">Toutes les catégories</option>
    <?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>" <?= $cat === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['nom']) ?></option><?php endforeach; ?></select>
  <select name="tri" aria-label="Tri">
    <option value="cout_asc" <?= $tri === 'cout_asc' ? 'selected' : '' ?>>Prix croissant</option>
    <option value="cout_desc" <?= $tri === 'cout_desc' ? 'selected' : '' ?>>Prix décroissant</option>
    <option value="nom" <?= $tri === 'nom' ? 'selected' : '' ?>>Nom (A-Z)</option></select>
  <button class="btn btn-ghost" type="submit">Filtrer</button>
  <?php if ($q !== '' || $cat > 0): ?><a class="btn btn-ghost" href="<?= url('frontoffice/recompenses.php') ?>">Réinitialiser</a><?php endif; ?>
</form>

<?php if (!$recompenses): ?>
  <div class="card empty">Aucune récompense ne correspond à ta recherche.</div>
<?php else: ?>
<div class="grid g3">
  <?php foreach ($recompenses as $r):
    $rupture = (int)$r['stock'] < 1; $peut = !$rupture && $user['points'] >= $r['cout_points']; ?>
    <article class="card reward">
      <?= reward_art($r) ?>
      <h3><?= e($r['nom']) ?></h3>
      <p class="muted sm"><?= e($r['description']) ?></p>
      <?php if ($r['categorie']): ?><span class="muted xs"><?= e($r['categorie']) ?></span><?php endif; ?>
      <?php if ($rupture): ?><span class="badge bad"><i></i>Rupture de stock</span>
      <?php elseif ((int)$r['stock'] <= 10): ?><span class="stock-low xs">Plus que <?= (int)$r['stock'] ?> en stock</span><?php endif; ?>
      <div class="reward-f"><b><?= icon('star', 16) ?> <?= fmt($r['cout_points']) ?> pts</b>
        <?php if ($peut): ?><a class="btn btn-sm btn-primary" href="<?= url('frontoffice/echanger.php?id=' . (int)$r['id']) ?>">Échanger</a>
        <?php elseif ($rupture): ?><button class="btn btn-sm btn-primary" disabled>Indisponible</button>
        <?php else: ?><button class="btn btn-sm btn-primary" disabled><?= fmt($r['cout_points'] - $user['points']) ?> pts manquants</button><?php endif; ?>
      </div>
    </article>
  <?php endforeach; ?>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
