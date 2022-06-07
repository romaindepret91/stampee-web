<?php
/**
 * Classe de l'entité User
 *
 */
class User extends Entity {
  protected $user_id = 0;
  protected $user_lastname;
  protected $user_firstname;
  protected $user_email;
  protected $user_password;
  protected $role_id;

  const ROLE_ADMIN  = 1;
  const ROLE_MEMBER = 2;

  protected $errors = array();
  const ERR_EMAIL_USED = "Courriel déjà utilisé.";

  public function getUser_id()          { return $this->user_id; }
  public function getUser_lastname()    { return $this->user_lastname; }
  public function getUser_firstname()   { return $this->user_firstname; }
  public function getUser_email()       { return $this->user_email; }
  public function getUser_password()    { return $this->user_password; }
  public function getRole_id()          { return $this->role_id; }
  public function getErrors()           { return $this->errors; }
  
  /**
   * Mutateur de la propriété user_id 
   * @param int $user_id
   * @return $this
   */    
  public function setUser_id($user_id) {
    unset($this->errors['user_id']);
    $regExp = '/^\d+$/';
    if (!preg_match($regExp, $user_id)) {
      $this->errors['user_id'] = 'Numéro incorrect.';
    }
    $this->user_id = $user_id;
    return $this;
  }    

  /**
   * Mutateur de la propriété user_lastname 
   * @param string $user_lastname
   * @return $this
   */    
  public function setUser_lastname($user_lastname) {
    unset($this->errors['user_lastname']);
    $user_lastname = trim($user_lastname);
    $regExp = '/^[a-zÀ-ÖØ-öø-ÿ]{2,}( [a-zÀ-ÖØ-öø-ÿ]{2,})*$/i';
    if (!preg_match($regExp, $user_lastname)) {
      $this->errors['user_lastname'] = 'Au moins 2 caractères alphabétiques pour chaque mot.';
    }
    $this->user_lastname = $user_lastname;
    return $this;
  }

  /**
   * Mutateur de la propriété user_firstname 
   * @param string $user_firstname
   * @return $this
   */    
  public function setUser_firstname($user_firstname) {
    unset($this->errors['user_firstname']);
    $user_firstname = trim($user_firstname);
    $regExp = '/^[a-zÀ-ÖØ-öø-ÿ]{2,}( [a-zÀ-ÖØ-öø-ÿ]{2,})*$/i';
    if (!preg_match($regExp, $user_firstname)) {
      $this->errors['user_firstname'] = 'Au moins 2 caractères alphabétiques pour chaque mot.';
    }
    $this->user_firstname = $user_firstname;
    return $this;
  }

  /**
   * Mutateur de la propriété user_email
   * @param string $user_email
   * @return $this
   */    
  public function setUser_email($user_email) {
    unset($this->errors['user_email']);
    $user_email = trim(strtolower($user_email));
    if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
      $this->errors['user_email'] = 'Format incorrect.';
    }
    $this->user_email = $user_email;
    return $this;
  }

  /**
   * Mutateur de la propriété role_id
   * @param string $role_id
   * @return $this
   */    
  public function setRole_id($role_id) {
    unset($this->errors['user_role']);
    if (intval($role_id) !== self::ROLE_ADMIN &&
        intval($role_id) !== self::ROLE_MEMBER) {
      $this->errors['user_role'] = 'Profil incorrect.';
    }
    $this->role_id = $role_id;
    return $this;
  }

   /**
   * Mutateur de la propriété user_password 
   * @param int $user_password
   * @return $this
   */    
  public function setUser_password($user_password) {
    unset($this->errors['user_password']);
    $regExp = '/^[A-Za-z0-9]{12,}$/';
    if (!preg_match($regExp, $user_password)) {
      $this->errors['user_password'] = 'Mot de passe incorrect.';
    }
    $this->user_password = $user_password;
    return $this;
  }  
  
  /**
   * Mutateur de la propriété user_created_date 
   * @param string $user_created_date
   * @return $this
   */    
  public function setUser_created_date($user_created_date) {
    unset($this->errors['user_created_date']);
    $this->user_created_date = $user_created_date;
    return $this;
  }

  /**
   * Controler l'existence du courriel 
   */    
  public function checkEmail() {
    if (!isset($this->errors['user_email'])) {
      $result = (new SQLRequests)->checkEmail([ 'user_email'    => $this->user_email,
                                                'user_id'       => $this->user_id  ]);
      if ($result) $this->errors['user_email'] = self::ERR_EMAIL_USED;
    }
  }

  /**
   * Génération d'un mot de passe aléatoire
   * @return $password
   */    
  public function generatePassword() {
    $password = random_bytes(12);
    $password = bin2hex($password);
    $this->user_password = $password;
    return $password;
  }
}