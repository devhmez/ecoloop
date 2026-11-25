<?php
// Contrôleur User : profil (front office) et gestion des utilisateurs (back office)
require_once __DIR__ . '/../models/User.php';

class UserController
{
    // FRONT : l'utilisateur connecté modifie son nom et son email
    public function profile(): void
    {
        requireLogin();
        $me = User::find(currentUser()['id']);
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            checkCsrf();
            $me['name'] = trim($_POST['name'] ?? '');
            $me['email'] = trim($_POST['email'] ?? '');
            $errors = User::validate($me['name'], $me['email'], $me['id']);
            if (empty($errors)) {
                User::update($me['id'], $me['name'], $me['email'], $me['role']);
                $_SESSION['user']['name'] = $me['name'];
                flash('Profil mis à jour.');
                redirect('profile');
            }
        }
        view('user/profile', compact('me', 'errors'));
    }

    // FRONT : changer son mot de passe (il faut connaître l'ancien)
    public function password(): void
    {
        requireLogin();
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            checkCsrf();
            if (!User::checkPassword(currentUser()['id'], $_POST['current'] ?? '')) {
                $errors['current'] = 'Mot de passe actuel incorrect.';
            }
            $errors += User::validatePassword($_POST['password'] ?? '', $_POST['confirm'] ?? '');
            if (empty($errors)) {
                User::updatePassword(currentUser()['id'], $_POST['password']);
                flash('Mot de passe modifié.');
                redirect('profile');
            }
        }
        view('user/password', compact('errors'));
    }

    // BACK : liste des utilisateurs avec recherche
    public function index(): void
    {
        requireAdmin();
        $q = trim($_GET['q'] ?? '');
        $users = User::all($q);
        $stats = User::stats();
        view('admin/users', compact('users', 'q', 'stats'));
    }

    // BACK : ajouter (sans id) ou modifier (avec id) un utilisateur
    public function form(): void
    {
        requireAdmin();
        $id = (int) ($_GET['id'] ?? 0);
        $u = $id > 0 ? User::find($id) : ['name' => '', 'email' => '', 'role' => 'citoyen'];
        if ($u === null) {
            redirect('admin/users');
        }
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            checkCsrf();
            $u['name'] = trim($_POST['name'] ?? '');
            $u['email'] = trim($_POST['email'] ?? '');
            $u['role'] = ($_POST['role'] ?? '') === 'admin' ? 'admin' : 'citoyen';
            if ($id === currentUser()['id']) {
                $u['role'] = 'admin';
            }
            $errors = User::validate($u['name'], $u['email'], $id ?: null);
            if ($id === 0) {
                $errors += User::validatePassword($_POST['password'] ?? '', $_POST['password'] ?? '');
            }
            if (empty($errors)) {
                if ($id > 0) {
                    User::update($id, $u['name'], $u['email'], $u['role']);
                    flash('Utilisateur modifié.');
                } else {
                    User::create($u['name'], $u['email'], $_POST['password'], $u['role']);
                    flash('Utilisateur ajouté.');
                }
                redirect('admin/users');
            }
        }
        view('admin/form', compact('u', 'id', 'errors'));
    }

    // BACK : supprimer un utilisateur (formulaire POST, jamais un simple lien)
    public function delete(): void
    {
        requireAdmin();
        checkCsrf();
        $id = (int) ($_POST['id'] ?? 0);
        if ($id === currentUser()['id']) {
            flash('Tu ne peux pas supprimer ton propre compte.');
        } else {
            User::delete($id);
            flash('Utilisateur supprimé.');
        }
        redirect('admin/users');
    }
}
