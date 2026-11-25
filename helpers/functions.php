<?php
// Petites fonctions utilisées partout dans le site

// Protège un texte avant de l'afficher dans le HTML (contre le XSS)
function e(?string $text): string
{
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

// Initiales d'un nom pour l'avatar : "Sami Ben Ali" -> "SB"
function initials(string $name): string
{
    $letters = '';
    foreach (array_slice(explode(' ', trim($name)), 0, 2) as $word) {
        $letters .= mb_strtoupper(mb_substr($word, 0, 1));
    }
    return $letters;
}

// Envoie le navigateur vers une autre page du site
function redirect(string $route): never
{
    header('Location: index.php?r=' . $route);
    exit;
}

// Garde un message pour la page suivante, ou le récupère une seule fois
function flash(?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'] = $message;
        return null;
    }
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $message;
}

// Jeton CSRF : prouve que le formulaire vient bien de notre site
function csrf(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function checkCsrf(): void
{
    if (!hash_equals(csrf(), $_POST['csrf'] ?? '')) {
        http_response_code(400);
        exit('Requête invalide.');
    }
}

// Utilisateur connecté (ou null si personne n'est connecté)
function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function requireLogin(): void
{
    if (currentUser() === null) {
        flash("Connecte-toi d'abord.");
        redirect('login');
    }
}

function requireAdmin(): void
{
    requireLogin();
    if (currentUser()['role'] !== 'admin') {
        http_response_code(403);
        exit("Accès réservé à l'administrateur.");
    }
}

// Affiche une vue entre l'en-tête et le pied de page
function view(string $file, array $data = []): void
{
    extract($data);
    require __DIR__ . '/../views/layout/header.php';
    require __DIR__ . '/../views/' . $file . '.php';
    require __DIR__ . '/../views/layout/footer.php';
}
