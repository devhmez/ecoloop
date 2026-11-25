<?php
// Modèle Scan : chaque déchet scanné par un citoyen
require_once __DIR__ . '/../config/database.php';

class Scan
{
    // CREATE : enregistre le scan ET ajoute les points au citoyen (les deux ou rien : transaction)
    public static function create(int $userId, array $materiau, string $objet, int $confiance): void
    {
        $pdo = db();
        $pdo->beginTransaction();
        $st = $pdo->prepare('INSERT INTO scan (user_id, materiau_id, objet, confiance, points) VALUES (?, ?, ?, ?, ?)');
        $st->execute([$userId, $materiau['id'], $objet, $confiance, $materiau['points']]);
        $st = $pdo->prepare('UPDATE users SET points = points + ? WHERE id = ?');
        $st->execute([$materiau['points'], $userId]);
        $pdo->commit();
    }

    // READ : l'historique d'un citoyen (jointure avec materiau)
    public static function byUser(int $userId): array
    {
        $st = db()->prepare('SELECT s.*, m.nom AS materiau, m.couleur, m.poubelle
                             FROM scan s JOIN materiau m ON m.id = s.materiau_id
                             WHERE s.user_id = ? ORDER BY s.created_at DESC');
        $st->execute([$userId]);
        return $st->fetchAll();
    }

    // READ : tous les scans pour le back office (jointure avec users et materiau), filtre facultatif
    public static function all(int $materiauId = 0): array
    {
        $sql = 'SELECT s.*, u.name AS citoyen, m.nom AS materiau, m.couleur
                FROM scan s
                JOIN users u ON u.id = s.user_id
                JOIN materiau m ON m.id = s.materiau_id';
        $params = [];
        if ($materiauId > 0) {
            $sql .= ' WHERE s.materiau_id = ?';
            $params[] = $materiauId;
        }
        $st = db()->prepare($sql . ' ORDER BY s.created_at DESC');
        $st->execute($params);
        return $st->fetchAll();
    }

    // DELETE : supprime le scan et retire ses points au citoyen
    public static function delete(int $id): void
    {
        $pdo = db();
        $st = $pdo->prepare('SELECT user_id, points FROM scan WHERE id = ?');
        $st->execute([$id]);
        $scan = $st->fetch();
        if (!$scan) {
            return;
        }
        $pdo->beginTransaction();
        $st = $pdo->prepare('UPDATE users SET points = GREATEST(points - ?, 0) WHERE id = ?');
        $st->execute([$scan['points'], $scan['user_id']]);
        $st = $pdo->prepare('DELETE FROM scan WHERE id = ?');
        $st->execute([$id]);
        $pdo->commit();
    }
}
