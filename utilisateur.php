<?php
require_once("connexion.php");
header('Content-type:application/json');

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        if(isset($data['action'])&& $data['action'] === 'signup'){
            signUp($data);
        }else{
            logIn();

        }
        
        break;
    case 'GET':
        getUtilisateursAvecDelaiDepasse();
        break;    
}

function logIn()
{
    global $connexion;
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Vérifier si les données de connexion ont été soumises
    if(isset($data['Login']) && isset($data['Pass'])) {
        // Récupérer les données soumises
        $login = $data['Login'];
        $pass = $data['Pass'];
        
        // Requête SQL pour vérifier les informations d'identification dans la base de données
        $requete = "SELECT * FROM utilisateur WHERE Login = :login AND Pass = :pass";
        
        // Préparer la requête
        $statement = $connexion->prepare($requete);
        
        // Exécuter la requête en remplaçant les paramètres avec les valeurs soumises
        $statement->execute(array(':login' => $login, ':pass' => $pass));
        
        // Récupérer le résultat sous forme de tableau associatif
        $utilisateur = $statement->fetch(PDO::FETCH_ASSOC);
        
        // Vérifier si un utilisateur correspondant a été trouvé
        if($utilisateur) {
            // Utilisateur trouvé, retourner les données de l'utilisateur
            http_response_code(200);
            //echo json_encode(["role" => $utilisateur['Role']]);
            echo json_encode($utilisateur);
        } else {
            // Aucun utilisateur correspondant, renvoyer une réponse avec un code de statut 401 (Unauthorized)
            http_response_code(401);
            echo json_encode(["erreur" => "Login ou mot de passe incorrect"]);
        }
    } else {
        // Les données de connexion ne sont pas complètes, renvoyer une réponse avec un code de statut 400 (Bad Request)
        http_response_code(400);
        echo json_encode(["erreur" => "Les champs de connexion sont requis"]);
    }
}

function checkUserExists($login)
{
    global $connexion;
    
    // Requête SQL pour vérifier si l'utilisateur existe déjà dans la base de données
    $checkQuery = "SELECT * FROM utilisateur WHERE Login = :login";
    $checkStatement = $connexion->prepare($checkQuery);
    $checkStatement->execute(array(':login' => $login));
    $existingUser = $checkStatement->fetch(PDO::FETCH_ASSOC);
    
    return $existingUser;
}

function signUp($data)
{
    global $connexion;
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Vérifier si toutes les données nécessaires sont soumises
    if(isset($data['Nom']) && isset($data['Prenom']) && isset($data['Mail']) && isset($data['Login']) && isset($data['Pass']) && isset($data['Role'])) {
        
        // Récupérer les données soumises
        $nom = $data['Nom'];
        $prenom = $data['Prenom'];
        $mail = $data['Mail'];
        $login = $data['Login'];
        $pass = $data['Pass'];
        $role = $data['Role']; // Ajout du champ "role"
        
        // Vérifier si l'utilisateur existe déjà dans la base de données
        $existingUser = checkUserExists($login);
        
        if($existingUser) {
            // L'utilisateur existe déjà, renvoyer une réponse avec un code de statut 409 (Conflict)
            http_response_code(409);
            echo json_encode(["erreur" => "L'utilisateur existe déjà"]);
        } else {
            // Insérer le nouvel utilisateur dans la base de données
            $insertQuery = "INSERT INTO utilisateur (Nom, Prenom, Mail, Login, Pass, Role) VALUES (:nom, :prenom, :mail, :login, :pass, :role)";
            $insertStatement = $connexion->prepare($insertQuery);
            
            if($insertStatement->execute(array(':nom' => $nom, ':prenom' => $prenom, ':mail' => $mail, ':login' => $login, ':pass' => $pass, ':role' => $role))) {
                // Utilisateur inséré avec succès, renvoyer une réponse avec un code de statut 201 (Created)
                http_response_code(201);
                echo json_encode(["message" => "Utilisateur inséré avec succès"]);
            } else {
                // Une erreur s'est produite lors de l'insertion de l'utilisateur, renvoyer une réponse avec un code de statut 500 (Internal Server Error)
                http_response_code(500);
                echo json_encode(["erreur" => "Erreur lors de l'insertion de l'utilisateur"]);
            }
        }
    } else {
        // Les données nécessaires pour l'inscription ne sont pas complètes, renvoyer une réponse avec un code de statut 400 (Bad Request)
        http_response_code(400);
        echo json_encode(["erreur" => "Toutes les données nécessaires pour l'inscription sont requises"]);
    }
}


function getUtilisateursAvecDelaiDepasse()
{
    global $connexion;
    
    // Requête SQL pour sélectionner les utilisateurs dont le délai d'emprunt a expiré
    $requete = "SELECT u.idUser, u.Nom, u.Prenom, u.Mail, u.Login, u.Pass
                FROM utilisateur u
                INNER JOIN emprunter e ON u.idUser = e.idUser
                WHERE e.delais < NOW()"; // Assurez-vous que la colonne 'delais' est un datetime ou un timestamp
    
    // Exécuter la requête
    $statement = $connexion->query($requete);
    
    // Récupérer les résultats sous forme associative
    $resultat = $statement->fetchAll(PDO::FETCH_ASSOC);
    
    // Si aucun résultat n'est retourné, renvoyer une réponse avec un code de statut 204 (No Content)
    if (!$resultat) {
        http_response_code(204);
        echo json_encode(["erreur" => "Aucun utilisateur avec délai dépassé"]);
    } else {
        // Convertir les résultats en format JSON et les renvoyer
        echo json_encode($resultat);
    }
}
?>
