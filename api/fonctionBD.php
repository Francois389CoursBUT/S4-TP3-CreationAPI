<?php


function sendJSON($infos, $codeRetour = 200)
{
    header('Access-Control-Allow-Origin: *'); // Tout le monde peut accéder à l'API
    header('Content-Type: application/json; charset=UTF-8'); // On précise que c'est du JSON
    header('Access-Control-Allow-Methods: GET'); // On précise que l'API accepte les requêtes GET

    http_response_code($codeRetour);
    echo json_encode($infos, JSON_UNESCAPED_UNICODE);
}

function getPDO(): PDO
{
    $host = 'localhost';
    $db = 'mezabi3';
    $user = 'root';
    $pass = 'root';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];

    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        // Il y a eu une erreur avec la BD
        $info = [
            'Statut' => 'KO',
            'message' => 'Erreur de connexion à la BD.'
        ];
        sendJSON($info, 500);
    }
    return new PDO("", "", "", []);
}

function getArticlesStockPrix()
{
    try {
        $pdo = getPDO();

        $stmt = $pdo->prepare('SELECT ar.CATEGORIE, ca.DESIGNATION AS CATEGORIE, ar.CODE_ARTICLE, ar.DESIGNATION, ta.CODE_TAILLE, ta.DESIGNATION as TAILLE, co.CODE_COULEUR, co.DESIGNATION as COULEUR, sp.CODE_BARRE, sp.PRIX, sp.STOCK  
				FROM stockprix sp left join articles ar on sp.ARTICLE=ar.ID_ARTICLE 
				LEFT JOIN a_couleurs co ON sp.COULEUR = co.CODE_COULEUR 
				LEFT JOIN a_tailles ta ON sp.TAILLE = ta.CODE_TAILLE
				LEFT JOIN a_categories ca ON ar.CATEGORIE = ca.CODE_CATEGORIE
				order by ar.CATEGORIE, ar.CODE_ARTICLE, ta.CODE_TAILLE, co.DESIGNATION');
        $articles = $stmt->execute();

        $articles = $stmt->fetchAll();
        $stmt->closeCursor();
        $stmt = null;
        $pdo = null;

        sendJSON($articles);
    } catch (\PDOException $e) {
        // Il y a eu une erreur avec la BD
        $info = [
            'Statut' => 'KO',
            'message' => $e->getMessage()
        ];
        sendJSON($info, 500);
    }
}

function modifierCodeBarre($donnee, $id)
{
    if ($donnee == null || !isset($donnee['new_prix']) || !isset($donnee['new_stock'])) {
        $info = [
            'Statut' => 'KO',
            'message' => 'Données manquantes: new_prix et new_stock.'
        ];
        sendJSON($info, 400);
        die();
    }
    try {
        $pdo = getPDO();

        $pdo->beginTransaction();

        $stmt = $pdo->prepare('UPDATE stockprix SET PRIX = :nouveauPrix, STOCK = :nouveauStock WHERE CODE_BARRE = :codeBarre');
        $stmt->bindParam(':nouveauPrix', $donnee['new_prix'], PDO::PARAM_INT);
        $stmt->bindParam(':nouveauStock', $donnee['new_stock'], PDO::PARAM_INT);
        $stmt->bindParam(':codeBarre', $id, PDO::PARAM_STR);
        $stmt->execute();

        $stmt = $pdo->prepare('SELECT ARTICLE, COULEUR, TAILLE FROM stockprix WHERE CODE_BARRE = :codeBarre');
        $stmt->bindParam(':codeBarre', $id, PDO::PARAM_STR);
        $stmt->execute();
        $article = $stmt->fetch();

        $pdo->commit();


        $stmt->closeCursor();
        $stmt = null;
        $pdo = null;

        $info = [
            'Statut' => 'OK',
            'message' => "Entrée modifiée: ARTICLE : " . $article['ARTICLE'] . ", COULEUR : " . $article['COULEUR'] . ", TAILLE : " . $article['TAILLE']
        ];
        sendJSON($info);
    } catch (\PDOException $e) {
        // Il y a eu une erreur avec la BD
        $info = [
            'Statut' => 'KO',
            'message' => $e->getMessage()
        ];
        sendJSON($info, 500);
    }
}

?>