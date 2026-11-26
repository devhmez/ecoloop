<?php // Boutons de pagination. Variables : $p (page, pages) et $url (fonction qui construit l'adresse) ?>
<?php if ($p['pages'] > 1): ?>
    <nav class="pager" aria-label="Pagination">
        <?php if ($p['page'] > 1): ?><a href="<?= e($url(['page' => $p['page'] - 1])) ?>">Précédent</a><?php endif; ?>
        <?php for ($i = 1; $i <= $p['pages']; $i++): ?>
            <a class="<?= $i === $p['page'] ? 'on' : '' ?>" href="<?= e($url(['page' => $i])) ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($p['page'] < $p['pages']): ?><a href="<?= e($url(['page' => $p['page'] + 1])) ?>">Suivant</a><?php endif; ?>
    </nav>
<?php endif; ?>
