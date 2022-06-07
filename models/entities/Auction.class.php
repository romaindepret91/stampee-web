<?php

/**
 * Classe de l'entité Auction
 *
 */
class Auction extends Entity {
  protected $auction_id;
  protected $auction_date_start;
  protected $auction_date_end;
  protected $auction_starting_price;
  protected $auction_highest_bid;
  protected $auction_bids_number;
  protected $auction_favorite;
  protected $auction_date_created;
  protected $auction_user_id;

  protected $errors = array();

  const AUCTION_FAVORITE_YES = 1;
  const AUCTION_FAVORITE_NO  = 0;

  public function getAuction_id()               { return $this->auction_id; }
  public function getAuction_date_start()       { return $this->auction_date_start; }
  public function getAuction_date_end()         { return $this->auction_date_end; }
  public function getAuction_starting_price()   { return $this->auction_starting_price; }
  public function getAuction_highest_bid()      { return $this->auction_highest_bid; }
  public function getAuction_bids_number()      { return $this->auction_bids_number; }
  public function getAuction_favorite()         { return $this->auction_favorite; }
  public function getAuction_date_created()     { return $this->auction_date_created; }
  public function getAuction_user_id()          { return $this->auction_user_id; }
  public function getErrors()                  { return $this->errors; }
  
  /**
   * Mutateur de la propriété auction_id 
   * @param int $auction_id
   * @return $this
   */    
  public function setAuction_id($auction_id) {
    unset($this->errors['auction_id']);
    $regExp = '/^\d+$/';
    if (!preg_match($regExp, $auction_id)) {
      $this->errors['auction_id'] = 'Numéro d\'identifiant incorrect.';
    }
    $this->auction_id = $auction_id;
    return $this;
  }    

  /**
   * Mutateur de la propriété auction_date_start 
   * @param string $auction_date_start
   * @return $this
   */    
  public function setAuction_date_start($auction_date_start) {
    unset($this->errors['auction_date_start']);
    $today = date('Y-m-d', time());
    if($auction_date_start === "") $this->errors['auction_date_start'] = 'Champ requis';
    else if(!$this->validateDate($auction_date_start, 'Y-m-d H:i:s')) $this->errors['auction_date_start'] = 'Format de date invalide.';
    else if(date('Y-m-d', strtotime($auction_date_start)) < $today) {
        $this->errors['auction_date_start'] = 'Date inférieure à date du jour';
    }
    $this->auction_date_start = $auction_date_start;
    return $this;
  }

  /**
   * Mutateur de la propriété auction_date_end 
   * @param string $auction_date_end
   * @return $this
   */    
  public function setAuction_date_end($auction_date_end) {
    unset($this->errors['auction_date_end']);
    $today = date('Y-m-d', time());
    if($auction_date_end === "") $this->errors['auction_date_end'] = 'Champ requis';
    else if(!$this->validateDate($auction_date_end, 'Y-m-d H:i:s')) $this->errors['auction_date_end'] = 'Format de date invalide.';
    else if(date('Y-m-d', strtotime($auction_date_end)) < $today) $this->errors['auction_date_end'] = 'Date inférieure à date du jour';
    else if(date('Y-m-d', strtotime($auction_date_end)) < date('Y-m-d', strtotime($this->auction_date_start))) $this->errors['auction_date_end'] = 'Date inférieure à date de début d\'enchère.';
    $this->auction_date_end = $auction_date_end;
    return $this;
  }

  /**
   * Mutateur de la propriété auction_starting_price 
   * @param string $auction_starting_price
   * @return $this
   */    
  public function setAuction_starting_price($auction_starting_price) {
    unset($this->errors['auction_starting_price']);
    $auction_starting_price = trim($auction_starting_price);
    $regExp = '/^[1-9]\d*$/i';
    if (!preg_match($regExp, $auction_starting_price)) {
      $this->errors['auction_starting_price'] = 'Nombre positif entier';
    }
    $this->auction_starting_price = $auction_starting_price;
    return $this;
  }

  /**
   * Mutateur de la propriété auction_highest_bid 
   * @param string $auction_highest_bid
   * @return $this
   */    
  public function setAuction_highest_bid($auction_highest_bid) {
    unset($this->errors['auction_highest_bid']);
    if($auction_highest_bid !== null) {
      $auction_highest_bid = trim($auction_highest_bid);
      $regExp = '/^[1-9]\d*$/i';
      if (!preg_match($regExp, $auction_highest_bid)) {
        $this->errors['auction_highest_bid'] = 'Nombre positif entier';
      }
      else if($auction_highest_bid <= $this->auction_starting_price) {
          $this->errors['auction_highest_bid'] = 'Doit être supérieur au prix plancher';
      }
      else if($auction_highest_bid <= $this->auction_highest_bid) {
          $this->errors['auction_highest_bid'] = 'Doit être supérieur à l\'enchère la plus haute';
      }
      $this->auction_highest_bid = $auction_highest_bid;
    }
    else $this->auction_highest_bid = $this->getAuction_starting_price();
    return $this;
  }

  /**
   * Mutateur de la propriété auction_bids_number 
   * @param string $auction_bids_number
   * @return $this
   */    
  public function setAuction_bids_number($auction_bids_number) {
    unset($this->errors['auction_bids_number']);
    if($auction_bids_number !== null) {
      $auction_bids_number = trim($auction_bids_number);
      $regExp = '/^\d+$/';
      if (!preg_match($regExp, $auction_bids_number)) {
        $this->errors['auction_bids_number'] = 'Nombre d\'enchères invalide ';
      }
      $this->auction_bids_number = $auction_bids_number;
    }
    else $this->auction_bids_number = 0;
    return $this;
  }

  /**
   * Mutateur de la propriété auction_favorite 
   * @param string $auction_favorite
   * @return $this
   */    
  public function setAuction_favorite($auction_favorite) {
    unset($this->errors['auction_favorite']);
    $auction_favorite = trim($auction_favorite);
    if($auction_favorite !== 'no' && $auction_favorite !== 'yes' && intval($auction_favorite) !== self::AUCTION_FAVORITE_YES && intval($auction_favorite) !== self::AUCTION_FAVORITE_NO) $this->errors['auction_favorite'] = 'Status favori requis.';
    else $auction_favorite === 'yes' ? $this->auction_favorite = self::AUCTION_FAVORITE_YES : $this->auction_favorite = self::AUCTION_FAVORITE_NO;
    return $this;
  }

  /**
   * Mutateur de la propriété auction_date_created 
   * @param string $auction_date_created
   * @return $this
   */    
  public function setAuction_date_created($auction_date_created) {
    unset($this->errors['auction_date_created']);
    $this->auction_date_created = $auction_date_created;
    return $this;
  }

  /**
   * Mutateur de la propriété auction_user_id 
   * @param string $auction_user_id
   * @return $this
   */    
  public function setAuction_user_id($auction_user_id) {
    unset($this->errors['auction_user_id']);
    $regExp = '/^\d+$/';
    if (!preg_match($regExp, $auction_user_id)) {
      $this->errors['auction_user_id'] = 'Numéro d\'identifiant incorrect.';
    }
    $this->auction_user_id = $auction_user_id;
    return $this;
  }

   /**
   * Contrôle le format de date
   * @param string $inputDate
   * @param string $format
   * @return true si le format est valide
   */    
  function validateDate($inputDate, $format = 'Y-m-d H:i:s') {
    $date = DateTime::createFromFormat($format, $inputDate);
    return $date && $date->format($format) == $inputDate;
  }
}