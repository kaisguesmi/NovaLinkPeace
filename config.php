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
    error_log("PDO Connection Error: " . $e->getMessage());
    
    // Si c'est une requête API, répondre en JSON
    if (strpos($_SERVER['REQUEST_URI'] ?? '', 'api_') !== false) {
        header("Content-Type: application/json; charset=utf-8");
        http_response_code(500);
        die(json_encode(['success' => false, 'error' => 'Erreur de connexion à la base de données']));
    }
    
    // Sinon, afficher un message d'erreur HTML
    die("Erreur de connexion à la base de données : " . htmlspecialchars($e->getMessage()));
}