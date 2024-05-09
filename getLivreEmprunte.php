<?php

header('Content-type:application/json') ;
switch($_SERVER['REQUEST_METHOD'])

    {
        case 'GET':
            if(isset($_GET["idUser"])) {
                getLivresEmpruntesParUtilisateur($_GET["idUser"]);
           
            }
            break;
        
       
    }


function getLivresEmpruntesParUtilisateur($idUser)
{
    
    $json = file_get_contents('php://input');
    header('Content-type:application/json');
    // Inclure le fichier de connexion à la base de données
    require_once("connexion.php");

    // Étape 1 : Préparation de la requête SQL
    $requete = "SELECT livre.* FROM emprunter
                INNER JOIN livre ON emprunter.idLivre = livre.idLivre
                WHERE emprunter.idUser = :idUser";

    // Étape 2 : Préparation de la requête avec PDO
    $statement = $connexion->prepare($requete);

    // Étape 3 : Liaison des paramètres
    $statement->bindParam(':idUser', $idUser, PDO::PARAM_INT);

    // Étape 4 : Exécution de la requête
    $statement->execute();

    // Étape 5 : Récupération des résultats sous forme associative
    $resultat = $statement->fetchAll(PDO::FETCH_ASSOC);

    // Étape 6 : Vérifier s'il y a des résultats
    if (!$resultat) {
        // Si aucun résultat n'est retourné, renvoyer une réponse avec un code de statut 204 (No Content)
        http_response_code(204);
        echo json_encode(["erreur" => "Aucun livre emprunté par cet utilisateur"]);
    } else {
        // Convertir les résultats en format JSON et les renvoyer
        echo json_encode($resultat);
    }
}

?>