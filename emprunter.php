<?php
require_once("connexion.php");
header('Content-type:application/json');

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        getAllLivresEmpruntes();
        break;
}

function getAllLivresEmpruntes()
{
    global $connexion;
    
    // Requête SQL pour sélectionner tous les champs des livres empruntés
    $requete = "SELECT livre.* FROM emprunter
                INNER JOIN livre ON emprunter.idLivre = livre.idLivre
                GROUP BY livre.idLivre"; // Utilisation de GROUP BY pour s'assurer que chaque livre n'est retourné qu'une seule fois
    
    // Exécuter la requête
    $statement = $connexion->query($requete);
    
    // Récupérer les résultats sous forme associative
    $resultat = $statement->fetchAll(PDO::FETCH_ASSOC);
    
    // Si aucun résultat n'est retourné, renvoyer une réponse avec un code de statut 204 (No Content)
    if (!$resultat) {
        http_response_code(204);
        echo json_encode(["erreur" => "Aucun livre emprunté"]);
    } else {
        // Convertir les résultats en format JSON et les renvoyer
        echo json_encode($resultat);
    }
}

?>