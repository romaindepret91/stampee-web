<?php

/**
 * Classe de l'entité Stamp
 *
 */
class Stamp extends Entity {
  protected $stamp_id;
  protected $stamp_name;
  protected $stamp_year;
  protected $stamp_region_origin;
  protected $stamp_country_origin;
  protected $stamp_main_image;
  protected $stamp_condition;
  protected $stamp_color;
  protected $stamp_creator;
  protected $stamp_number;
  protected $stamp_prints_number;
  protected $stamp_dimensions;
  protected $stamp_certified;
  protected $stamp_auction_id;

  protected $errors = array();

  const YEAR_FIRST_STAMP    = 1840;
  const STAMP_CERTIFIED_YES = 1;
  const STAMP_CERTIFIED_NO  = 0;

  public function getStamp_id()             { return $this->stamp_id; }
  public function getStamp_name()           { return $this->stamp_name; }
  public function getStamp_year()           { return $this->stamp_year; }
  public function getStamp_region_origin()  { return $this->stamp_region_origin; }
  public function getStamp_country_origin() { return $this->stamp_country_origin; }
  public function getStamp_main_image()     { return $this->stamp_main_image; }
  public function getStamp_condition()      { return $this->stamp_condition; }
  public function getStamp_color()          { return $this->stamp_color; }
  public function getStamp_creator()        { return $this->stamp_creator; }
  public function getStamp_number()         { return $this->stamp_number; }
  public function getStamp_prints_number()  { return $this->stamp_prints_number; }
  public function getStamp_dimensions()     { return $this->stamp_dimensions; }
  public function getStamp_certified()      { return $this->stamp_certified; }
  public function getStamp_auction_id()      { return $this->stamp_auction_id; }
  public function getErrors()               { return $this->errors; }
  
  /**
   * Mutateur de la propriété stamp_id 
   * @param int $stamp_id
   * @return $this
   */    
  public function setStamp_id($stamp_id) {
    unset($this->errors['stamp_id']);
    $regExp = '/^\d+$/';
    if (!preg_match($regExp, $stamp_id)) {
      $this->errors['stamp_id'] = 'Numéro d\'identifiant incorrect.';
    }
    $this->stamp_id = $stamp_id;
    return $this;
  }    

  /**
   * Mutateur de la propriété stamp_name 
   * @param string $stamp_name
   * @return $this
   */    
  public function setStamp_name($stamp_name) {
    unset($this->errors['stamp_name']);
    $stamp_name = trim($stamp_name);
    $regExp = '/^[a-zÀ-ÖØ-öø-ÿ]{2,}( [a-zÀ-ÖØ-öø-ÿ]{2,})*$/i';
    if (!preg_match($regExp, $stamp_name)) {
      $this->errors['stamp_name'] = 'Au moins 2 caractères alphabétiques pour chaque mot.';
    }
    $this->stamp_name = $stamp_name;
    return $this;
  }

  /**
   * Mutateur de la propriété stamp_year 
   * @param string $stamp_year
   * @return $this
   */    
  public function setStamp_year($stamp_year) {
    unset($this->errors['stamp_year']);
    $stamp_year = trim($stamp_year);
    if (!preg_match('/^\d{4}$/', $stamp_year) ||
        $stamp_year < self::YEAR_FIRST_STAMP  || 
        $stamp_year > date("Y")) {
      $this->errors['stamp_year'] = "Entre ".self::YEAR_FIRST_STAMP." et l'année en cours.";
    }
    $this->stamp_year = $stamp_year;
    return $this;
  }

