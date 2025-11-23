<?php
$host = '127.0.0.1';
$db   = 'Lumora';      
$user = 'root';        
$pass = '';            
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

//PDO PHP Data Objects - Schnittstelle für DB (SQL-injection-Schutz)
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, //SQL Fehler werden sofort und verständlich angezeigt
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // gibt Daten als Arrays zurück ohne doppelte Inhalte
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('DB connection failed: ' . $e->getMessage());
}
?>
