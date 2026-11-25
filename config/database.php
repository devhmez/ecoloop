<?php
// Connexion à la base de données avec PDO (une seule connexion pour tout le site)
// XAMPP : utilisateur root, mot de passe vide. MAMP : mot de passe 'root', port 8889.
const DB_HOST = 'localhost';
const DB_NAME = 'ecoloop';
const DB_USER = 'root';
const DB_PASS = '';

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
    return $pdo;
}
