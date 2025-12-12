<?php
// index.php - Routeur Principal

// Affichage des erreurs pour le débogage (à retirer en prod)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'controller/OfferController.php';

$controller = new OfferController();
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

switch ($action) {
    // --- Actions CLIENT ---
    case 'apply': 
        $controller->showApplicationForm(); 
        break;
    case 'submit_application': 
        $controller->submitApplication(); 
        break;

    // --- Actions ADMIN ---
    case 'create': 
        $controller->createOffer(); 
        break;
    case 'store': 
        $controller->storeOffer(); 
        break;
    case 'edit': 
        $controller->editOffer(); 
        break;
    case 'update': 
        $controller->updateOffer(); 
        break;
    case 'delete': 
        $controller->deleteOffer(); 
        break;
    case 'list_applications': 
        $controller->listApplications(); 
        break;
    case 'update_status': 
        $controller->updateApplicationStatus(); 
        break;

    // --- NOUVELLE ROUTE : AJAX IA ---
    case 'generate_description':
        $controller->generateAiDescription();
        break;

    // --- Action par défaut ---
    case 'list':
    default: 
        $controller->listOffers(); 
        break;
}
?>