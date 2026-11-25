<?php
// Front controller: every request goes through here (index.php?r=<route>)
session_start();
require __DIR__ . '/helpers/functions.php';
require __DIR__ . '/models/User.php';
require __DIR__ . '/models/WasteReport.php';
require __DIR__ . '/controllers/AuthController.php';
require __DIR__ . '/controllers/WasteReportController.php';

$method = $_SERVER['REQUEST_METHOD'];
$route  = $_GET['r'] ?? 'report';
$c = new WasteReportController();
$a = new AuthController();

$routes = [
    'GET login'           => fn() => $a->showLogin(),
    'POST login'          => fn() => $a->login(),
    'GET register'        => fn() => $a->showRegister(),
    'POST register'       => fn() => $a->register(),
    'GET logout'          => fn() => $a->logout(),

    'GET report'          => fn() => $c->index(),
    'POST report'         => fn() => $c->store(),
    'GET admin.reports'   => fn() => $c->adminList(),
    'GET admin.report'    => fn() => $c->adminShow(),
    'POST admin.status'   => fn() => $c->adminStatus(),
    'GET api.reports'     => fn() => $c->apiReports(),
];

($routes["$method $route"] ?? function () { http_response_code(404); echo 'Page not found.'; })();