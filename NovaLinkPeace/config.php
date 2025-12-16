<?php
// config.php

$host = "localhost";        
$dbname = "peacelink";   
$username = "root";         
$password = "";             

try {
    // On crée l'objet PDO dans la variable GLOBALE $pdo
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Erreur de connexion à la base : " . $e->getMessage());
}
