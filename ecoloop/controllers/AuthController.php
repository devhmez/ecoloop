<?php
// Controller: register, login, logout
class AuthController
{
    private function csrf(): string
    {
        return $_SESSION['csrf'] ??= bin2hex(random_bytes(16));
    }

    private function checkCsrf(): void
    {
        if (!hash_equals($this->csrf(), $_POST['csrf'] ?? '')) { http_response_code(400); exit('Invalid form token.'); }
    }

    private function page(string $mode, array $values = [], array $errors = []): void
    {
        view('front/auth/auth', [
            'title'  => $mode === 'login' ? 'Log in' : 'Create account',
            'mode'   => $mode,
            'values' => $values + ['name' => '', 'email' => ''],
            'errors' => $errors,
            'csrf'   => $this->csrf(),
            'flash'  => flash(),
        ]);
    }

    public function showLogin(): void    { if (User::current()) redirect('report'); $this->page('login'); }
    public function showRegister(): void { if (User::current()) redirect('report'); $this->page('register'); }

    public function login(): void
    {
        $this->checkCsrf();
        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $user  = User::attempt($email, (string)($_POST['password'] ?? ''));
        if (!$user) {
            http_response_code(422);
            $this->page('login', ['email' => $email], ['form' => 'Wrong email or password.']);
            return;
        }
        User::login((int)$user['id']);
        redirect($user['role'] === 'admin' ? 'admin.reports' : 'report');
    }

    public function register(): void
    {
        $this->checkCsrf();
        $v = [
            'name'     => trim((string)($_POST['name'] ?? '')),
            'email'    => strtolower(trim((string)($_POST['email'] ?? ''))),
            'password' => (string)($_POST['password'] ?? ''),
            'confirm'  => (string)($_POST['confirm'] ?? ''),
        ];
        $errors = User::validateRegister($v);
        if ($errors) {
            http_response_code(422);
            $this->page('register', ['name' => $v['name'], 'email' => $v['email']], $errors);
            return;
        }
        $id = User::create($v['name'], $v['email'], $v['password']);
        User::login($id);
        redirect('report');
    }

    public function logout(): void
    {
        User::logout();
        header('Location: ' . url('login'));
        exit;
    }
}