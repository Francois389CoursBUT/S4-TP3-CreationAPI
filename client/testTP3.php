<?php


function appelAPI($apiUrl, $apiKey, &$http_status, $typeRequete = "GET", $donnees = null)
{
    // Interrogation de l'API
    // $apiUrl Url d'appel de l'API
    // $http_status Retourne le statut HTTP de la requete
    // $typeRequete = GET / POST / DELETE / PUT, GET par défaut si non précisé
    // $donnees = données envoyées au format JSON en PUT ET POST, rien si GET ou DELETE
    // La fonction retourne le résultat en format JSON

    $curl = curl_init();                                    // Initialisation

    curl_setopt($curl, CURLOPT_URL, $apiUrl);                // Url de l'API à appeler
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);            // Retour dans une chaine au lieu de l'afficher
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);        // Désactive test certificat
    curl_setopt($curl, CURLOPT_FAILONERROR, false);

    // Parametre pour le type de requete
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $typeRequete);

    // Si des données doivent être envoyées
    if (!empty($donnees)) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, $donnees);
        curl_setopt($curl, CURLOPT_POST, true);
    }

    $httpheader [] = "Content-Type:application/json";

    if (!empty($apiKey)) {
        // Ajout de la clé API dans l'entete si elle existe (pour tous les appels sauf login)
        $httpheader = ['APIKEYDEMONAPPLI: ' . $apiKey];
    }
    curl_setopt($curl, CURLOPT_HTTPHEADER, $httpheader);

    // À utiliser sur le réseau des PC IUT, pas en WIFI, pas sur une autre connexion
    // $proxy="http://cache.iut-rodez.fr:8080";
    // curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, true);
    // curl_setopt($curl, CURLOPT_PROXY,$proxy ) ;
    ///////////////////////////////////////////////////////////////////////////////

    $result = curl_exec($curl);                                      // Exécution
    $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);    // Récupération statut

    curl_close($curl);                                        // Cloture curl

    return json_decode($result, true);                    // Retourne la collection
}


?>


<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8"/>
        <title>TP3 API STOCK</title>

        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="bootstrap/css/bootstrap.css">
    </head>
    <body>

    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <h1>Modification des stocks et prix des articles</h1>

                <?php

                if (isset($_GET['prix']) && isset($_GET['stock']) && isset($_GET['code_barre'])) {

                    $new_prix = htmlentities($_GET['prix']);
                    $new_stock = htmlentities($_GET['stock']);
                    $code_barre = htmlentities($_GET['code_barre']);

                    if (!is_numeric($new_prix) || !is_numeric($new_stock)) {
                        ?>
                        <div class="alert alert-danger" role="alert">
                            Les valeurs de prix et de stock doivent être numériques.
                        </div>
                        <?php
                    } else if (!is_numeric($code_barre)) {
                        ?>
                        <div class="alert alert-danger" role="alert">
                            Ne pas modifier le code barre !
                        </div>
                        <?php
                    } else {
                        $nouvelleDonnee = json_encode(array("new_prix" => $_GET['prix'], "new_stock" => $_GET['stock']));
                        $urlsApiModification = "http://localhost/TP3-CreationAPI/api/CB_modifPrixStock/";
                        $reponseModification = appelAPI($urlsApiModification . $code_barre, "", $http_status_modification, "PUT", $nouvelleDonnee);

                        if ($http_status_modification == 200) {
                            ?>
                            <div class="alert alert-success" role="alert">
                                Modification effectuée avec succès.<br>
                                <br>
                            </div>
                            <?php
                        } else {
                            ?>
                            <div class="alert alert-danger" role="alert">
                                Erreur lors de la modification.<br>
                                <br>
                                <?php
                                echo "Code d'erreur HTTP : " . $http_status_modification . "<br>";
                                var_dump($reponseModification); ?>
                            </div>
                            <?php
                        }
                    }
                }

                $resultat = appelAPI("http://localhost/TP3-CreationAPI/api/articlesStockPrix", "", $http_status, "GET", null);

                if ($http_status != 200) {
                    ?>
                    <div class="alert alert-danger" role="alert">
                        Erreur lors de la récupération des données.
                        <br>
                        <br>
                        <?php echo $resultat["message"] ?>
                    </div>
                    <?php
                } else {

                    for ($i = 0; $i < count($resultat); $i++) {

                        ?>
                        <form action="testTP3.php" method="get" id="form<?php echo $i ?>"></form>
                        <?php
                    }
                    ?>
                    <table class='table table-striped table-bordered'>

                        <tr>
                            <th>Categorie</th>
                            <th>Code Article</th>
                            <th>D&eacute;signation</th>
                            <th>Taille</th>
                            <th>Couleur</th>
                            <th>Code Barre</th>
                            <th>Prix</th>
                            <th>Stock</th>
                            <th>Validation</th>
                        </tr>

                        <?php

                        $cpt = 0;
                        foreach ($resultat as $ligne) {

                            $formID = "form" . $cpt;
                            ?>

                            <tr>
                                <td><?php echo $ligne['CATEGORIE']; ?></td>
                                <td><?php echo $ligne['CODE_ARTICLE']; ?></td>
                                <td><?php echo $ligne['DESIGNATION']; ?></td>
                                <td><?php echo $ligne['TAILLE']; ?></td>
                                <td><?php echo $ligne['COULEUR']; ?></td>
                                <td><?php echo $ligne['CODE_BARRE']; ?></td>

                                <td>
                                    <input type="number" name="prix" form="<?php echo $formID ?>"
                                           value="<?php echo $ligne['PRIX'] ?>">
                                </td>
                                <td>
                                    <input type="number" name="stock" form="<?php echo $formID ?>"
                                           value="<?php echo $ligne['STOCK'] ?>">
                                </td>
                                <td>
                                    <input hidden form="<?php echo $formID ?>" name="code_barre"
                                           value="<?php echo $ligne['CODE_BARRE'] ?>">
                                    <input form="<?php echo $formID ?>" class='btn btn-primary' type="submit"
                                           value="Modifier">
                                </td>
                            </tr>

                            <?php
                            $cpt++;
                        }

                        ?>
                    </table>
                    <?php
                }
                ?>

            </div>
        </div>
    </div>
    <br><br>
    </body>
</html>