  /**
   * Mutateur de la propriété stamp_year_end 
   * @param string $stamp_year_end
   * @return $this
   */    
  public function setStamp_year_end($stamp_year_end) {
    unset($this->errors['stamp_year_end']);
    $stamp_year_end = trim($stamp_year_end);
    if (preg_match('/^\d{4}$/', $stamp_year_end)) {
      if($stamp_year_end < self::YEAR_FIRST_STAMP  || 
        $stamp_year_end > date("Y") || $stamp_year_end < $this->stamp_year) {
        $this->errors['stamp_year_end'] = "Entre ".$this->stamp_year." et l'année en cours.";
      }
    }
    $this->stamp_year_end = $stamp_year_end;
    return $this;
  }

  /**
   * Mutateur de la propriété stamp_region_origin 
   * @param string $stamp_region_origin
   * @return $this
   */    
  public function setStamp_region_origin($stamp_region_origin) {
    unset($this->errors['stamp_region_origin']);
    $stamp_region_origin = trim($stamp_region_origin);
    $regions = ['Amérique du Nord', 'Europe', 'Asie', 'Amérique du Sud', 'Afrique', 'Reste du monde'];
    $regionfound = false;
    foreach($regions as $region) {
        if($stamp_region_origin === $region) {
            $regionfound = true;
            break;
        }
    }
    if(!$regionfound) $this->errors['stamp_region_origin'] = 'Région sélectionnée invalide.';
    $this->stamp_region_origin = $stamp_region_origin;
    return $this;
  }

  /**
   * Mutateur de la propriété stamp_country_origin 
   * @param string $stamp_country_origin
   * @return $this
   */    
  public function setStamp_country_origin($stamp_country_origin) {
    unset($this->errors['stamp_country_origin']);
    $stamp_country_origin = trim($stamp_country_origin);
    $regExp = '/^[a-zÀ-ÖØ-öø-ÿ]{2,}( [a-zÀ-ÖØ-öø-ÿ]{2,})*$/i';
    if (!preg_match($regExp, $stamp_country_origin)) {
      $this->errors['stamp_country_origin'] = 'Au moins 2 caractères alphabétiques pour chaque mot.';
    }
    $this->stamp_country_origin = $stamp_country_origin;
    return $this;
  }

  /**
   * Mutateur de la propriété stamp_main_image 
   * @param string $stamp_main_image
   * @return $this
   */    
  public function setStamp_main_image($stamp_main_image) {
    unset($this->errors['stamp_main_image']);
    $stamp_main_image = trim($stamp_main_image);
    $regExp = '/^([\w\W-]+\/)*[\w\W-]+((.jpg)|(.png)|(.webp)|(.jpeg))$/i';
    if (!preg_match($regExp, $stamp_main_image)) {
      $this->errors['stamp_main_image'] = 'Chemin vers l\'image non valide.';
    }
    $this->stamp_main_image = $stamp_main_image;
    return $this;
  }

  /**
   * Mutateur de la propriété stamp_condition 
   * @param string $stamp_condition
   * @return $this
   */    
  public function setStamp_condition($stamp_condition) {
    unset($this->errors['stamp_condition']);
    $stamp_condition = trim($stamp_condition);
    $conditions = ['Parfaite', 'Excellente', 'Bonne', 'Moyenne', 'Endommagée'];
    $conditionfound = false;
    foreach($conditions as $condition) {
        if($stamp_condition === $condition) {
            $conditionfound = true;
            break;
        }
    }
    if(!$conditionfound) $this->errors['stamp_condition'] = 'Condition sélectionnée invalide.';
    $this->stamp_condition = $stamp_condition;
    return $this;
  }

  /**
   * Mutateur de la propriété stamp_color 
   * @param string $stamp_color
   * @return $this
   */    
  public function setStamp_color($stamp_color) {
    unset($this->errors['stamp_color']);
    $stamp_color = trim($stamp_color);
    $regExp = '/^[a-zÀ-ÖØ-öø-ÿ]{2,}( [a-zÀ-ÖØ-öø-ÿ]{2,})*$/i';
    if (!preg_match($regExp, $stamp_color)) {
      $this->errors['stamp_color'] = 'Au moins 2 caractères alphabétiques pour chaque mot.';
    }
    $this->stamp_color = $stamp_color;
    return $this;
  }

