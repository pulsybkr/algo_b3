<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

require '/var/www/html/services/service.php';

$db = new PDO('sqlite:/var/www/html/database/database.db');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $livres = afficherLivres($db);
    echo json_encode($livres);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    addItem($db, $data['name']);
    echo json_encode(['status' => 'success']);
}
?>