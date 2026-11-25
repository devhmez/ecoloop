<?php
require_once __DIR__ . '/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const CATEGORIES = [
    'plastic'     => 'Plastic',
    'paper'       => 'Paper',
    'glass'       => 'Glass',
    'metal'       => 'Metal',
    'organic'     => 'Organic',
    'electronics' => 'Electronics',
];
const SLOTS = ['09:00 – 11:00', '11:00 – 13:00', '14:00 – 16:00', '16:00 – 18:00'];
const STATUSES = ['Scheduled', 'On the way', 'Completed', 'Cancelled'];
const NEXT_STATUS = ['Scheduled' => 'On the way', 'On the way' => 'Completed'];
const ACTIVE_STATUSES = ['Scheduled', 'On the way'];

function e($v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

/* ---------- CSRF ---------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}
function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}
function csrf_check(): void
{
    $ok = isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
    if (!$ok) {
        http_response_code(400);
        exit('Invalid CSRF token.');
    }
}

/* ---------- Messages flash ---------- */
function flash(string $msg, string $kind = 'ok'): void
{
    $_SESSION['flash'][] = ['msg' => $msg, 'kind' => $kind];
}
function flashes(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/* ---------- Affichage ---------- */
function status_class(string $s): string
{
    return ['Scheduled' => 'info', 'On the way' => 'warn', 'Completed' => 'ok', 'Cancelled' => 'bad'][$s] ?? 'mute';
}
function status_badge(string $s): string
{
    return '<span class="badge ' . status_class($s) . '">' . e($s) . '</span>';
}
function cats_badges(?string $csv): string
{
    $out = '';
    foreach (array_filter(explode(',', (string)$csv)) as $k) {
        $out .= '<span class="cat c-' . e($k) . '">' . e(CATEGORIES[$k] ?? $k) . '</span> ';
    }
    return $out;
}
function fmt_date(string $d): string
{
    $ts = strtotime($d);
    $label = date('D j M', $ts);
    return $d === date('Y-m-d') ? 'Today · ' . $label : $label;
}
function vehicle_label(?string $code, ?string $type): string
{
    return $code ? ($type ?: 'Vehicle') . ' ' . $code : '—';
}

/* ---------- Requêtes ---------- */
const COLLECTION_SELECT = "
    SELECT c.*, co.name AS collector_name, v.code AS vehicle_code, v.type AS vehicle_type,
           GROUP_CONCAT(cc.category ORDER BY cc.category SEPARATOR ',') AS cats
    FROM collections c
    LEFT JOIN collectors co ON co.id = c.collector_id
    LEFT JOIN vehicles v ON v.id = c.vehicle_id
    LEFT JOIN collection_categories cc ON cc.collection_id = c.id";
const COLLECTION_GROUP = " GROUP BY c.id, co.name, v.code, v.type ";

/** Un véhicule est "occupé" s'il a déjà une collecte active à la même date et au même créneau. */
function vehicle_busy(int $vehicleId, string $date, string $slot, int $excludeId = 0): bool
{
    $st = db()->prepare(
        "SELECT COUNT(*) FROM collections
         WHERE vehicle_id = ? AND pickup_date = ? AND slot = ?
           AND status IN ('Scheduled','On the way') AND id <> ?"
    );
    $st->execute([$vehicleId, $date, $slot, $excludeId]);
    return (int)$st->fetchColumn() > 0;
}
