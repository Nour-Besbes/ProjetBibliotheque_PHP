<?php
require_once("connexion.php");
header('Content-type:application/json');

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        getAllLivresEmpruntes();
        break;
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);

       // Vérification de l'existence des données nécessaires
      if(isset($data["idUser"]) && isset($data["idLivre"]) && isset($data["delais"])) {
        // Récupérez les données nécessaires pour l'emprunt
        $idUser = $data["idUser"];
        $idLivre = $data["idLivre"];
        $delais = $data["delais"];
        
        // Appelez la fonction pour emprunter le livre
        $message = emprunterLivre($idUser, $idLivre, $delais);
        
       // Affichez le message retourné par la fonction
        echo $message;
    } else {
    // Affichez un message d'erreur si les données nécessaires sont manquantes
       echo "Erreur : Données manquantes pour l'emprunt de livre.";
    }
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

function emprunterLivre($idUser, $idLivre, $delais)
{
    // Étape 1 : Vérifier si le livre est disponible
    
    global $connexion;
    $requete = "SELECT Status FROM livre WHERE idLivre = $idLivre";
    $statement = $connexion->query($requete);
    $resultat = $statement->fetch(PDO::FETCH_ASSOC);
    
    
    if($resultat['Status'] == 'disponible')
    {
        // Étape 2 : Insérer une nouvelle entrée dans la table "emprunter"
        $requete_emprunt = "INSERT INTO emprunter (idUser, idLivre, delais) VALUES ($idUser, $idLivre, '$delais')";
        $connexion->exec($requete_emprunt);
        
        // Étape 3 : Mettre à jour le statut du livre pour le marquer comme "emprunté"
        $requete_update = "UPDATE livre SET Status = 'emprunté' WHERE idLivre = $idLivre";
        $connexion->exec($requete_update);
        
        // Renvoyer un message de succès
        //return "Le livre a été emprunté avec succès.";
        echo json_encode(["message" => "Le livre a été emprunté avec succès."]);
    }
    else
    {
        // Renvoyer un message d'erreur indiquant que le livre n'est pas disponible
        //return "Le livre n'est pas disponible pour l'emprunt.";
        echo json_encode(["message" => "Le livre n'est pas disponible pour l'emprunt."]);
    }
}

?>