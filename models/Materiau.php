<?php
// Modèle Materiau : les types de déchets, leur poubelle, leurs points et les mots reconnus par l'IA
require_once __DIR__ . '/../config/database.php';

class Materiau
{
    // READ : tous les matériaux, avec le nombre de scans de chacun (jointure)
    public static function all(): array
    {
        return db()->query('SELECT m.*, COUNT(s.id) AS nb_scans
                            FROM materiau m LEFT JOIN scan s ON s.materiau_id = m.id
                            GROUP BY m.id ORDER BY m.nom')->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $st = db()->prepare('SELECT * FROM materiau WHERE id = ?');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    // CREATE
    public static function create(array $m): void
    {
        $st = db()->prepare('INSERT INTO materiau (nom, poubelle, couleur, points, mots_cles, conseil) VALUES (?, ?, ?, ?, ?, ?)');
        $st->execute([$m['nom'], $m['poubelle'], $m['couleur'], $m['points'], $m['mots_cles'], $m['conseil']]);
    }

    // UPDATE
    public static function update(int $id, array $m): void
    {
        $st = db()->prepare('UPDATE materiau SET nom = ?, poubelle = ?, couleur = ?, points = ?, mots_cles = ?, conseil = ? WHERE id = ?');
        $st->execute([$m['nom'], $m['poubelle'], $m['couleur'], $m['points'], $m['mots_cles'], $m['conseil'], $id]);
    }

    // DELETE
    public static function delete(int $id): void
    {
        $st = db()->prepare('DELETE FROM materiau WHERE id = ?');
        $st->execute([$id]);
    }

    // Combien de scans utilisent ce matériau (on ne supprime pas un matériau déjà utilisé)
    public static function countScans(int $id): int
    {
        $st = db()->prepare('SELECT COUNT(*) FROM scan WHERE materiau_id = ?');
        $st->execute([$id]);
        return (int) $st->fetchColumn();
    }

    // Trouve le matériau qui correspond à un objet reconnu par l'IA (ex. "water bottle" -> Plastique)
    public static function matchLabel(string $label): ?array
    {
        foreach (self::all() as $m) {
            foreach (explode(',', $m['mots_cles']) as $mot) {
                $mot = trim($mot);
                if ($mot !== '' && preg_match('/\b' . preg_quote($mot, '/') . '\b/i', $label)) {
                    return $m;
                }
            }
        }
        return null;
    }

    // Contrôle de saisie côté serveur
    public static function validate(array $m, ?int $ignoreId = null): array
    {
        $errors = [];
        if (mb_strlen($m['nom']) < 2 || mb_strlen($m['nom']) > 40) {
            $errors['nom'] = 'Le nom doit contenir entre 2 et 40 caractères.';
        } else {
            $st = db()->prepare('SELECT id FROM materiau WHERE nom = ? AND id <> ?');
            $st->execute([$m['nom'], $ignoreId ?? 0]);
            if ($st->fetch()) {
                $errors['nom'] = 'Ce matériau existe déjà.';
            }
        }
        if (mb_strlen($m['poubelle']) < 3) {
            $errors['poubelle'] = 'Indique la poubelle (au moins 3 caractères).';
        }
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $m['couleur'])) {
            $errors['couleur'] = 'Couleur invalide.';
        }
        if (!ctype_digit((string) $m['points']) || $m['points'] > 50) {
            $errors['points'] = 'Les points doivent être un nombre entre 0 et 50.';
        }
        if (mb_strlen($m['mots_cles']) < 3) {
            $errors['mots_cles'] = 'Ajoute au moins un mot-clé.';
        }
        if (mb_strlen($m['conseil']) < 5) {
            $errors['conseil'] = 'Le conseil doit contenir au moins 5 caractères.';
        }
        return $errors;
    }
}
