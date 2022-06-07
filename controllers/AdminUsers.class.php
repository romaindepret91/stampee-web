<?php
/**
 * Classe qui gère les actions associées à la page Admin section Utilisateurs
 * 
 */
class AdminUsers extends Admin {

    private $methods = [
    'display'           => 'displayUsers',
    'addUserForm'       => 'displayAddUserForm',
    'add'               => 'addUser',
    'delete'            => 'deleteUser',
    'update'            => 'updateUser',
    'password'          => 'generatePassword'
    ];

    /**
     * Gére l'appel de la fonction correspondante à la demande d'action reçue
     */  
    public function manage($entity = "users") {
        $this->entity  = $entity;
        $this->action  = $_GET['action'] ?? 'display';
        if (isset($this->methods[$this->action])) {
            $method = $this->methods[$this->action];
            $this->$method();
        } 
        else throw new Exception("L'action $this->action de l'entité $this->entity n'existe pas.");
    }

    /**
     * Affiche la page de users
     */  
    public function displayUsers() {
        $oAdminConnected = null;
        if(isset($_SESSION['adminConnected'])) $oAdminConnected = $_SESSION['adminConnected'];
        $users = self::$oSQLRequests->getAllUsers();
        (new View)->generate("vAdminUsers",
                array(
                    'title'             => "Gestion des utilisateurs",
                    'users'             => $users,
                    'message'           => $this->resultActionMsg,
                    'classResult'       => $this->resultClass,
                    'oAdminConnected'   => $oAdminConnected
                ),
                "admin-temp");
    }

     /**
     * Affiche le formulaire d'ajout d'utilisateur
     */ 
    public function displayAddUserForm() {
        $oAdminConnected = null;
        if(isset($_SESSION['adminConnected'])) $oAdminConnected = $_SESSION['adminConnected'];
        (new View)->generate("vAdminAddUserForm",
                array(
                    'title'             => "Ajout d'un utilisateur",
                    'oAdminConnected'   => $oAdminConnected
                ),
                "admin-temp");
    }

     /**
     * Ajoute un utilisateur
     */ 
    public function addUser() {
        $oAdminConnected = null;
        if(isset($_SESSION['adminConnected'])) $oAdminConnected = $_SESSION['adminConnected'];
        if (self::$oAdminConnected->role_id !== User::ROLE_ADMIN) throw new Exception(self::ERROR_FORBIDDEN);
        if (count($_POST) !== 0) {
            $user = $_POST;
            $oUser = new User($user);
            $oUser->checkEmail();
            $errors = $oUser->getErrors();
            if (count($errors) === 0) {
                $oUser->generatePassword();
                $currentDateTime = date('Y-m-d H:i:s');
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
                        $this->resultActionMsg = "La création du compte a bien été effectuée.";
                        $oSendPassword = new SendPassword($oUser);
                        $this->resultActionMsg .= $oSendPassword->sendPassword($oUser) ? "Les identifiants ont bien été envoyés à votre adresse: $oUser->user_email" : "L'envoi des identifiants a échoué.";
                        unset($_GET['action']);
                    }
                    else {
                        $this->resultActionMsg = 'Le processus d\'inscription a échoué.';
                        $this->resultClass = 'failed';
                    }
                }
                else $errors['user_email'] = $result;
            }
            else {
                (new View)->generate("vAdminAddUserForm",
                    array(
                        'title'     => "Ajout d'un utilisateur",
                        'user'      => $user,
                        'errors'    => $errors
                    ),
                    "admin-temp");
                    exit;
                };
            $users = self::$oSQLRequests->getAllUsers();
            (new View)->generate("vAdminUsers",
                                array(
                                    'title'             => "Gestion des utilisateurs",
                                    'users'             => $users,
                                    'oAdminConnected'   => $oAdminConnected,
                                    'message'           => $this->resultActionMsg,
                                    'resultClass'       => $this->resultClass
                                ),
                                "admin-temp");
        }
    }

    /**
   * Modifier un utilisateur
   */
    public function updateUser() {
        if(self::$oAdminConnected->role_id !== User::ROLE_ADMIN) throw new Exception(self::ERROR_FORBIDDEN);
        $userId = $_GET['userId'];
        if(!preg_match('/^\d+$/', $userId)) throw new Exception("Numéro d'utilisateur incorrect pour une modification");
        if(count($_POST) !== 0) {
            $user = $_POST;
            $user['User_id'] = $userId;
            $oUser = new User($user);
            $oUser->checkEmail();
            $errors = $oUser->getErrors();
            if(count($errors) === 0) {
                $result = self::$oSQLRequests->updateUser([
                    'user_id'           => $oUser->user_id, 
                    'user_lastname'     => $oUser->user_lastname,
                    'user_firstname'    => $oUser->user_firstname,
                    'user_email'        => $oUser->user_email,
                    'role_id'           => $oUser->role_id
                ]);
                if($result !== User::ERR_EMAIL_USED) {
                    if($result === true)  {
                        $this->resultActionMsg = "Modification de l'utilisateur numéro $userId effectuée.";    
                    } else {  
                        $this->resultClass = "failed";
                        $this->resultActionMsg = "Modification de l'utilisateur numéro $userId non effectuée.";
                    }
                    $this->displayUsers();
                    exit;
                } 
                else $errors['user_email'] = $result;
            }
        }
        else {
        $user = self::$oSQLRequests->getUser($userId);
        $errors = [];
        }
        (new View)->generate("vAdminUpdateUserForm",
                            array(
                                'title'     => "Modification d'un utilisateur",
                                'user'      => $user,
                                'errors'    => $errors
                            ),
                            "admin-temp");
    }

    /**
     * Supprime un utilisateur
     */ 
    public function deleteUser() {
        if (self::$oAdminConnected->role_id !== User::ROLE_ADMIN) throw new Exception(self::ERROR_FORBIDDEN);
        $userId = $_GET['userId'];
        if (!preg_match('/^\d+$/', $userId))
          throw new Exception("Numéro d'utilisateur incorrect pour une suppression.");
        $result = self::$oSQLRequests->deleteUser($userId);
        if ($result === false) $this->classResult = "failed";
        $this->resultActionMsg = "Suppression de l'utilisateur numéro $userId ".($result ? "" : "non ")."effectuée.";
        $this->displayUsers();
    }

    /**
     * Génére un nouveau mot de passe
     */
    public function generatePassword() {
        if (self::$oAdminConnected->role_id !== User::ROLE_ADMIN) throw new Exception(self::ERROR_FORBIDDEN);
        $userId = $_GET['userId'];
        if (!preg_match('/^\d+$/', $userId)) throw new Exception("Numéro d'utilisateur incorrect pour une modification du mot de passe.");
        $user = self::$oSQLRequests->getUser($userId);
        $oUser = new User($user);
        $password = $oUser->generatePassword();
        $result = self::$oSQLRequests->changeUserPassword([
            'user_id'       => $userId, 
            'user_password' => $password
        ]);
        if ($result === true)  {
        $this->resultActionMsg = "Modification du mot de passe de l'utilisateur numéro $userId effectuée.";
        $oSendPassword = new SendPassword($oUser);
        $this->resultActionMsg .= $oSendPassword->sendPassword($oUser) ?  " Courriel envoyé à l'utilisateur." : " Erreur d'envoi d'un courriel à l'utilisateur.";
        } else {  
        $this->resultClass = "failed";
        $this->resultActionMsg = "Modification du mot de passe de l'utilisateur numéro $userId  non effectuée.";
        }
        $this->displayUsers();
    }
}
?>