<?php
// Contrôleur Centre : centres de recyclage (front office : citoyens, back office : admin)
require_once __DIR__ . '/../models/Centre.php';

class CentreController
{
    // FRONT : liste publique des centres actifs, avec filtre par matière et centre le plus proche
    public function index(): void
    {
        $f = $this->filters();
        $f['status'] = 'actif'; // les citoyens ne voient que les centres actifs
        $rows = Centre::search($f);
        $p = Centre::paginate($rows, $f['page'], 6);
        $url = fn(array $over = []): string => $this->url('centres', $f, $over);
        view('centres/index', ['f' => $f, 'p' => $p, 'url' => $url, 'points' => Centre::POINTS]);
    }

    // BACK : liste des centres (recherche, filtres, tri, pagination)
    public function adminIndex(): void
    {
        requireAdmin();
        $f = $this->filters();
        $rows = Centre::search($f);
        $p = Centre::paginate($rows, $f['page'], 5);
        $url = fn(array $over = []): string => $this->url('admin/centres', $f, $over);
        $exportUrl = $this->url('admin/centres-export', $f, ['page' => null]);
        view('admin/centres', ['f' => $f, 'p' => $p, 'url' => $url, 'exportUrl' => $exportUrl]);
    }

    // BACK : ajouter (sans id) ou modifier (avec id) un centre
    public function adminForm(): void
    {
        requireAdmin();
        $id = (int) ($_GET['id'] ?? 0);
        $c = ['nom' => '', 'adresse' => '', 'ville' => '', 'horaires' => 'Lun-Sam 08:00-18:00', 'telephone' => '',
              'latitude' => '', 'longitude' => '', 'materials' => [], 'actif' => 1];
        if ($id > 0) {
            $c = Centre::find($id);
            if ($c === null) {
                flash('Centre introuvable.');
                redirect('admin/centres');
            }
        }
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            checkCsrf();
            $c = [
                'nom'       => trim($_POST['nom'] ?? ''),
                'adresse'   => trim($_POST['adresse'] ?? ''),
                'ville'     => trim($_POST['ville'] ?? ''),
                'horaires'  => trim($_POST['horaires'] ?? ''),
                'telephone' => trim($_POST['telephone'] ?? ''),
                'latitude'  => str_replace(',', '.', trim($_POST['latitude'] ?? '')),
                'longitude' => str_replace(',', '.', trim($_POST['longitude'] ?? '')),
                'materials' => array_values(array_intersect(array_keys(Centre::MATERIALS), (array) ($_POST['materials'] ?? []))),
                'actif'     => isset($_POST['actif']) ? 1 : 0,
            ];
            $errors = Centre::validate($c, $id ?: null);
            if (empty($errors)) {
                if ($id > 0) {
                    Centre::update($id, $c);
                    flash('Centre modifié.');
                } else {
                    Centre::create($c);
                    flash('Centre ajouté.');
                }
                redirect('admin/centres');
            }
        }
        view('admin/centre-form', compact('c', 'id', 'errors'));
    }

    // BACK : supprimer un centre (formulaire POST avec jeton CSRF)
    public function adminDelete(): void
    {
        requireAdmin();
        checkCsrf();
        $id = (int) ($_POST['id'] ?? 0);
        if (Centre::find($id) === null) {
            flash('Centre introuvable.');
        } else {
            Centre::delete($id);
            flash('Centre supprimé.');
        }
        redirect('admin/centres');
    }

    // BACK : export CSV de la liste filtrée (s'ouvre dans Excel)
    public function adminExport(): void
    {
        requireAdmin();
        $rows = Centre::search($this->filters());
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="centres-recyclage.csv"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF"); // BOM pour qu'Excel lise bien les accents
        fputcsv($out, ['ID', 'Nom', 'Adresse', 'Ville', 'Matières', 'Horaires', 'Téléphone', 'Statut', 'Latitude', 'Longitude'], ';', '"', '\\');
        foreach ($rows as $r) {
            $matieres = implode(', ', array_map(fn($k) => Centre::MATERIALS[$k] ?? $k, $r['materials']));
            fputcsv($out, [
                $r['id'], $this->csvSafe($r['nom']), $this->csvSafe($r['adresse']), $this->csvSafe($r['ville']), $matieres,
                $this->csvSafe($r['horaires']), $r['telephone'] ?? '', $r['actif'] ? 'Actif' : 'Inactif', $r['latitude'], $r['longitude'],
            ], ';', '"', '\\');
        }
        fclose($out);
        exit;
    }

    // Lit et nettoie les paramètres de l'URL : recherche, matière, statut, tri, page, position
    private function filters(): array
    {
        $get = fn(string $k): string => is_string($_GET[$k] ?? null) ? $_GET[$k] : '';
        $lat = is_numeric($get('lat')) ? (float) $get('lat') : null;
        $lng = is_numeric($get('lng')) ? (float) $get('lng') : null;
        if ($lat === null || $lng === null || abs($lat) > 90 || abs($lng) > 180) {
            $lat = $lng = null;
        }
        $sort = in_array($get('sort'), Centre::SORTS, true) ? $get('sort') : ($lat !== null ? 'distance' : 'nom');
        if ($sort === 'distance' && $lat === null) {
            $sort = 'nom';
        }
        return [
            'q'      => mb_substr(trim($get('q')), 0, 80),
            'cat'    => isset(Centre::MATERIALS[$get('cat')]) ? $get('cat') : '',
            'status' => in_array($get('status'), ['actif', 'inactif'], true) ? $get('status') : '',
            'sort'   => $sort,
            'dir'    => $get('dir') === 'desc' ? 'desc' : 'asc',
            'page'   => max(1, (int) $get('page')),
            'lat'    => $lat,
            'lng'    => $lng,
        ];
    }

    // Construit une adresse comme index.php?r=centres&cat=verre&page=2 (valeurs vides ignorées)
    private function url(string $route, array $f, array $over = []): string
    {
        $keep = array_intersect_key($f, array_flip(['q', 'cat', 'status', 'sort', 'dir', 'page', 'lat', 'lng']));
        $params = array_merge(['r' => $route], $keep, $over);
        $params = array_filter($params, fn($v) => $v !== '' && $v !== null);
        if (isset($params['page']) && (int) $params['page'] === 1) {
            unset($params['page']);
        }
        return 'index.php?' . http_build_query($params);
    }

    // Empêche une cellule Excel de commencer par = + - @ (injection de formule)
    private function csvSafe(string $v): string
    {
        return preg_match('/^[=+\-@]/', $v) ? "'" . $v : $v;
    }
}
