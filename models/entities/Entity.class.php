<?php

/**
 * Classe Entity dont hérite toutes les classes des entités de la base de données
 *
 */
abstract class Entity
{
  /**
   * Constructeur de la classe
   * @param array $properties, tableau associatif des propriétés 
   *
   */ 
  public function __construct($properties = []) {
    $keyNames = array_keys($properties);
    foreach ($keyNames as $propertyName) {
      $this->__set($propertyName, $properties[$propertyName]);
    } 
  }

  /**
   * hydratation des propriétés de la classe sans passer par les setters ()
   * quand les données sont sûres car elles proviennent de la base de données 
   * @param array $properties, tableau associatif des propriétés 
   */ 
  public function hydrate($properties = []) {
    foreach ($properties as $propertyName => $propertyValue) {
      $this->$propertyName = $propertyValue;
    }
    return $this;
  }
  
  /**
   * Accesseur magique d'une propriété de l'objet
   * @param string $prop, nom de la propriété
   * @return property value
   */     
  public function __get($property) {
    return $this->$property;
  }
  
  /**
   * Mutateur magique qui exécute le mutateur de la propriété en paramètre 
   * @param string $prop, nom de la propriété
   * @param $val, contenu de la propriété à mettre à jour    
   */   
  public function __set($property, $value) {
    $setProperty = 'set'.ucfirst($property);
    $this->$setProperty($value);
  }
}