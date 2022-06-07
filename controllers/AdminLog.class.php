<?php
/**
 * Classe qui gère les actions associées à la page de connexion des utilisateurs à l'interface d'administration
 * 
 */
class AdminLog extends Admin {

    private $methods = [
    'display'           => 'displayLogInForm',
    'logIn'             => 'logIn',
    'logOut'            => 'logOut'
    ];

    /**
     * Gére l'appel de la fonction correspondante à la demande d'action reçue
     */ 
    public function manage($entity = "log") {
        $this->entity  = $entity;
        if(isset($_GET['action'])) $this->action  = $_GET['action'];
        else $this->action = 'display';
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
        if(isset($_SESSION['adminConnected'])) unset($_SESSION['adminConnected']);
        (new View)->generate("vAdminLogInForm",
            array(
              'title' => "Connexion interface administrateur",
            ),
            "admin-temp-min");
    }

    /**
     * Gère la connexion: vérifie les identifiants saisis dans la BD et crée une variable de session si la saisie est correcte
     */ 
    public function logIn() {
        $errorMsg = ""; 
        if (count($_POST) !== 0) {
            $adminConnected = self::$oSQLRequests->logIn($_POST);
            if ($adminConnected !== false && $adminConnected['Role_id'] === User::ROLE_ADMIN) {
                $oAdminConnected = new User($adminConnected);
                $_SESSION['adminConnected'] = $oAdminConnected;
                self::$oAdminConnected = $oAdminConnected;
                parent::manage();
                exit;
            } 
            else {
                $errorMsg = "Courriel ou mot de passe incorrect."; 
            }
        }
        (new View)->generate("vAdminLogInForm",
                            array(
                            'title'     => "Page de connexion",
                            'user'      => $_POST,
                            'errorMsg'  => $errorMsg
                            ),
                            "admin-temp-min");
    }

    /**
     * Gère la déconnexion 
     */ 
    public function logOut() {
        unset ($_SESSION['adminConnected']);
        parent::manage();
    }
}
?>