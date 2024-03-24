<?php
require_once("connexion.php");
header('Content-type:application/json');

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        logIn();
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
            echo json_encode(["role" => $utilisateur['Role']]);
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
?>
