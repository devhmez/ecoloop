<?php
require_once __DIR__ . '/db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

/* ---------- Helpers généraux ---------- */
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function url(string $path = ''): string { return BASE_URL . '/' . ltrim($path, '/'); }
function redirect(string $path): void { header('Location: ' . url($path)); exit; }
function fmt($n): string { return number_format((int)$n, 0, ',', ' '); }
function fmt_date(string $d): string { return date('d/m/Y H:i', strtotime($d)); }

/* ---------- Messages flash ---------- */
function flash(string $msg, string $type = 'ok'): void { $_SESSION['flash'][] = ['m' => $msg, 't' => $type]; }
function get_flashes(): array { $f = $_SESSION['flash'] ?? []; unset($_SESSION['flash']); return $f; }

/* ---------- CSRF ---------- */
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(32)); }
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'; }
function csrf_check(): void {
    $ok = isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
    if (!$ok) { http_response_code(403); exit('Requête invalide (jeton CSRF).'); }
}

/* ---------- Authentification ---------- */
function current_user(): ?array {
    if (empty($_SESSION['uid'])) return null;
    static $u = false;
    if ($u === false) {
        $s = db()->prepare('SELECT id, nom, email, role, quartier, points, statut FROM utilisateurs WHERE id = ?');
        $s->execute([$_SESSION['uid']]);
        $u = $s->fetch() ?: null;
    }
    return $u;
}
function require_login(): array {
    $u = current_user();
    if (!$u || $u['statut'] !== 'actif') { flash('Connecte-toi pour continuer.', 'warn'); redirect('login.php'); }
    return $u;
}
function require_admin(): array {
    $u = require_login();
    if ($u['role'] !== 'admin') { http_response_code(403); exit('Accès réservé aux administrateurs.'); }
    return $u;
}
function initials(string $name): string {
    $out = '';
    foreach (preg_split('/\s+/', trim($name)) as $w) { $out .= mb_strtoupper(mb_substr($w, 0, 1)); }
    return mb_substr($out, 0, 2);
}

/* ---------- Statuts d'échange ---------- */
const STATUTS = [
    'en_attente' => ['En attente', 'warn'],
    'valide'     => ['Validé',     'info'],
    'remis'      => ['Remis',      'ok'],
    'annule'     => ['Annulé',     'bad'],
];
function badge_statut(string $s): string {
    [$l, $k] = STATUTS[$s] ?? [$s, 'mute'];
    return '<span class="badge ' . $k . '"><i></i>' . e($l) . '</span>';
}

/* ---------- Icônes SVG (mêmes tracés que la maquette) ---------- */
function icon(string $n, int $s = 20): string {
    static $I = [
        'leaf' => ['M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z', 'M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12'],
        'home' => ['m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z', 'M9 22V12h6v10'],
        'grid' => ['M3 3h7v9H3z', 'M14 3h7v5h-7z', 'M14 12h7v9h-7z', 'M3 16h7v5H3z'],
        'scan' => ['M3 7V5a2 2 0 0 1 2-2h2', 'M17 3h2a2 2 0 0 1 2 2v2', 'M21 17v2a2 2 0 0 1-2 2h-2', 'M7 21H5a2 2 0 0 1-2-2v-2', 'M7 12h10'],
        'truck' => ['M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2', 'M15 18H9', 'M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.62l-3.48-4.35A1 1 0 0 0 17.52 8H14', 'M5 18a2 2 0 1 0 4 0 2 2 0 1 0-4 0', 'M15 18a2 2 0 1 0 4 0 2 2 0 1 0-4 0'],
        'gift' => ['M20 12v10H4V12', 'M2 7h20v5H2z', 'M12 22V7', 'M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z', 'M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z'],
        'search' => ['M3 11a8 8 0 1 0 16 0 8 8 0 1 0-16 0', 'm21 21-4.3-4.3'],
        'x' => ['M18 6 6 18', 'm6 6 12 12'],
        'check' => ['M20 6 9 17l-5-5'],
        'users' => ['M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2', 'M5 7a4 4 0 1 0 8 0 4 4 0 1 0-8 0', 'M22 21v-2a4 4 0 0 0-3-3.87', 'M16 3.13a4 4 0 0 1 0 7.75'],
        'award' => ['M7 9a5 5 0 1 0 10 0 5 5 0 1 0-10 0', 'M8.21 13.89 7 23l5-3 5 3-1.21-9.12'],
        'droplet' => ['M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z'],
        'zap' => ['M13 2 3 14h9l-1 8 10-12h-9l1-8z'],
        'arrowRight' => ['M5 12h14', 'm12 5 7 7-7 7'],
        'clock' => ['M3 12a9 9 0 1 0 18 0 9 9 0 1 0-18 0', 'M12 6v6l4 2'],
        'bottle' => ['M10 2h4', 'M10 2v3l-2 3v12a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2V8l-2-3V2', 'M8 13h8'],
        'file' => ['M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z', 'M14 2v6h6', 'M8 13h8', 'M8 17h8'],
        'glass' => ['M8 22h8', 'M12 15v7', 'M7 2h10l-1 8a4 4 0 0 1-8 0z'],
        'info' => ['M3 12a9 9 0 1 0 18 0 9 9 0 1 0-18 0', 'M12 16v-4', 'M12 8h.01'],
        'plus' => ['M5 12h14', 'M12 5v14'],
        'logout' => ['M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4', 'm16 17 5-5-5-5', 'M21 12H9'],
        'star' => ['m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8L12 17.8 5.8 21l1.2-6.8-5-4.9 6.9-1z'],
        'recycle' => ['M7 19H4.8a1.8 1.8 0 0 1-1.6-2.6L5 13', 'm11 19 2 2-2 2', 'M8 8 5.5 4.5a1.8 1.8 0 0 1 3 0L10 7', 'M14 5.4l2.5-.9', 'M17 13l2.2 3.4a1.8 1.8 0 0 1-1.5 2.6H13'],
        'edit' => ['M12 20h9', 'M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z'],
        'trash' => ['M3 6h18', 'M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6', 'M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2'],
    ];
    $paths = '';
    foreach ($I[$n] ?? [] as $d) { $paths .= '<path d="' . $d . '"/>'; }
    return '<svg width="' . $s . '" height="' . $s . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths . '</svg>';
}
const ICONES_DISPO = ['gift', 'leaf', 'droplet', 'recycle', 'star', 'zap', 'bottle', 'glass', 'file', 'award'];

