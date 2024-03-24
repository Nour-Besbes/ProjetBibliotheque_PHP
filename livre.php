<?php
require_once("connexion.php");
header('Content-type:application/json') ;

switch($_SERVER['REQUEST_METHOD'])
{
    case 'GET':
    if(isset($_GET['idLivre'])){
        $id=$_GET['idLivre'];
        getLivre($id);
    }else{
        getAllLivre();
    }
    break;
    case 'DELETE':
        if(isset($_GET['idLivre']) && $_GET['idLivre']!=null){
            $id=$_GET['idLivre'];
            deleteLivre($id);
        }
        break;
    case 'POST':
            // Récupérer les données envoyées par le client
        $donnees = json_decode(file_get_contents('php://input'), true);
            
            // Vérifier si les données nécessaires sont présentes
        if(isset($donnees['Titre']) && isset($donnees['Auteur']) && isset($donnees['Categorie'])  && isset($donnees['Quantite'])  && isset($donnees['Image']) && isset($donnees['Status']) ) {
            $titre = $donnees['Titre'];
            $auteur = $donnees['Auteur'];
            $categorie = $donnees['Categorie'];
            $quantite=$donnees['Quantite'];
            $image=$donnees['Image'];
            $status=$donnees['Status'];
                
                // Appeler la fonction pour ajouter un livre
            ajouterLivre($titre, $auteur, $categorie,$quantite,$image,$status);
        } else {
                // Si des données sont manquantes, renvoyer un message d'erreur
            echo json_encode(["erreur" => "Données manquantes"]);
        }
        break;
    case 'PUT':
            // Récupérer les données envoyées par le client
        $donnees = json_decode(file_get_contents('php://input'), true);
        
            // Vérifier si les données nécessaires sont présentes
        if(isset($_GET['idLivre']) && isset($donnees['Titre']) && isset($donnees['Auteur']) && isset($donnees['Categorie']) && isset($donnees['Quantite']) && isset($donnees['Image']) && isset($donnees['Status'])) {
            $id = $_GET['idLivre'];
            $titre = $donnees['Titre'];
            $auteur = $donnees['Auteur'];
            $categorie = $donnees['Categorie'];
            $quantite = $donnees['Quantite'];
            $image = $donnees['Image'];
            $status = $donnees['Status'];
                        
                // Appeler la fonction pour mettre à jour un livre
            updateLivre($id, $titre, $auteur, $categorie, $quantite, $image,$status);
        } else {
            echo json_encode(["erreur" => "Données manquantes"]);
            }
        break;    
            
}

function deleteLivre($id)
{
    global $connexion;
    //preparer la requete 
    $statement=$connexion->prepare("Delete from LIVRE where idLivre=:x");
    $statement->bindParam(":x",$id);
    $result=$statement->execute();
    $msg=["resultat"=>$result];
    echo json_encode($msg);
}

function getAllLivre()
{
    //Etape 1 : préparation de la requête
    $requete ="SELECT * from Livre ";
    //Etape 2 : connexion avec la base de données, création de l'objet connexion
    global $connexion;
    //Etape 3: exécuter la requête
    $statement = $connexion->query($requete);
    //Etape 4 : récupérer le résultat sous forme associative
    $resultat = $statement->fetchAll(PDO::FETCH_ASSOC);
    //ajouter l'entête pour spécifier le format retourné par le fichier
    
    //si pas de produits
    if($resultat==null)
    {
    http_response_code(204);
    $msg = array("erreur"=> "pas de livre");
    json_encode($msg);
    }
    else
    { //Etape 5 : convertir les données en json
    $json = json_encode($resultat);
    //afficher les données json
    echo $json;
    }
}


function getLivre($id)
{
    //Etape 1 : préparation de la requête
    $requete ="SELECT * from Livre where idLivre = $id ";
    //Etape 2 : connexion avec la base de données, création de l'objet connexion
    global $connexion;
    //Etape 3: exécuter la requête
    $statement = $connexion->query($requete);
    //Etape 4 : récupérer le résultat sous forme associative
    $resultat = $statement->fetch(PDO::FETCH_ASSOC);
    //ajouter l'entête pour spécifier le format retourné par le fichier
    header('Content-type:application/json') ;
    //si pas de produits
    if($resultat==null)
    {
    http_response_code(204);
    $msg = array("erreur"=> "Livre inexistant");
    json_encode($msg);
    }
    else
    { //Etape 5 : convertir les données en json
    $json = json_encode($resultat);
    //afficher les données json
    echo $json;
    }

    
}

function ajouterLivre($titre, $auteur, $categorie,$quantite,$image,$status)
{
    global $connexion;
    
    // Préparer la requête d'insertion
    $requete = "INSERT INTO Livre (Titre, Auteur, Categorie,Quantite,Image,Status) VALUES (:titre, :auteur, :categorie, :quantite, :image,:status)";
    $statement = $connexion->prepare($requete);
    
    // Liaison des paramètres
    $statement->bindParam(":titre", $titre);
    $statement->bindParam(":auteur", $auteur);
    $statement->bindParam(":categorie", $categorie);
    $statement->bindParam(":quantite", $quantite);
    $statement->bindParam(":image", $image);
    $statement->bindParam(":status", $status);
    
    // Exécuter la requête
    $resultat = $statement->execute();
    
    // Créer un message JSON pour renvoyer le résultat
    $msg = ["resultat" => $resultat];
    
    // Retourner le résultat sous forme JSON
    echo json_encode($msg);
}

function updateLivre($id, $titre, $auteur, $categorie, $quantite, $image,$status)
{
    global $connexion;
    
    // Préparer la requête de mise à jour
    $requete = "UPDATE Livre SET Titre = :titre, Auteur = :auteur, Categorie = :categorie, Quantite = :quantite, Image = :image,Status=:status WHERE idLivre = :id";
    $statement = $connexion->prepare($requete);
    
    // Liaison des paramètres
    $statement->bindParam(":id", $id);
    $statement->bindParam(":titre", $titre);
    $statement->bindParam(":auteur", $auteur);
    $statement->bindParam(":categorie", $categorie);
    $statement->bindParam(":quantite", $quantite);
    $statement->bindParam(":image", $image);
    $statement->bindParam(":status", $status);
    
    // Exécuter la requête
    $resultat = $statement->execute();
    
    // Créer un message JSON pour renvoyer le résultat
    $msg = ["resultat" => $resultat];
    
    // Retourner le résultat sous forme JSON
    echo json_encode($msg);
}

?>