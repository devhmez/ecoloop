<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pdo = db();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$r = ['nom' => '', 'description' => '', 'cout_points' => '', 'stock' => 0, 'icone' => 'gift', 'teinte' => 'a', 'categorie_id' => '', 'actif' => 1];
if ($id) {
    $s = $pdo->prepare('SELECT * FROM recompenses WHERE id = ?'); $s->execute([$id]);
    $r = $s->fetch();
    if (!$r) { flash('Récompense introuvable.', 'bad'); redirect('backoffice/recompenses.php'); }
}
$categories = $pdo->query('SELECT id, nom FROM categories_recompenses ORDER BY nom')->fetchAll();
$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $r = [
        'nom' => trim($_POST['nom'] ?? ''), 'description' => trim($_POST['description'] ?? ''),
        'cout_points' => $_POST['cout_points'] ?? '', 'stock' => $_POST['stock'] ?? '',
        'icone' => $_POST['icone'] ?? 'gift', 'teinte' => $_POST['teinte'] ?? 'a',
        'categorie_id' => (int)($_POST['categorie_id'] ?? 0) ?: null, 'actif' => isset($_POST['actif']) ? 1 : 0,
    ];
    if (mb_strlen($r['nom']) < 3 || mb_strlen($r['nom']) > 120)         $erreurs['nom'] = 'Le nom doit contenir entre 3 et 120 caractères.';
    if (mb_strlen($r['description']) > 255)                             $erreurs['description'] = '255 caractères maximum.';
    if (!ctype_digit((string)$r['cout_points']) || (int)$r['cout_points'] < 1) $erreurs['cout_points'] = 'Entre un coût en points supérieur à 0.';
    if (!ctype_digit((string)$r['stock']))                              $erreurs['stock'] = 'Entre un stock valide (0 ou plus).';
    if (!in_array($r['icone'], ICONES_DISPO, true))                     $r['icone'] = 'gift';
    if (!in_array($r['teinte'], ['a', 'b', 'c'], true))                 $r['teinte'] = 'a';

    if (!$erreurs) {
        $data = [$r['nom'], $r['description'], (int)$r['cout_points'], (int)$r['stock'], $r['icone'], $r['teinte'], $r['categorie_id'], $r['actif']];
        if ($id) {
            $pdo->prepare('UPDATE recompenses SET nom=?, description=?, cout_points=?, stock=?, icone=?, teinte=?, categorie_id=?, actif=? WHERE id=?')->execute([...$data, $id]);
            flash('Récompense modifiée.');
        } else {
            $pdo->prepare('INSERT INTO recompenses (nom, description, cout_points, stock, icone, teinte, categorie_id, actif) VALUES (?,?,?,?,?,?,?,?)')->execute($data);
            flash('Récompense ajoutée au catalogue.');
        }
        redirect('backoffice/recompenses.php');
    }
}

$pageTitle = $id ? 'Modifier la récompense' : 'Nouvelle récompense'; $area = 'admin'; $active = 'rec';
require __DIR__ . '/../includes/header.php';
$err = fn($k) => isset($erreurs[$k]) ? '<span class="neg xs">' . e($erreurs[$k]) . '</span>' : '';
?>
<div class="ph"><div><h1><?= e($pageTitle) ?></h1></div><a class="btn btn-ghost" href="<?= url('backoffice/recompenses.php') ?>">Retour à la liste</a></div>
<form class="card form-card stack" method="post" novalidate>
  <?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>">
  <div class="form-grid">
    <div class="field full"><label for="nom">Nom</label><input id="nom" name="nom" maxlength="120" required value="<?= e($r['nom']) ?>"><?= $err('nom') ?></div>
    <div class="field full"><label for="description">Description</label><textarea id="description" name="description" rows="2" maxlength="255"><?= e($r['description']) ?></textarea><?= $err('description') ?></div>
    <div class="field"><label for="cout">Coût (points)</label><input id="cout" name="cout_points" type="number" min="1" required value="<?= e($r['cout_points']) ?>"><?= $err('cout_points') ?></div>
    <div class="field"><label for="stock">Stock</label><input id="stock" name="stock" type="number" min="0" required value="<?= e($r['stock']) ?>"><?= $err('stock') ?></div>
    <div class="field"><label for="cat">Catégorie</label><select id="cat" name="categorie_id"><option value="0">— Aucune —</option>
      <?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>" <?= (int)$r['categorie_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['nom']) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label for="teinte">Couleur de la vignette</label><select id="teinte" name="teinte">
      <option value="a" <?= $r['teinte'] === 'a' ? 'selected' : '' ?>>Vert forêt</option><option value="b" <?= $r['teinte'] === 'b' ? 'selected' : '' ?>>Vert lime</option><option value="c" <?= $r['teinte'] === 'c' ? 'selected' : '' ?>>Turquoise</option></select></div>
    <div class="field full"><span style="font-weight:600;font-size:13.5px">Icône</span>
      <div class="icon-pick"><?php foreach (ICONES_DISPO as $ic): ?>
        <span><input type="radio" id="ic-<?= $ic ?>" name="icone" value="<?= $ic ?>" <?= $r['icone'] === $ic ? 'checked' : '' ?>><label for="ic-<?= $ic ?>" title="<?= $ic ?>"><?= icon($ic, 20) ?></label></span>
      <?php endforeach; ?></div></div>
    <label class="full row" style="gap:10px;cursor:pointer"><input type="checkbox" name="actif" style="width:auto" <?= $r['actif'] ? 'checked' : '' ?>> Visible dans le catalogue citoyen</label>
  </div>
  <div class="row" style="justify-content:flex-end"><button class="btn btn-primary" type="submit"><?= $id ? 'Enregistrer' : 'Ajouter la récompense' ?></button></div>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>
