<?php
/**
 * Classe contrôleur des requêtes de l'interface admin
 * 
 */
class Admin extends Routeur {

  protected $entity;
  protected $action;
  protected $resultClass = "done";
  protected $resultActionMsg = "";

  protected static $oAdminConnected;

  protected static $oSQLRequests;

  /**
   * Gére l'interface admin. Envoie vers la classe frontend appropriée en fonction de la valeur de l'entité saisie.
   */  
  public function manage() {
    if(isset($_SESSION['memberConnected'])) unset($_SESSION['memberConnected']);
    self::$oSQLRequests = new SQLRequests;
    if (isset($_SESSION['adminConnected'])) {
      self::$oAdminConnected = $_SESSION['adminConnected'];
      $entity = $_GET['entity']  ?? 'users';
      $entity = ucwords($entity);
      $class = "Admin$entity";
      if (class_exists($class)) (new $class())->manage();
      else throw new Exception("L'entité $entity n'existe pas.");
    }
    else {
      (new AdminLog)->logIn();
    }
  }
}
