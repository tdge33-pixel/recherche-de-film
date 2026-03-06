<?php
// db.php
$host = 'localhost';
$db   = 'recherche film'; 
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // On ne met pas de "echo" ici en production, 
    // mais pour tes tests, tu peux laisser une trace.
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}