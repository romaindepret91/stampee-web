<?php
/**
 * Classe qui gère les actions associées à la déconnexion d'un membre
 * 
 */
class FrontendLogOut extends Frontend {

    private $methods = [
    'log'               => 'logOut'
    ];

    /**
     * Gére l'appel de la fonction correspondante à la demande d'action reçue
     */ 
    public function manage($entity = "logOut") {
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
     * Gére la déconnexion
     */ 
    public function logOut() {
        if(isset($_SESSION['memberConnected'])) {
            $oMemberConnected = $_SESSION['memberConnected'];
            $message = "Nous sommes tristes de vous voir partir $oMemberConnected->user_firstname $oMemberConnected->user_lastname.";
            unset($_SESSION['memberConnected']);
            (new View)->generate('vLogOutResult',
            array(
            'title'             => 'Déconnexion réussie!',
            'message'           => $message
            ),
            'frontend-temp');
            exit;
        }
        else (new View)->generate('vLogOutResult',
        array(
        'title'             => 'Vous êtes bien déconnecté..',
        ),
        'frontend-temp');
    }
}
?>