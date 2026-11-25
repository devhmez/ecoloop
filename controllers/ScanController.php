<?php
// Contrôleur Scan : le scanner IA (front office) et la liste des scans (back office)
require_once __DIR__ . '/../models/Materiau.php';
require_once __DIR__ . '/../models/Scan.php';

class ScanController
{
    // FRONT : page du scanner. L'IA tourne dans le navigateur, puis le formulaire envoie ce qu'elle a vu.
    public function scanner(): void
    {
        requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            checkCsrf();
            $labels = array_slice((array) ($_POST['labels'] ?? []), 0, 3);
            $probas = array_slice((array) ($_POST['probas'] ?? []), 0, 3);
            $result = ['found' => false, 'objet' => mb_substr(trim((string) ($labels[0] ?? '')), 0, 120)];
            foreach ($labels as $i => $label) {
                $label = mb_substr(trim((string) $label), 0, 120);
                $materiau = Materiau::matchLabel($label);
                if ($materiau !== null) {
                    $confiance = max(0, min(100, (int) round((float) ($probas[$i] ?? 0) * 100)));
                    Scan::create(currentUser()['id'], $materiau, $label, $confiance);
                    $result = ['found' => true, 'objet' => $label, 'confiance' => $confiance, 'materiau' => $materiau];
                    break;
                }
            }
            $_SESSION['scan_result'] = $result;
            redirect('scanner');
        }
        $result = $_SESSION['scan_result'] ?? null;
        unset($_SESSION['scan_result']);
        $materiaux = Materiau::all();
        view('scanner/scan', compact('result', 'materiaux'));
    }

    // FRONT : historique des scans du citoyen connecté
    public function history(): void
    {
        requireLogin();
        $scans = Scan::byUser(currentUser()['id']);
        view('scanner/history', compact('scans'));
    }

    // BACK : tous les scans, avec filtre par matériau
    public function index(): void
    {
        requireAdmin();
        $filtre = (int) ($_GET['materiau'] ?? 0);
        $scans = Scan::all($filtre);
        $materiaux = Materiau::all();
        view('admin/scans', compact('scans', 'materiaux', 'filtre'));
    }

    // BACK : supprimer un scan (ses points sont retirés au citoyen)
    public function delete(): void
    {
        requireAdmin();
        checkCsrf();
        Scan::delete((int) ($_POST['id'] ?? 0));
        flash('Scan supprimé, les points ont été retirés.');
        redirect('admin/scans');
    }
}
