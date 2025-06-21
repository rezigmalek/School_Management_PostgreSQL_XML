<?php
// db.php

$host = 'localhost';
$port = '5432';
$dbname = 'cawa_projet';
$user = 'postgres';
$password = 'postgres'; // Remplace par ton mot de passe réel

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    // echo "Connexion réussie à PostgreSQL 🐘";
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
