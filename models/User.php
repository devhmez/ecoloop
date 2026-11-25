<?php
// Modèle User : toutes les requêtes SQL sur la table users
require_once __DIR__ . '/../config/database.php';

class User
{
    // READ : liste des utilisateurs, avec recherche par nom ou email
    public static function all(string $search = ''): array
    {
        $st = db()->prepare('SELECT id, name, email, role, points, created_at FROM users
                             WHERE name LIKE ? OR email LIKE ? ORDER BY created_at DESC');
        $st->execute(['%' . $search . '%', '%' . $search . '%']);
        return $st->fetchAll();
    }

    // READ : chiffres pour le tableau de bord (total, citoyens, admins, points)
    public static function stats(): array
    {
        return db()->query("SELECT COUNT(*) AS total,
                                   SUM(role = 'citoyen') AS citoyens,
                                   SUM(role = 'admin') AS admins,
                                   COALESCE(SUM(points), 0) AS points
                            FROM users")->fetch();
    }

    // READ : un seul utilisateur grâce à son id
    public static function find(int $id): ?array
    {
        $st = db()->prepare('SELECT id, name, email, role, points, created_at FROM users WHERE id = ?');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    // READ : un utilisateur grâce à son email (sert à la connexion)
    public static function findByEmail(string $email): ?array
    {
        $st = db()->prepare('SELECT * FROM users WHERE email = ?');
        $st->execute([$email]);
        return $st->fetch() ?: null;
    }

    // CREATE : ajoute un utilisateur (le mot de passe est haché, jamais stocké en clair)
    public static function create(string $name, string $email, string $password, string $role = 'citoyen'): void
    {
        $st = db()->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
        $st->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
    }

    // UPDATE : modifie le nom, l'email et le rôle
    public static function update(int $id, string $name, string $email, string $role): void
    {
        $st = db()->prepare('UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?');
        $st->execute([$name, $email, $role, $id]);
    }

    // UPDATE : nouveau mot de passe (et on efface le lien "mot de passe oublié")
    public static function updatePassword(int $id, string $password): void
    {
        $st = db()->prepare('UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?');
        $st->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
    }

    // DELETE : supprime un utilisateur
    public static function delete(int $id): void
    {
        $st = db()->prepare('DELETE FROM users WHERE id = ?');
        $st->execute([$id]);
    }

    // Vérifie le mot de passe actuel d'un utilisateur
    public static function checkPassword(int $id, string $password): bool
    {
        $st = db()->prepare('SELECT password FROM users WHERE id = ?');
        $st->execute([$id]);
        $hash = $st->fetchColumn();
        return $hash !== false && password_verify($password, $hash);
    }

    // Mot de passe oublié : enregistre un jeton valable 30 minutes
    public static function setResetToken(int $id, string $token): void
    {
        $st = db()->prepare('UPDATE users SET reset_token = ?, reset_expires = DATE_ADD(NOW(), INTERVAL 30 MINUTE) WHERE id = ?');
        $st->execute([$token, $id]);
    }

    public static function findByResetToken(string $token): ?array
    {
        $st = db()->prepare('SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW()');
        $st->execute([$token]);
        return $st->fetch() ?: null;
    }

    // Contrôle de saisie côté serveur (le JavaScript peut être désactivé, donc on revérifie ici)
    public static function validate(string $name, string $email, ?int $ignoreId = null): array
    {
        $errors = [];
        if (mb_strlen($name) < 3 || mb_strlen($name) > 80) {
            $errors['name'] = 'Le nom doit contenir entre 3 et 80 caractères.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Adresse email invalide.';
        } else {
            $other = self::findByEmail($email);
            if ($other !== null && (int) $other['id'] !== $ignoreId) {
                $errors['email'] = 'Cet email est déjà utilisé.';
            }
        }
        return $errors;
    }

    public static function validatePassword(string $password, string $confirm): array
    {
        $errors = [];
        if (strlen($password) < 8 || !preg_match('/[0-9]/', $password)) {
            $errors['password'] = 'Au moins 8 caractères, dont un chiffre.';
        } elseif ($password !== $confirm) {
            $errors['confirm'] = 'Les deux mots de passe ne correspondent pas.';
        }
        return $errors;
    }
}
