<?php
// Contrôleur Materiau : gestion des matériaux dans le back office (CRUD)
require_once __DIR__ . '/../models/Materiau.php';

class MateriauController
{
    // READ : liste des matériaux
    public function index(): void
    {
        requireAdmin();
        $materiaux = Materiau::all();
        view('admin/materiaux', compact('materiaux'));
    }

    // CREATE (sans id) ou UPDATE (avec id)
    public function form(): void
    {
        requireAdmin();
        $id = (int) ($_GET['id'] ?? 0);
        $vide = ['nom' => '', 'poubelle' => '', 'couleur' => '#12855a', 'points' => '0', 'mots_cles' => '', 'conseil' => ''];
        $m = $id > 0 ? Materiau::find($id) : $vide;
        if ($m === null) {
            redirect('admin/materiaux');
        }
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            checkCsrf();
            foreach (array_keys($vide) as $champ) {
                $m[$champ] = trim($_POST[$champ] ?? '');
            }
            $errors = Materiau::validate($m, $id ?: null);
            if (empty($errors)) {
                if ($id > 0) {
                    Materiau::update($id, $m);
                    flash('Matériau modifié.');
                } else {
                    Materiau::create($m);
                    flash('Matériau ajouté.');
                }
                redirect('admin/materiaux');
            }
        }
        view('admin/materiau_form', compact('m', 'id', 'errors'));
    }

    // DELETE : refusé si des scans utilisent ce matériau
    public function delete(): void
    {
        requireAdmin();
        checkCsrf();
        $id = (int) ($_POST['id'] ?? 0);
        $nb = Materiau::countScans($id);
        if ($nb > 0) {
            flash("Impossible : $nb scan(s) utilisent ce matériau.");
        } else {
            Materiau::delete($id);
            flash('Matériau supprimé.');
        }
        redirect('admin/materiaux');
    }
}
