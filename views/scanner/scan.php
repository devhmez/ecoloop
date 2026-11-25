<div class="top">
    <div>
        <h1>Scanner IA</h1>
        <p class="muted">Prends ton déchet en photo : l'IA le reconnaît et te dit dans quelle poubelle le jeter.</p>
    </div>
</div>

<div class="scan-grid">
    <section class="card">
        <div class="preview" id="preview">
            <img id="photo" alt="">
            <p id="placeholder">📷 Aucune photo pour l'instant</p>
        </div>

        <label class="btn btn-full">
            Prendre ou choisir une photo
            <input type="file" id="file" accept="image/*" capture="environment" hidden>
        </label>

        <p class="muted" id="status">Chargement de l'IA…</p>
        <ul class="guesses" id="guesses"></ul>

        <form method="post" action="index.php?r=scanner" id="scan-form">
            <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
            <div id="hidden-fields"></div>
            <button class="btn btn-full" id="send" disabled>Valider ce déchet</button>
        </form>
    </section>

    <section>
        <?php if ($result && $result['found']): ?>
            <div class="card result" style="border-color: <?= e($result['materiau']['couleur']) ?>">
                <span class="badge" style="background: <?= e($result['materiau']['couleur']) ?>; color: #fff"><?= e($result['materiau']['nom']) ?></span>
                <h2>👉 <?= e($result['materiau']['poubelle']) ?></h2>
                <p>Objet reconnu : <strong><?= e($result['objet']) ?></strong> (<?= (int) $result['confiance'] ?> % de confiance)</p>
                <p class="gain">+<?= (int) $result['materiau']['points'] ?> EcoPoints</p>
                <p class="muted"><?= e($result['materiau']['conseil']) ?></p>
            </div>
        <?php elseif ($result): ?>
            <div class="card result">
                <h2>🤔 Objet non reconnu</h2>
                <p>L'IA a vu « <?= e($result['objet']) ?> », mais ce n'est pas un déchet connu. Essaie une autre photo, de plus près.</p>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2>Où jeter quoi ?</h2>
            <?php foreach ($materiaux as $m): ?>
                <div class="bin">
                    <span class="dot" style="background: <?= e($m['couleur']) ?>"></span>
                    <div><strong><?= e($m['nom']) ?></strong><small><?= e($m['poubelle']) ?></small></div>
                    <span class="pts"><?= (int) $m['points'] ?> pts</span>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<!-- L'IA : TensorFlow.js + le modèle MobileNet (reconnaissance d'images), exécutés dans le navigateur -->
<script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@4.22.0/dist/tf.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/mobilenet@2.1.1/dist/mobilenet.min.js"></script>
<script src="assets/js/scanner.js"></script>
