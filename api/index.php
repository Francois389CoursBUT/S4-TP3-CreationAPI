<?php
require 'fonctionBD.php';


if (!empty($_GET['demande'])) {
    $url = $_GET['demande'];
    $demande = explode('/', $url);
    switch ($demande[0]) {
        case 'articlesStockPrix':
            getArticlesStockPrix();
            break;
        case 'CB_modifPrixStock':
            $donnee = json_decode(file_get_contents('php://input'), true);
            modifierCodeBarre($donnee, $demande[1]);
            break;
        default:
            $info = [
                'Statut' => 'KO',
                'message' => 'Ressource inexistante.'
            ];
            sendJSON($info, 404);
    }

} else {
    $info = [
        'Statut' => 'KO',
        'message' => 'URL vide.'
    ];
    sendJSON($info, 404);
}