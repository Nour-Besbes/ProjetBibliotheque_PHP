<?php

header('Content-type:application/json') ;
switch($_SERVER['REQUEST_METHOD'])

    {
        case 'GET':
            if(isset($_GET["category"])) {
                getLivreByCategory($_GET["category"]);
            } elseif(isset($_GET["title"])) {
                getLivreByTitle($_GET["title"]);
            }
            break;
        
       
    }

function getLivreByCategory($categorie)
{

  
    $json = file_get_contents('php://input');
  
    // Étape 1 : Préparation de la requête
    $requete = "SELECT * FROM livre WHERE Categorie = '$categorie'";
    
    // Étape 2 : Connexion avec la base de données, création de l'objet connexion
    require_once("connexion.php");
    
    // Étape 3 : Exécuter la requête
    $statement = $connexion->query($requete);
    
    // Étape 4 : Récupérer le résultat sous forme associative
    $resultat = $statement->fetchAll(PDO::FETCH_ASSOC);
    
    // Ajouter l'entête pour spécifier le format retourné par le fichier
    header('Content-type:application/json');
    
    // Si pas de livres trouvés dans cette catégorie
    if(empty($resultat))
    {
        http_response_code(204);
        $msg = array("erreur"=> "Aucun livre trouvé dans la catégorie '$categorie'");
        echo json_encode($msg);
    }
    else
    {
        // Étape 5 : Convertir les données en JSON
        $json = json_encode($resultat);
        // Afficher les données JSON
        echo $json;
    }
}
function getLivreByTitle($title){

    $json = file_get_contents('php://input');
   
     // Étape 1 : Préparation de la requête
     $requete = "SELECT * FROM livre WHERE Titre LIKE '%$title%'";
    
     // Étape 2 : Connexion avec la base de données, création de l'objet connexion
     require_once("connexion.php");
     
     // Étape 3 : Exécuter la requête
     $statement = $connexion->query($requete);
     
     // Étape 4 : Récupérer le résultat sous forme associative
     $resultat = $statement->fetchAll(PDO::FETCH_ASSOC);
     
     // Ajouter l'entête pour spécifier le format retourné par le fichier
     header('Content-type:application/json');
     
     // Si pas de livres trouvés avec ce titre
     if(empty($resultat))
     {
         http_response_code(204);
         $msg = array("erreur"=> "Aucun livre trouvé avec le titre '$title'");
         echo json_encode($msg);
     }
     else
     {
         // Étape 5 : Convertir les données en JSON
         $json = json_encode($resultat);
         // Afficher les données JSON
         echo $json;
     }
}

function emprunterLivre($idUser, $idLivre, $delais)
{
    // Étape 1 : Vérifier si le livre est disponible
    require_once("connexion.php");
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
        return "Le livre a été emprunté avec succès.";
    }
    else
    {
        // Renvoyer un message d'erreur indiquant que le livre n'est pas disponible
        return "Le livre n'est pas disponible pour l'emprunt.";
    }
}
?>