<?php
/**
 * Classe qui gère les actions associées à la page de connexion des membres
 * 
 */
class FrontendLogIn extends Frontend {

    private $methods = [
    'display'           => 'displayLogInForm',
    'log'               => 'logIn'
    ];

    /**
     * Gére l'appel de la fonction correspondante à la demande d'action reçue
     */ 
    public function manage($entity = "logIn") {
        $this->entity  = $entity;
        if(isset($_GET['action'])) $this->action  = $_GET['action'];
        else $this->action = 'result';
        if (isset($this->methods[$this->action])) {
            $method = $this->methods[$this->action];
            $this->$method();
        } 
        else throw new Exception("L'action $this->action de l'entité $this->entity n'existe pas.");
    }

    /**
     * Affiche la page de connexion
     */ 
    public function displayLogInForm() {
        if(isset($_SESSION['memberConnected'])) unset($_SESSION['memberConnected']);
        (new View)->generate("vLogInForm",
            array(
              'title' => "Page de connexion",
            ),
            "frontend-temp");
    }

    /**
     * Gère la connexion: vérifie les identifiants saisis dans la BD et crée une variable de session si la saisie est correcte
     */ 
    public function logIn() {
        $errorMsg = ""; 
        $message = "";
        if (count($_POST) !== 0) {
            $memberConnected = self::$oSQLRequests->logIn($_POST);
            if ($memberConnected !== false && $memberConnected['Role_id'] === User::ROLE_MEMBER) {
                $oMemberConnected = new User($memberConnected);
                $_SESSION['memberConnected'] = $oMemberConnected;
                self::$oMemberConnected = $oMemberConnected;
                $message = "Bienvenue dans votre espace membre $oMemberConnected->user_firstname $oMemberConnected->user_lastname.";
            } 
            else {
                $errorMsg = "Courriel ou mot de passe incorrect."; 
                (new View)->generate("vLogInForm",
                    array(
                    'title'     => "Page de connexion",
                    'user'      => $_POST,
                    'errorMsg'  => $errorMsg
                    ),
                    "frontend-temp");
                    exit;
            }
        }
        (new View)->generate('vLogInResult',
                array(
                'title'             => 'Connexion réussie!',
                'oMemberConnected'  => $oMemberConnected,
                'message'           => $message
                ),
                'frontend-temp');
    }
}
?>