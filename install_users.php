<?php
// À exécuter UNE SEULE FOIS après avoir importé database.sql :
//   php install_users.php        (ou ouvrir http://localhost/ecoloop_php/install_users.php)
// Puis SUPPRIMER ce fichier. Il crée les comptes de démonstration.
require_once __DIR__ . '/includes/db.php';

if (PHP_SAPI !== 'cli' && !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit('Run this script from localhost only.');
}
header('Content-Type: text/plain; charset=utf-8');

$pdo = db();
if ((int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() > 0) {
    exit("Users already exist. Nothing done. Delete install_users.php.\n");
}

$password = 'Password123!';   // mot de passe de démonstration : à changer (admin > Users)
$hash = password_hash($password, PASSWORD_DEFAULT);

$collectors = [];
foreach ($pdo->query("SELECT id, name FROM collectors") as $c) {
    $collectors[$c['name']] = (int)$c['id'];
}

$accounts = [
    ['Admin',            'admin@ecoloop.test',  'admin',   null],
    ['Amira B.',         'amira@ecoloop.test',  'citizen', null],
    ['Hichem Mansouri',  'hichem@ecoloop.test', 'driver',  $collectors['Hichem Mansouri'] ?? null],
    ['Nour Gharbi',      'nour@ecoloop.test',   'driver',  $collectors['Nour Gharbi'] ?? null],
    ['Amine Jlassi',     'amine@ecoloop.test',  'driver',  $collectors['Amine Jlassi'] ?? null],
];

$ins = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, collector_id) VALUES (?, ?, ?, ?, ?)");
foreach ($accounts as [$name, $email, $role, $cid]) {
    $ins->execute([$name, $email, $hash, $role, $cid]);
    echo str_pad($role, 8) . $email . "\n";
}
echo "\nPassword for all accounts: $password\nNow DELETE install_users.php.\n";
