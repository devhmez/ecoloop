<?php
// Contrôleur frontal : toutes les pages passent par ici (index.php?r=nom-de-la-page)
session_start();
require_once __DIR__ . '/helpers/functions.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/ScanController.php';
require_once __DIR__ . '/controllers/MateriauController.php';

$auth = new AuthController();
$users = new UserController();
$scans = new ScanController();
$materiaux = new MateriauController();

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
    // Module Scanner IA
    'scanner'               => [$scans, 'scanner'],
    'scanner/historique'    => [$scans, 'history'],
    'admin/scans'           => [$scans, 'index'],
    'admin/scan-delete'     => [$scans, 'delete'],
    'admin/materiaux'       => [$materiaux, 'index'],
    'admin/materiau-form'   => [$materiaux, 'form'],
    'admin/materiau-delete' => [$materiaux, 'delete'],
];

$route = $_GET['r'] ?? (currentUser() ? 'profile' : 'login');
if (!isset($routes[$route])) {
    http_response_code(404);
    exit('Page introuvable.');
}
$routes[$route]();
