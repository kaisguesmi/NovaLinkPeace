<?php
// Test simple pour déboguer handleLoginWithFace
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== TEST FACE LOGIN ===\n";

// Simuler une requête POST
$_POST['action'] = 'login_with_face';
$_POST['email'] = 'kais@gmail.com'; // Remplacez par votre email

echo "POST simulé:\n";
print_r($_POST);

// Inclure le contrôleur
echo "\nInclusion du contrôleur...\n";
include 'UtilisateurController.php';

echo "\n=== FIN TEST ===\n";
?>
