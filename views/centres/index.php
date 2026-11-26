<?php include __DIR__ . '/_style.php'; ?>
<div class="top">
    <div>
        <h1>Centres de recyclage</h1>
        <p class="muted">Trouve où déposer chaque matière, et le centre le plus proche de toi.</p>
    </div>
</div>

<section class="card">
    <form method="get" action="index.php" class="search">
        <input type="hidden" name="r" value="centres">
        <?php if ($f['cat'] !== ''): ?><input type="hidden" name="cat" value="<?= e($f['cat']) ?>"><?php endif; ?>
        <?php if ($f['lat'] !== null): ?>
            <input type="hidden" name="lat" value="<?= e((string) $f['lat']) ?>">
            <input type="hidden" name="lng" value="<?= e((string) $f['lng']) ?>">
        <?php endif; ?>
        <input type="text" name="q" value="<?= e($f['q']) ?>" placeholder="Rechercher par nom, ville ou adresse">
        <select name="sort" aria-label="Trier">
            <option value="nom" <?= $f['sort'] === 'nom' ? 'selected' : '' ?>>Trier : nom</option>
            <option value="ville" <?= $f['sort'] === 'ville' ? 'selected' : '' ?>>Trier : ville</option>
            <?php if ($f['lat'] !== null): ?>
                <option value="distance" <?= $f['sort'] === 'distance' ? 'selected' : '' ?>>Trier : le plus proche</option>
            <?php endif; ?>
        </select>
        <button class="btn">Rechercher</button>
    </form>

    <div class="chips" style="margin-top:16px">
        <a class="chip <?= $f['cat'] === '' ? 'on' : '' ?>" href="<?= e($url(['cat' => null, 'page' => null])) ?>">Toutes les matières</a>
        <?php foreach (Centre::MATERIALS as $key => $label): ?>
            <a class="chip <?= $f['cat'] === $key ? 'on' : '' ?>" href="<?= e($url(['cat' => $key, 'page' => null])) ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
    </div>

    <div class="toolbar" id="nearBox" data-q="<?= e($f['q']) ?>" data-cat="<?= e($f['cat']) ?>">
        <strong>Centre le plus proche :</strong>
        <button type="button" class="btn btn-light" id="nearMe">Utiliser ma position</button>
        <select id="refPoint" aria-label="Ou choisir un point de départ">
            <option value="">…ou choisir un point de départ</option>
            <?php foreach ($points as $pt): ?>
                <option value="<?= e($pt['lat'] . ',' . $pt['lng']) ?>"><?= e($pt['label']) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if ($f['lat'] !== null): ?>
            <a href="<?= e($url(['lat' => null, 'lng' => null, 'sort' => 'nom', 'page' => null])) ?>">Effacer la position</a>
        <?php endif; ?>
        <span class="muted" id="nearMsg" role="status" style="margin:0"></span>
    </div>
</section>

<?php if (empty($p['items'])): ?>
    <section class="card" style="margin-top:20px"><strong>Aucun centre trouvé.</strong>
        <p class="muted" style="margin:6px 0 0">Essaie une autre matière ou efface la recherche.</p></section>
<?php endif; ?>

<div class="grid-centres">
    <?php foreach ($p['items'] as $c): ?>
        <article class="centre">
            <h3><?= e($c['nom']) ?></h3>
            <p><?= e($c['adresse']) ?>, <?= e($c['ville']) ?></p>
            <p>Horaires : <?= e($c['horaires']) ?></p>
            <?php if (!empty($c['telephone'])): ?><p>Tél. : <?= e($c['telephone']) ?></p><?php endif; ?>
            <?php if ($c['distance'] !== null): ?>
                <p class="km"><?= e(number_format($c['distance'], 1, ',', ' ')) ?> km</p>
            <?php endif; ?>
            <div class="chips">
                <?php foreach ($c['materials'] as $m): ?><span class="chip"><?= e(Centre::MATERIALS[$m] ?? $m) ?></span><?php endforeach; ?>
            </div>
            <a href="https://www.openstreetmap.org/?mlat=<?= e((string) $c['latitude']) ?>&amp;mlon=<?= e((string) $c['longitude']) ?>#map=16/<?= e((string) $c['latitude']) ?>/<?= e((string) $c['longitude']) ?>" target="_blank" rel="noopener">Voir sur la carte</a>
        </article>
    <?php endforeach; ?>
</div>

<?php include __DIR__ . '/_pager.php'; ?>
<p class="muted" style="margin-top:18px">Noms, adresses et horaires : exemples pour la démonstration.</p>

<script>
(function () {
    var box = document.getElementById('nearBox');
    var msg = document.getElementById('nearMsg');
    function go(lat, lng) {
        var p = new URLSearchParams();
        p.set('r', 'centres');
        if (box.dataset.q) p.set('q', box.dataset.q);
        if (box.dataset.cat) p.set('cat', box.dataset.cat);
        p.set('lat', lat);
        p.set('lng', lng);
        window.location.href = 'index.php?' + p.toString();
    }
    document.getElementById('nearMe').addEventListener('click', function () {
        if (!navigator.geolocation) { msg.textContent = 'La position n\u2019est pas disponible sur ce navigateur.'; return; }
        msg.textContent = 'Recherche de ta position\u2026';
        navigator.geolocation.getCurrentPosition(
            function (pos) { go(pos.coords.latitude.toFixed(5), pos.coords.longitude.toFixed(5)); },
            function () { msg.textContent = 'Position indisponible. Choisis un point de départ.'; }
        );
    });
    document.getElementById('refPoint').addEventListener('change', function () {
        if (!this.value) return;
        var v = this.value.split(',');
        go(v[0], v[1]);
    });
})();
</script>