  /**
   * Mutateur de la propriété stamp_prints_number 
   * @param string $stamp_prints_number
   * @return $this
   */    
  public function setStamp_prints_number($stamp_prints_number) {
    unset($this->errors['stamp_prints_number']);
    $stamp_prints_number = trim($stamp_prints_number);
    $regExp = '/^\d+$/';
    if (!preg_match($regExp, $stamp_prints_number)) {
      $this->errors['stamp_prints_number'] = 'Entrez un nombre positif';
    }
    $this->stamp_prints_number = $stamp_prints_number;
    return $this;
  }

  /**
   * Mutateur de la propriété stamp_dimensions 
   * @param string $stamp_dimensions
   * @return $this
   */    
  public function setStamp_dimensions($stamp_dimensions) {
    unset($this->errors['stamp_dimensions']);
    $stamp_dimensions = trim($stamp_dimensions);
    $regExp = '/^[a-zÀ-ÖØ-öø-ÿ]{2,}( [a-zÀ-ÖØ-öø-ÿ]{2,})*$/i';
    if (!preg_match($regExp, $stamp_dimensions)) {
      $this->errors['stamp_dimensions'] = 'Au moins 2 caractères alphabétiques pour chaque mot.';
    }
    $this->stamp_dimensions = $stamp_dimensions;
    return $this;
  }

    /**
   * Mutateur de la propriété stamp_number 
   * @param int $stamp_number
   * @return $this
   */    
  public function setStamp_number($stamp_number) {
    unset($this->errors['stamp_number']);
    $regExp = '/^\d*$/';
    if (!preg_match($regExp, $stamp_number)) {
      $this->errors['stamp_number'] = 'Numéro de série incorrect.';
    }
    $this->stamp_number = $stamp_number;
    return $this;
  }    

  /**
   * Mutateur de la propriété stamp_creator 
   * @param string $stamp_creator
   * @return $this
   */    
  public function setStamp_creator($stamp_creator) {
    unset($this->errors['stamp_creator']);
    $stamp_creator = trim($stamp_creator);
    $regExp = '/^([a-zÀ-ÖØ-öø-ÿ]{2,}( [a-zÀ-ÖØ-öø-ÿ]{2,})*)*$/i';
    if (!preg_match($regExp, $stamp_creator)) {
      $this->errors['stamp_creator'] = 'Au moins 2 caractères alphabétiques pour chaque mot.';
    }
    $this->stamp_creator = $stamp_creator;
    return $this;
  }

  /**
   * Mutateur de la propriété stamp_certified 
   * @param string $stamp_certified
   * @return $this
   */    
  public function setStamp_certified($stamp_certified) {
    unset($this->errors['stamp_certified']);
    $stamp_certified = trim($stamp_certified);
    if($stamp_certified !== 'no' && $stamp_certified !== 'yes' && intval($stamp_certified) !== self::STAMP_CERTIFIED_YES && intval($stamp_certified) !== self::STAMP_CERTIFIED_NO) $this->errors['stamp_certified'] = 'Certification requise.';
    else $stamp_certified === 'yes' ? $this->stamp_certified = self::STAMP_CERTIFIED_YES : $this->stamp_certified = self::STAMP_CERTIFIED_NO;
    return $this;
  }

    /**
   * Mutateur de la propriété stamp_auction_id 
   * @param string $stamp_auction_id
   * @return $this
   */    
  public function setStamp_auction_id($stamp_auction_id) {
    unset($this->errors['stamp_auction_id']);
    $regExp = '/^\d+$/';
    if (!preg_match($regExp, $stamp_auction_id)) {
      $this->errors['stamp_auction_id'] = 'Numéro d\'identifiant incorrect.';
    }
    $this->stamp_auction_id = $stamp_auction_id;
    return $this;
  }
}