<?php
/**
 * Classe des requêtes PDO 
 *
 */
class PDORequests {

  protected $sql;

  const SINGLE_ROW = true;

  /**
   * Récupération d'une ou plusieurs ligne de la requête $sql
   * @param array   $params paramètres de la requête préparée
   * @param boolean $uneSeuleLigne true si une seule ligne à récupérer false sinon 
   * @return array
   */ 
  public function getRows($params = [], $singleRow = false) {
    $sPDO = PDOSingleton::getInstance();
    $oPDOStatement = $sPDO->prepare($this->sql);
    $paramsNames = array_keys($params);
    foreach ($paramsNames as $paramName) $oPDOStatement->bindParam(':'.$paramName, $params[$paramName]);
    $oPDOStatement->execute();
    $result = $singleRow ? $oPDOStatement->fetch(PDO::FETCH_ASSOC) : $oPDOStatement->fetchAll(PDO::FETCH_ASSOC);
    return $result;
  }

  /**
   * Requête $sql de Création Update ou Delete d'une ligne
   * @param array   $params paramètres de la requête préparée
   * @return boolean|string chaîne contenant lastInsertId s'il est > 0
   */ 
  public function CUDRow($params = []) {
    $sPDO = PDOSingleton::getInstance();
    $oPDOStatement = $sPDO->prepare($this->sql);
    foreach ($params as $paramName => $paramVal) $oPDOStatement->bindValue(':'.$paramName, $paramVal);
    $execute = $oPDOStatement->execute();
    if ($oPDOStatement->rowCount() <= 0 && !$execute) return false;
    if ($sPDO->lastInsertId() > 0)       return $sPDO->lastInsertId();
    return true;
  }
}