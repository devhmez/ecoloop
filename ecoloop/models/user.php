<?php
// Model: users and login
class User
{
    private static function db(): PDO
    {
        static $pdo = null;
        return $pdo ??= require __DIR__ . '/../config/database.php';
    }

    public static function find(int $id): ?array
    {
        $st = self::db()->prepare('SELECT id, name, email, role, points FROM users WHERE id = ?');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $st = self::db()->prepare('SELECT * FROM users WHERE email = ?');
        $st->execute([$email]);
        return $st->fetch() ?: null;
    }

    // The logged-in user, or null
    public static function current(): ?array
    {
        static $user = false;
        if ($user === false) {
            $id = (int)($_SESSION['user_id'] ?? 0);
            $user = $id ? self::find($id) : null;
        }
        return $user;
    }

    public static function validateRegister(array $v): array
    {
        $e = [];
        if (mb_strlen($v['name']) < 2)                         $e['name'] = 'Enter your name (at least 2 characters).';
        if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL))   $e['email'] = 'Enter a valid email address.';
        elseif (self::findByEmail($v['email']))                $e['email'] = 'This email is already registered.';
        if (strlen($v['password']) < 8)                        $e['password'] = 'The password needs at least 8 characters.';
        if ($v['password'] !== $v['confirm'])                  $e['confirm'] = 'The passwords do not match.';
        return $e;
    }

    public static function create(string $name, string $email, string $password): int
    {
        $st = self::db()->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
        $st->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
        return (int)self::db()->lastInsertId();
    }

    // Returns the user if the email and password are correct
    public static function attempt(string $email, string $password): ?array
    {
        $u = self::findByEmail($email);
        return ($u && $u['password_hash'] && password_verify($password, $u['password_hash'])) ? $u : null;
    }

    public static function login(int $id): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $id;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    // Guards: $json = true answers with a status code instead of redirecting
    public static function requireLogin(bool $json = false): array
    {
        $u = self::current();
        if ($u) return $u;
        if ($json) { http_response_code(401); echo json_encode(['error' => 'login']); exit; }
        redirect('login');
    }

    public static function requireAdmin(bool $json = false): array
    {
        $u = self::requireLogin($json);
        if ($u['role'] === 'admin') return $u;
        http_response_code(403);
        echo $json ? json_encode(['error' => 'forbidden']) : 'Access denied: this page is for administrators.';
        exit;
    }
}