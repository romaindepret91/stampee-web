<?php
/**
 * Classe qui gère les actions associées à la souscription d'un utilisateur
 * 
 */
class FrontendSignUp extends Frontend {

    private $methods = [
    'display'           => 'displaySignUpForm',
    'add'               => 'addMember',
    'result'            => 'displayResult'
    ];

    /**
     * Gére l'appel de la fonction correspondante à la demande d'action reçue
     */ 
    public function manage($entity = "signUp") {
        $this->entity  = $entity;
        if(isset($_GET['action'])) $this->action  = $_GET['action'];
        else $this->action = 'result';
        if (isset($this->methods[$this->action])) {
            $method = $this->methods[$this->action];
            $this->$method();
        } else throw new Exception("L'action $this->action de l'entité $this->entity n'existe pas.");
    }

    /**
     * Affiche la page de souscription
     */ 
    public function displaySignUpForm() {
        (new View)->generate("vSignUpForm",
            array(
              'title' => "Formulaire d'inscription",
            ),
            "frontend-temp");
    }

    /**
     * Ajoute un membre
     */ 
    public function addMember() {
        $title = "";
        $message = "";
        if (count($_POST) !== 0) {
            $user = $_POST;
            $oUser = new User($user);
            $oUser->checkEmail();
            $errors = $oUser->getErrors();
            if (count($errors) === 0) {
                $oUser->generatePassword();
                $currentDateTime = date('Y-m-d H:i:s');
                $oUser->role_id = $oUser::ROLE_MEMBER;
                $result = self::$oSQLRequests->addUser([
                    'user_lastname'     => $oUser->user_lastname,
                    'user_firstname'    => $oUser->user_firstname,
                    'user_email'        => $oUser->user_email,
                    'user_password'     => $oUser->user_password,
                    'user_date_created' => $currentDateTime,
                    'role_id'           => $oUser->role_id
                  ]);
                if($result !== User::ERR_EMAIL_USED) {
                    if (preg_match('/^[1-9]\d*$/', $result)) {
                        $title = "Félicitations, votre compte a été créé avec succès!";
                        $oSendPassword = new SendPassword($oUser);
                        $message .= $oSendPassword->sendPassword($oUser) ? "Les identifiants ont bien été envoyés à votre adresse: $oUser->user_email" : "L'envoi des identifiants a échoué.";
                    }
                    else $title = 'Le processus d\'inscription a échoué.';
                }
                else $errors['user_email'] = $result;
            }
            else {
                (new View)->generate("vSignUpForm",
                    array(
                        'title'     => "Formulaire d'inscription",
                        'user'      => $user,
                        'errors'    => $errors
                    ),
                    "frontend-temp");
                    exit;
                };
            (new View)->generate('vSignUpResult',
                array(
                    'title'         => $title,
                    'message'       => $message,
                ),
                'frontend-temp');
        }
    }

    /**
     * Affiche la page du résultat de la souscription
     */ 
    public function displayResult() {
        $title = $_GET['title'];
        $message= $_GET['message'];
        (new View)->generate('vSignUpResult',
        array(
            'title'         => $title,
            'message'       => $message,
        ),
        'frontend-temp');
    }
}
?>