function reward_art(array $r, string $size = 'md'): string {
    $px = $size === 'xs' ? 20 : ($size === 'sm' ? 28 : 40);
    return '<div class="r-art hue-' . e($r['teinte']) . ' ' . e($size) . '">' . icon($r['icone'], $px) . '</div>';
}

/* ---------- Logique métier : échange de points ---------- */
function generer_code(): string {
    return 'ECO-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
}

/**
 * Échange atomique : vérifie solde + stock, débite, décrémente, journalise.
 * Retourne le code d'échange ou lève une RuntimeException avec un message lisible.
 */
function effectuer_echange(int $userId, int $recompenseId): string {
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $u = $pdo->prepare('SELECT id, points, statut FROM utilisateurs WHERE id = ? FOR UPDATE');
        $u->execute([$userId]);
        $user = $u->fetch();
        $r = $pdo->prepare('SELECT * FROM recompenses WHERE id = ? FOR UPDATE');
        $r->execute([$recompenseId]);
        $rec = $r->fetch();

        if (!$user || $user['statut'] !== 'actif') throw new RuntimeException('Compte indisponible.');
        if (!$rec || !(int)$rec['actif'])          throw new RuntimeException('Cette récompense n\'est plus disponible.');
        if ((int)$rec['stock'] < 1)                throw new RuntimeException('Cette récompense est en rupture de stock.');
        if ((int)$user['points'] < (int)$rec['cout_points']) throw new RuntimeException('Points insuffisants.');

        $code = generer_code();
        $pdo->prepare('INSERT INTO echanges (code, utilisateur_id, recompense_id, points_depenses) VALUES (?,?,?,?)')
            ->execute([$code, $userId, $recompenseId, $rec['cout_points']]);
        $echangeId = (int)$pdo->lastInsertId();

        $pdo->prepare('UPDATE utilisateurs SET points = points - ? WHERE id = ?')->execute([$rec['cout_points'], $userId]);
        $pdo->prepare('UPDATE recompenses SET stock = stock - 1 WHERE id = ?')->execute([$recompenseId]);
        $pdo->prepare('INSERT INTO historique_points (utilisateur_id, type, points, description, echange_id) VALUES (?,?,?,?,?)')
            ->execute([$userId, 'depense', -(int)$rec['cout_points'], 'Échange : ' . $rec['nom'], $echangeId]);

        $pdo->commit();
        return $code;
    } catch (Throwable $t) {
        $pdo->rollBack();
        throw $t;
    }
}

/** Annule un échange non remis : rembourse les points et remet l'article en stock. */
function annuler_echange(int $echangeId, ?int $userId = null): void {
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $s = $pdo->prepare('SELECT * FROM echanges WHERE id = ? FOR UPDATE');
        $s->execute([$echangeId]);
        $ech = $s->fetch();
        if (!$ech || ($userId !== null && (int)$ech['utilisateur_id'] !== $userId)) throw new RuntimeException('Échange introuvable.');
        if (in_array($ech['statut'], ['annule', 'remis'], true)) throw new RuntimeException('Cet échange ne peut plus être annulé.');
        if ($userId !== null && $ech['statut'] !== 'en_attente') throw new RuntimeException('Seul un échange en attente peut être annulé.');

        $pdo->prepare("UPDATE echanges SET statut = 'annule' WHERE id = ?")->execute([$echangeId]);
        $pdo->prepare('UPDATE utilisateurs SET points = points + ? WHERE id = ?')->execute([$ech['points_depenses'], $ech['utilisateur_id']]);
        $pdo->prepare('UPDATE recompenses SET stock = stock + 1 WHERE id = ?')->execute([$ech['recompense_id']]);
        $pdo->prepare('INSERT INTO historique_points (utilisateur_id, type, points, description, echange_id) VALUES (?,?,?,?,?)')
            ->execute([$ech['utilisateur_id'], 'remboursement', $ech['points_depenses'], 'Remboursement échange ' . $ech['code'], $echangeId]);
        $pdo->commit();
    } catch (Throwable $t) {
        $pdo->rollBack();
        throw $t;
    }
}
