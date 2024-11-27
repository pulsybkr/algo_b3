<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

require '/var/www/html/services/service.php';

$db = new PDO('sqlite:/var/www/html/database/database.db');

// Fonction pour gérer les routes
function handleRequest($db) {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    // Définir des routes personnalisées
    switch ($uri) {
        case '/livres':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $livres = afficherLivres($db);
                echo json_encode($livres);
            }
            break;
        case '/ajouter':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $nom = $data['nom'] ?? '';
                $description = $data['description'] ?? '';
                $disponible = isset($data['disponible']) ? (int)$data['disponible'] : 0;

                if ($nom && $description) {
                    $result = creerLivre($db, $nom, $description, $disponible);
                    echo json_encode(['status' => 'success', 'message' => $result]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Nom et description requis.']);
                }
            }
            break;
        case '/modifier':
            if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                $data = json_decode(file_get_contents('php://input'), true);
                $result = modifierLivre($db, $data['id'], $data['nom'], $data['description'], $data['disponible']);
                echo json_encode(['status' => $result]);
            }
            break;
        case '/supprimer':
            if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
                $data = json_decode(file_get_contents('php://input'), true);
                $result = supprimerLivre($db, $data['id']);
                echo json_encode(['status' => $result]);
            }
            break;
        case '/trier':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $colonne = $_GET['colonne'] ?? 'nom'; // Par défaut, trier par nom
                $sortedLivres = trierLivres($db, $colonne);
                echo json_encode($sortedLivres);
            }
            break;
        case '/rechercher':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $colonne = $_GET['colonne'] ?? 'nom';
                $valeur = $_GET['valeur'] ?? '';
                $resultat = rechercherLivre($db, $colonne, $valeur);
                echo json_encode($resultat);
            }
            break;
        case '/historique':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $historyFile = '/var/www/html/database/historique.txt';
                if (file_exists($historyFile)) {
                    $historique = file_get_contents($historyFile);
                    echo json_encode(['historique' => $historique]);
                } else {
                    echo json_encode(['error' => 'Aucun historique trouvé.']);
                }
            }
            break;
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Route non trouvée']);
            break;
    }
}

handleRequest($db);
?>