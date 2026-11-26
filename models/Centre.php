<?php
// Modèle Centre : toutes les requêtes SQL sur les tables centre et centre_categorie
require_once __DIR__ . '/../config/database.php';

class Centre
{
    // Matières acceptées : clé enregistrée en base => libellé affiché
    public const MATERIALS = [
        'plastique'    => 'Plastique',
        'verre'        => 'Verre',
        'papier'       => 'Papier & carton',
        'metal'        => 'Métal',
        'organique'    => 'Organique',
        'electronique' => 'Électronique',
        'encombrants'  => 'Encombrants',
    ];

    public const SORTS = ['nom', 'ville', 'distance'];

    // Points de départ proposés au citoyen pour trouver le centre le plus proche
    public const POINTS = [
        ['label' => 'Tunis centre-ville', 'lat' => 36.8065, 'lng' => 10.1815],
        ['label' => 'Ariana', 'lat' => 36.866, 'lng' => 10.165],
        ['label' => 'Les Berges du Lac', 'lat' => 36.833, 'lng' => 10.233],
        ['label' => 'El Menzah', 'lat' => 36.843, 'lng' => 10.176],
        ['label' => 'La Marsa', 'lat' => 36.878, 'lng' => 10.325],
        ['label' => 'Ben Arous', 'lat' => 36.753, 'lng' => 10.219],
    ];

