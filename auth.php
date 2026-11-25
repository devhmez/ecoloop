<?php
require_once __DIR__ . '/helpers.php';

/** Utilisateur connecté (ou null). Rechargé depuis la base à chaque requête. */
function current_user(): ?array
{
    static $cache = false;
    if ($cache !== false) {
        return $cache;
    }
    if (empty($_SESSION['uid'])) {
        return $cache = null;
    }
    $st = db()->prepare("SELECT id, name, email, role, collector_id, active FROM users WHERE id = ?");
    $st->execute([$_SESSION['uid']]);
    $u = $st->fetch();
    if (!$u || !$u['active']) {
        unset($_SESSION['uid']);
        return $cache = null;
    }
    return $cache = $u;
}

/** Page d'accueil selon le rôle (chemin relatif à la racine du projet). */
function home_for(string $role): string
{
    return ['admin' => 'admin/collections.php', 'driver' => 'driver/index.php', 'citizen' => 'index.php'][$role] ?? 'index.php';
}

/** Exige une connexion et un des rôles donnés. $root = '' ou '../' selon le dossier de la page. */
function require_login(array $roles, string $root = ''): array
{
    $u = current_user();
    if (!$u) {
        redirect($root . 'login.php');
    }
    if (!in_array($u['role'], $roles, true)) {
        flash('Access denied for your account.', 'bad');
        redirect($root . home_for($u['role']));
    }
    return $u;
}
