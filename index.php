<?php
// Contrôleur frontal : toutes les pages passent par ici (index.php?r=nom-de-la-page)
session_start();
require_once __DIR__ . '/helpers/functions.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/CentreController.php';

$auth = new AuthController();
$users = new UserController();
$centres = new CentreController();

$routes = [
    'register'    => [$auth, 'register'],
    'login'       => [$auth, 'login'],
    'logout'      => [$auth, 'logout'],
    'forgot'      => [$auth, 'forgot'],
    'reset'       => [$auth, 'reset'],
    'profile'     => [$users, 'profile'],
    'password'    => [$users, 'password'],
    'admin/users' => [$users, 'index'],
    'admin/form'  => [$users, 'form'],
    'admin/delete' => [$users, 'delete'],
    'centres'              => [$centres, 'index'],
    'admin/centres'        => [$centres, 'adminIndex'],
    'admin/centre-form'    => [$centres, 'adminForm'],
    'admin/centre-delete'  => [$centres, 'adminDelete'],
    'admin/centres-export' => [$centres, 'adminExport'],
];

$route = $_GET['r'] ?? (currentUser() ? 'profile' : 'login');
if (!isset($routes[$route])) {
    http_response_code(404);
    exit('Page introuvable.');
}
$routes[$route]();
