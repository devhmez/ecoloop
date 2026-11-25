<?php
$host = 'localhost';
$port = 3307;
$name = 'ecoloop';
$user = 'root';
$pass = '';

try {
    return new PDO("mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    exit('Database connection failed: ' . $e->getMessage());
}