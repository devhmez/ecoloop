<?php
// Contrôleur Auth : inscription, connexion, déconnexion, mot de passe oublié
require_once __DIR__ . '/../models/User.php';

class AuthController
{
    public function register(): void
    {
        $errors = [];
        $name = '';
        $email = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            checkCsrf();
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $errors = User::validate($name, $email)
                    + User::validatePassword($_POST['password'] ?? '', $_POST['confirm'] ?? '');
            if (empty($errors)) {
                User::create($name, $email, $_POST['password']);
                flash('Compte créé ! Tu peux te connecter.');
                redirect('login');
            }
        }
        view('auth/register', compact('errors', 'name', 'email'));
    }

    public function login(): void
    {
        $errors = [];
        $email = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            checkCsrf();
            $email = trim($_POST['email'] ?? '');
            $user = User::findByEmail($email);
            if ($user !== null && password_verify($_POST['password'] ?? '', $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user'] = ['id' => (int) $user['id'], 'name' => $user['name'], 'role' => $user['role']];
                redirect($user['role'] === 'admin' ? 'admin/users' : 'profile');
            }
            $errors['login'] = 'Email ou mot de passe incorrect.';
        }
        view('auth/login', compact('errors', 'email'));
    }

    public function logout(): void
    {
        checkCsrf();
        $_SESSION = [];
        session_destroy();
        session_start();
        flash('Tu es déconnecté.');
        redirect('login');
    }

    public function forgot(): void
    {
        $errors = [];
        $link = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            checkCsrf();
            $user = User::findByEmail(trim($_POST['email'] ?? ''));
            if ($user === null) {
                $errors['email'] = 'Aucun compte avec cet email.';
            } else {
                $token = bin2hex(random_bytes(32));
                User::setResetToken((int) $user['id'], $token);
                $link = 'index.php?r=reset&token=' . $token;
            }
        }
        view('auth/forgot', compact('errors', 'link'));
    }

    public function reset(): void
    {
        $token = $_GET['token'] ?? '';
        $user = User::findByResetToken($token);
        if ($user === null) {
            flash('Lien invalide ou expiré.');
            redirect('forgot');
        }
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            checkCsrf();
            $errors = User::validatePassword($_POST['password'] ?? '', $_POST['confirm'] ?? '');
            if (empty($errors)) {
                User::updatePassword((int) $user['id'], $_POST['password']);
                flash('Mot de passe changé ! Connecte-toi.');
                redirect('login');
            }
        }
        view('auth/reset', compact('errors', 'token'));
    }
}
