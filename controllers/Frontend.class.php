<?php
/**
 * Classe contrôleur des requêtes de l'interface frontend
 * 
 */
class Frontend extends Routeur {

  protected $entity;
  protected $action;
  protected $resultClass = "done";
  protected $resultActionMsg = "";

  protected static $oMemberConnected;

  protected static $oSQLRequests;

  /**
   * Gére l'interface frontend. Envoie vers la classe frontend appropriée en fonction de la valeur de l'entité saisie.
   */  
  public function manage() {
    if(isset($_SESSION['adminConnected'])) unset($_SESSION['adminConnected']);
    self::$oSQLRequests = new SQLRequests;
    $entity = $_GET['entity']  ?? 'homepage';
    $entity = ucwords($entity);
    $class = "Frontend$entity";
    if (class_exists($class)) (new $class())->manage();
    else throw new Exception("L'entité $entity n'existe pas.");
  }
}
