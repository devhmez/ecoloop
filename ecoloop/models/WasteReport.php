<?php
class WasteReport
{
    const CURRENT_USER = 1;

    const CATEGORIES = [
        'plastic'     => ['label' => 'Plastic',     'color' => '#3B82F6', 'icon' => 'bottle'],
        'paper'       => ['label' => 'Paper',       'color' => '#E59A12', 'icon' => 'file'],
        'glass'       => ['label' => 'Glass',       'color' => '#12A89A', 'icon' => 'glass'],
        'metal'       => ['label' => 'Metal',       'color' => '#8393A7', 'icon' => 'can'],
        'organic'     => ['label' => 'Organic',     'color' => '#6DB33F', 'icon' => 'leaf'],
        'electronics' => ['label' => 'Electronics', 'color' => '#8B5CF6', 'icon' => 'laptop'],
    ];
    const STATUSES    = ['Pending', 'In review', 'Assigned', 'Resolved'];
    const PREFERENCES = ['Pickup within 48h', 'Scheduled pickup', 'Drop-off bin nearby', 'Special pickup'];

    private const SELECT = 'SELECT r.*, u.name FROM waste_reports r JOIN users u ON u.id = r.user_id';

    private static function db(): PDO
    {
        static $pdo = null;
        return $pdo ??= require __DIR__ . '/../config/database.php';
    }

    private static function map(array $r): array
    {
        $t = strtotime($r['created_at']);
        return [
            'id'     => 'R-' . $r['id'],
            'cat'    => $r['category'],
            'loc'    => $r['location'],
            'desc'   => $r['description'],
            'pref'   => $r['preference'],
            'status' => $r['status'],
            'date'   => date('Y-m-d', $t) === date('Y-m-d') ? 'Today' : date('j M', $t),
            'by'     => (int)$r['user_id'] === self::CURRENT_USER ? 'You' : $r['name'],
        ];
    }

    public static function all(): array
    {
        $rows = self::db()->query(self::SELECT . ' ORDER BY r.id DESC')->fetchAll();
        return array_map([self::class, 'map'], $rows);
    }

    public static function mine(): array
    {
        $st = self::db()->prepare(self::SELECT . ' WHERE r.user_id = ? ORDER BY r.id DESC');
        $st->execute([self::CURRENT_USER]);
        return array_map([self::class, 'map'], $st->fetchAll());
    }

    public static function find(string $id): ?array
    {
        $st = self::db()->prepare(self::SELECT . ' WHERE r.id = ?');
        $st->execute([(int)preg_replace('/\D/', '', $id)]);
        $row = $st->fetch();
        return $row ? self::map($row) : null;
    }

    public static function search(string $q = '', string $status = 'All'): array
    {
        $sql = self::SELECT . " WHERE CONCAT('R-', r.id, r.location, u.name, r.category) LIKE :q";
        $params = [':q' => '%' . trim($q) . '%'];
        if ($status !== 'All') { $sql .= ' AND r.status = :s'; $params[':s'] = $status; }
        $st = self::db()->prepare($sql . ' ORDER BY r.id DESC');
        $st->execute($params);
        return array_map([self::class, 'map'], $st->fetchAll());
    }

    public static function countBy(string $status): int
    {
        if ($status === 'All') return (int)self::db()->query('SELECT COUNT(*) FROM waste_reports')->fetchColumn();
        $st = self::db()->prepare('SELECT COUNT(*) FROM waste_reports WHERE status = ?');
        $st->execute([$status]);
        return (int)$st->fetchColumn();
    }

    public static function validate(array $v): array
    {
        $e = [];
        if (!isset(self::CATEGORIES[$v['cat']]))            $e['cat']  = 'Pick the type of waste.';
        if (mb_strlen($v['loc']) < 3)                        $e['loc']  = 'Type a location of at least 3 characters.';
        if (mb_strlen($v['desc']) < 10)                      $e['desc'] = 'Describe the problem in at least 10 characters.';
        if (!in_array($v['pref'], self::PREFERENCES, true))  $e['pref'] = 'Choose a collection preference.';
        return $e;
    }

    public static function create(array $v): array
    {
        $st = self::db()->prepare(
            'INSERT INTO waste_reports (user_id, category, location, description, preference) VALUES (?, ?, ?, ?, ?)'
        );
        $st->execute([self::CURRENT_USER, $v['cat'], $v['loc'], $v['desc'], $v['pref']]);
        return self::find((string)self::db()->lastInsertId());
    }

    public static function setStatus(string $id, string $status): bool
    {
        if (!in_array($status, self::STATUSES, true) || !self::find($id)) return false;
        $st = self::db()->prepare('UPDATE waste_reports SET status = ? WHERE id = ?');
        return $st->execute([$status, (int)preg_replace('/\D/', '', $id)]);
    }
}