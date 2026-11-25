<?php
require_once __DIR__ . '/icons.php';

function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function url(string $route, array $params = []): string
{
    return 'index.php?' . http_build_query(['r' => $route] + $params);
}

function view(string $path, array $data = []): void
{
    extract($data);
    require __DIR__ . '/../views/' . $path . '.php';
}

function redirect(string $route, array $params = []): never
{
    header('Location: ' . url($route, $params));
    exit;
}

function flash(?string $msg = null): ?string
{
    if ($msg !== null) { $_SESSION['flash'] = $msg; return null; }
    $m = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
    return $m;
}

function logo(int $size = 38, bool $light = false): string
{
    $word = $light ? '<span class="logo-word" style="color:#fff">Eco<span style="color:#A8E063">Loop</span></span>'
                   : '<span class="logo-word">Eco<span style="color:var(--accent-text)">Loop</span></span>';
    return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 48 48" role="img" aria-label="EcoLoop logo">'
        . '<rect width="48" height="48" rx="14" fill="#123D35"/>'
        . '<path d="M24 6A18 18 0 0 1 39.6 15" fill="none" stroke="#A8E063" stroke-width="3.6" stroke-linecap="round"/>'
        . '<polygon points="42.1,19.3 43.1,13 36.1,17" fill="#A8E063"/>'
        . '<path d="M24 42A18 18 0 0 1 8.4 33" fill="none" stroke="#2D8C74" stroke-width="3.6" stroke-linecap="round"/>'
        . '<polygon points="5.9,28.7 4.9,35 11.9,31" fill="#2D8C74"/>'
        . '<path d="M17 31c0-9 5-14 14-15 0 9-4 15-12 15-1 0-2 0-2 0z" fill="#A8E063"/>'
        . '<path d="M17.5 30.5 26 22" stroke="#123D35" stroke-width="1.6" stroke-linecap="round" fill="none"/></svg>' . $word;
}

function status_badge(string $status): string
{
    $kind = ['Pending' => 'warn', 'In review' => 'info', 'Assigned' => 'info', 'Resolved' => 'ok'][$status] ?? 'mute';
    return '<span class="badge b-' . $kind . '"><i></i>' . e($status) . '</span>';
}

function cat_badge(string $key, bool $small = false): string
{
    $c = WasteReport::CATEGORIES[$key];
    return '<span class="cat' . ($small ? ' cat-sm' : '') . '" style="--c:' . $c['color'] . '">'
        . icon($c['icon'], $small ? 14 : 16) . ' ' . e($c['label']) . '</span>';
}
