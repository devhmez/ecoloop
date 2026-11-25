<?php
require_once __DIR__ . '/../includes/functions.php';
$user = require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('frontoffice/mes_echanges.php');
csrf_check();
try {
    annuler_echange((int)($_POST['id'] ?? 0), (int)$user['id']);
    flash('Échange annulé : tes points ont été remboursés.');
} catch (RuntimeException $ex) {
    flash($ex->getMessage(), 'bad');
}
redirect('frontoffice/mes_echanges.php');