    // Distance en km entre deux points GPS (formule de Haversine)
    public static function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return 2 * $r * asin(sqrt($a));
    }

    // READ : liste filtrée (recherche, matière, statut) puis triée. $f vient du contrôleur.
    public static function search(array $f): array
    {
        $sql = 'SELECT c.*, GROUP_CONCAT(cc.categorie ORDER BY cc.categorie) AS cats
                FROM centre c
                LEFT JOIN centre_categorie cc ON cc.centre_id = c.id
                WHERE (c.nom LIKE ? OR c.ville LIKE ? OR c.adresse LIKE ?)';
        $like = '%' . $f['q'] . '%';
        $params = [$like, $like, $like];
        if ($f['cat'] !== '') {
            $sql .= ' AND EXISTS (SELECT 1 FROM centre_categorie x WHERE x.centre_id = c.id AND x.categorie = ?)';
            $params[] = $f['cat'];
        }
        if ($f['status'] === 'actif' || $f['status'] === 'inactif') {
            $sql .= ' AND c.actif = ?';
            $params[] = $f['status'] === 'actif' ? 1 : 0;
        }
        $sql .= ' GROUP BY c.id';
        $st = db()->prepare($sql);
        $st->execute($params);
        $rows = $st->fetchAll();

        $hasPos = $f['lat'] !== null && $f['lng'] !== null;
        foreach ($rows as &$r) {
            $r['materials'] = $r['cats'] === null ? [] : explode(',', $r['cats']);
            $r['distance'] = $hasPos ? self::haversine($f['lat'], $f['lng'], (float) $r['latitude'], (float) $r['longitude']) : null;
        }
        unset($r);

        $sort = in_array($f['sort'], self::SORTS, true) ? $f['sort'] : 'nom';
        if ($sort === 'distance' && !$hasPos) {
            $sort = 'nom';
        }
        $dir = $f['dir'] === 'desc' ? -1 : 1;
        usort($rows, function (array $a, array $b) use ($sort, $dir): int {
            $x = $sort === 'distance' ? $a['distance'] : mb_strtolower($a[$sort]);
            $y = $sort === 'distance' ? $b['distance'] : mb_strtolower($b[$sort]);
            return $dir * ($x <=> $y);
        });
        return $rows;
    }

    // Découpe une liste en pages
    public static function paginate(array $rows, int $page, int $perPage): array
    {
        $total = count($rows);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $pages);
        return [
            'items' => array_slice($rows, ($page - 1) * $perPage, $perPage),
            'total' => $total,
            'page'  => $page,
            'pages' => $pages,
        ];
    }

    // READ : un centre grâce à son id (avec ses matières)
    public static function find(int $id): ?array
    {
        $st = db()->prepare('SELECT * FROM centre WHERE id = ?');
        $st->execute([$id]);
        $c = $st->fetch();
        if (!$c) {
            return null;
        }
        $m = db()->prepare('SELECT categorie FROM centre_categorie WHERE centre_id = ?');
        $m->execute([$id]);
        $c['materials'] = $m->fetchAll(PDO::FETCH_COLUMN);
        return $c;
    }

    // CREATE : ajoute un centre et ses matières (en une seule transaction)
    public static function create(array $d): int
    {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $st = $pdo->prepare('INSERT INTO centre (nom, adresse, ville, latitude, longitude, horaires, telephone, actif)
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $st->execute([$d['nom'], $d['adresse'], $d['ville'], (float) $d['latitude'], (float) $d['longitude'],
                          $d['horaires'], $d['telephone'] !== '' ? $d['telephone'] : null, $d['actif'] ? 1 : 0]);
            $id = (int) $pdo->lastInsertId();
            self::saveMaterials($id, $d['materials']);
            $pdo->commit();
            return $id;
        } catch (Throwable $ex) {
            $pdo->rollBack();
            throw $ex;
        }
    }

    // UPDATE : modifie un centre et ses matières
    public static function update(int $id, array $d): void
    {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $st = $pdo->prepare('UPDATE centre SET nom = ?, adresse = ?, ville = ?, latitude = ?, longitude = ?,
                                 horaires = ?, telephone = ?, actif = ? WHERE id = ?');
            $st->execute([$d['nom'], $d['adresse'], $d['ville'], (float) $d['latitude'], (float) $d['longitude'],
                          $d['horaires'], $d['telephone'] !== '' ? $d['telephone'] : null, $d['actif'] ? 1 : 0, $id]);
            self::saveMaterials($id, $d['materials']);
            $pdo->commit();
        } catch (Throwable $ex) {
            $pdo->rollBack();
            throw $ex;
        }
    }

    // DELETE : supprime un centre (ses matières partent avec lui grâce à ON DELETE CASCADE)
    public static function delete(int $id): void
    {
        $st = db()->prepare('DELETE FROM centre WHERE id = ?');
        $st->execute([$id]);
    }

    private static function saveMaterials(int $id, array $materials): void
    {
        $pdo = db();
        $pdo->prepare('DELETE FROM centre_categorie WHERE centre_id = ?')->execute([$id]);
        $ins = $pdo->prepare('INSERT INTO centre_categorie (centre_id, categorie) VALUES (?, ?)');
        foreach ($materials as $m) {
            $ins->execute([$id, $m]);
        }
    }

    // Contrôle de saisie côté serveur
    public static function validate(array $d, ?int $ignoreId = null): array
    {
        $errors = [];
        if (mb_strlen($d['nom']) < 3 || mb_strlen($d['nom']) > 100) {
            $errors['nom'] = 'Le nom doit contenir entre 3 et 100 caractères.';
        } else {
            $st = db()->prepare('SELECT id FROM centre WHERE nom = ?');
            $st->execute([$d['nom']]);
            $other = $st->fetchColumn();
            if ($other !== false && (int) $other !== $ignoreId) {
                $errors['nom'] = 'Un centre porte déjà ce nom.';
            }
        }
        if (mb_strlen($d['adresse']) < 5 || mb_strlen($d['adresse']) > 255) {
            $errors['adresse'] = "L'adresse doit contenir entre 5 et 255 caractères.";
        }
        if (mb_strlen($d['ville']) < 2 || mb_strlen($d['ville']) > 80) {
            $errors['ville'] = 'La ville doit contenir entre 2 et 80 caractères.';
        }
        if (mb_strlen($d['horaires']) < 3 || mb_strlen($d['horaires']) > 80) {
            $errors['horaires'] = 'Indique les horaires (3 à 80 caractères).';
        }
        if ($d['telephone'] !== '' && !preg_match('/^\+?[0-9 ]{6,20}$/', $d['telephone'])) {
            $errors['telephone'] = 'Téléphone invalide (chiffres et espaces, ex. +216 71 000 000).';
        }
        if (!is_numeric($d['latitude']) || $d['latitude'] < 30 || $d['latitude'] > 38) {
            $errors['latitude'] = 'La latitude doit être un nombre entre 30 et 38 (Tunisie).';
        }
        if (!is_numeric($d['longitude']) || $d['longitude'] < 7 || $d['longitude'] > 12) {
            $errors['longitude'] = 'La longitude doit être un nombre entre 7 et 12 (Tunisie).';
        }
        if (empty($d['materials'])) {
            $errors['materials'] = 'Choisis au moins une matière acceptée.';
        }
        return $errors;
    }
}
