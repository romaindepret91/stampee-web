<?php

/**
 * Classe des requêtes SQL
 *
 */
class SQLRequests extends PDORequests {

  // ------- SECTION UTILISATEURS/MEMBRES ---------
/**
   * Récupération des utilisateurs
   * @return array tableau d'objets Auction
   */ 
  public function getAllUsers() {
    $this->sql = "
      SELECT *
      FROM User";
     return $this->getRows();
  }

/**
   * Récupération d'un utilisateur
   * @return array tableau d'objets Auction
   */ 
  public function getUser($user_id) {
    $this->sql = "
      SELECT *
      FROM User
      WHERE User_id = :user_id";
     return $this->getRows(['user_id' => $user_id], PDORequests::SINGLE_ROW);
  }

  /**
   * Ajouter un utilisateur
   * @param array $inputs tableau des champs de l'utilisateur 
   * @return int|string clé primaire de la ligne ajoutée, message d'erreur sinon
   */ 
  public function addUser($inputs) {
    $user = $this->checkEmail(['user_email' => $inputs['user_email'], 'user_id' => 0]);
    if ($user !== false) return user::ERR_EMAIL_USED;
    $this->sql = '
      INSERT INTO User SET
      User_lastname     = :user_lastname,
      User_firstname    = :user_firstname,
      User_email        = :user_email,
      User_password     = SHA2(:user_password, 512),
      User_created_date = :user_date_created,
      Role_id           = :role_id';
    return $this->CUDRow($inputs);
  }

    /**
   * Modifier un utilisateur
   * @param array $inputs tableau des champs de l'utilisateur 
   * @return boolean|string true si modifié, message d'erreur sinon
   */ 
  public function updateUser($inputs) {
    $user = $this->checkEmail(['user_email' => $inputs['user_email'], 'user_id' => $inputs['user_id']]);
    if ($user !== false) return user::ERR_EMAIL_USED;
    $this->sql = '
      UPDATE User SET
      User_lastname         = :user_lastname,
      User_firstname        = :user_firstname,
      User_email            = :user_email,
      Role_id               = :role_id
      WHERE User_id         = :user_id';
    return $this->CUDRow($inputs);
  }

  /**
   * Supprimer un utilisateur
   * @param int $user_id clé primaire
   * @return boolean|string true si suppression effectuée, message d'erreur sinon
   */ 
  public function deleteUser($user_id) {
    $this->sql = '
      DELETE FROM `User` WHERE User_id = :user_id';
    return $this->CUDRow(['user_id' => $user_id]);
  }
  
  /**
   * Contrôler si adresse courriel non déjà utilisée par un autre utilisateur que user_id
   * @param array $inputs tableau user_email et user_id (0 si dans toute la table)
   * @return string|false utilisateur avec ce courriel, false si courriel disponible
   */ 
  public function checkEmail($inputs) {
    $this->sql = 'SELECT User_id FROM User
                  WHERE User_email = :user_email AND User_id != :user_id';
    return $this->getRows($inputs, PDORequests::SINGLE_ROW);
  }

  /**
   * Connecter un utilisateur
   * @param array $inputs, tableau avec les champs user_email et user_password  
   * @return object User
   */ 
  public function logIn($inputs) {
    $this->sql = "
      SELECT User_id, User_lastname, User_firstname, User_email, Role_id
      FROM User
      WHERE User_email = :user_email AND User_password = SHA2(:user_password, 512)";
    return $this->getRows($inputs, PDORequests::SINGLE_ROW);
  }

   /**
   * Modifier le mot de passe d'un utilisateur
   * @param array $inputs tableau des champs de l'utilisateur 
   * @return boolean true si modifié, false sinon
   */ 
  public function changeUserPassword($inputs) {
    $this->sql = '
      UPDATE User SET User_password  = SHA2(:user_password, 512)
      WHERE User_id = :user_id';
    return $this->CUDRow($inputs);
  }

  // --------- SECTION TIMBRE/ENCHÈRE ---------
  /**
   * Récupération des enchères
   * @return array tableau d'objets Auction
   */ 
  public function getAllAuctions() {
    $this->sql = "
      SELECT *
      FROM Auction
      JOIN Stamp
      ON Auction_id = Stamp_auction_id";
     return $this->getRows();
  }

  /**
   * Récupération d'une seule enchère
   * @return array tableau Auction
   */ 
  public function getSingleAuction($auction_id) {
    $this->sql = "
      SELECT *
      FROM Auction
      JOIN Stamp
      ON Auction_id = Stamp_auction_id
      WHERE Auction_id = :auction_id";
     return $this->getRows(['auction_id' => $auction_id], PDORequests::SINGLE_ROW);
  }

  /**
   * Récupération des enchères d'un membre
   * @return array tableau d'objets Auction
   */ 
  public function getMyAuctionsSales($user_id) {
    $this->sql = "
      SELECT *
      FROM Auction
      JOIN Stamp
      ON Auction_id = Stamp_auction_id
      WHERE Auction_user_id = :user_id";
     return $this->getRows(['user_id' => $user_id]);
  }

  /**
   * Ajoute un timbre
   * @param array $inputs tableau des champs du timbre
   * @return int|string clé primaire de la ligne ajoutée, message d'erreur sinon
   */ 
  public function addStamp($inputs) {
    $this->sql = '
      INSERT INTO Stamp SET
      Stamp_name                  = :stamp_name,
      Stamp_year                  = :stamp_year,
      Stamp_region_origin         = :stamp_region_origin,
      Stamp_country_origin        = :stamp_country_origin ,
      Stamp_year_end              = :stamp_year_end ,
      Stamp_main_image            = :stamp_main_image,
      Stamp_condition             = :stamp_condition ,
      Stamp_prints_number         = :stamp_prints_number,
      Stamp_dimensions            = :stamp_dimensions,
      Stamp_color                 = :stamp_color,
      Stamp_number                = :stamp_number,
      Stamp_creator               = :stamp_creator,
      Stamp_certified             = :stamp_certified,
      Stamp_auction_id            = :stamp_auction_id';
    return $this->CUDRow($inputs);
  }

  /**
   * Ajoute une enchère
   * @param array $inputs tableau des champs de l'utilisateur 
   * @return int|string clé primaire de la ligne ajoutée, message d'erreur sinon
   */ 
  public function addAuction($inputs) {
    $this->sql = '
      INSERT INTO Auction SET
      Auction_date_start         = :auction_date_start,
      Auction_date_end           = :auction_date_end,
      Auction_starting_price     = :auction_starting_price,
      Auction_highest_bid        = :auction_highest_bid ,
      Auction_bids_number        = :auction_bids_number,
      Auction_favorite           = :auction_favorite ,
      Auction_user_id            = :auction_user_id';
    return $this->CUDRow($inputs);
  }

  /**
   * Met à jour un timbre
   * @param array $inputs tableau des champs du timbre
   * @return boolean|string true si modifiée, false sinon
   */ 
  public function updateStamp($inputs) {
    $this->sql = '
      UPDATE Stamp SET
      Stamp_name                  = :stamp_name,
      Stamp_year                  = :stamp_year,
      Stamp_region_origin         = :stamp_region_origin,
      Stamp_country_origin        = :stamp_country_origin,
      Stamp_year_end              = :stamp_year_end,
      Stamp_main_image            = :stamp_main_image,
      Stamp_condition             = :stamp_condition,
      Stamp_prints_number         = :stamp_prints_number,
      Stamp_dimensions            = :stamp_dimensions,
      Stamp_color                 = :stamp_color,
      Stamp_number                = :stamp_number,
      Stamp_creator               = :stamp_creator,
      Stamp_certified             = :stamp_certified
      WHERE Stamp_auction_id      = :stamp_auction_id';
    return $this->CUDRow($inputs);
  }
  
  /**
   *  Met à jour une enchère
   * @param array $inputs tableau des champs de l'enchère
   * @return boolean|string true si modifiée, false sinon
   */ 
  public function updateAuction($inputs) {
    $this->sql = '
      UPDATE Auction SET
      Auction_date_start         = :auction_date_start,
      Auction_date_end           = :auction_date_end,
      Auction_starting_price     = :auction_starting_price,
      Auction_highest_bid        = :auction_highest_bid,
      Auction_bids_number        = :auction_bids_number,
      Auction_favorite           = :auction_favorite
      WHERE Auction_id = :auction_id AND Auction_user_id = :auction_user_id';
    return $this->CUDRow($inputs);
  }

    /**
   * Supprime une enchère
   * @return boolean|string true si suppression effectuée, message d'erreur sinon
   */ 
  public function deleteAuction($auction_id) {
    $this->sql = '
    DELETE FROM Auction WHERE Auction_id = :auction_id';
    return $this->CUDRow(['auction_id' => $auction_id]);
  }
  
    /**
   * Récupération des mises d'un membre
   * @return array tableau d'objets Auction
   */ 
  public function getMyAuctionsBids($user_id) {
    $this->sql = "
      SELECT *
      FROM bids_on
      WHERE User_id = :user_id";
     return $this->getRows(['user_id' => $user_id]);
  }

  /**
   * Ajoute une mise 
   * @param array $inputs tableau des champs de la mise 
   * @return int|string clé primaire de la ligne ajoutée, message d'erreur sinon
   */ 
  public function addBid($inputs) {
    $this->sql = '
      INSERT INTO bids_on SET
      bids_on_bid_amount          = :bid_amount,
      Auction_id                  = :auction_id,
      User_id                     = :user_id';
    return $this->CUDRow($inputs);
  }

  /**
   * Ajoute une image secondaire 
   * @param array $inputs tableau des champs de la mise 
   * @return int|string clé primaire de la ligne ajoutée, message d'erreur sinon
   */ 
  public function addImg($inputs) {
    $this->sql = '
      INSERT INTO Image_secondary SET
      Image_secondary_path          = :image_secondary_path,
      Stamp_id                      = :stamp_id';
    return $this->CUDRow($inputs);
  }

  /**
   *  Met à jour une enchère
   * @param array $inputs tableau des champs de l'enchère
   * @return boolean|string true si modifiée, false sinon
   */ 
  public function updateImg($inputs) {
    $this->sql = '
      UPDATE Image_secondary SET
      Image_secondary_path          = :image_secondary_path
      WHERE Stamp_id                = :stamp_id 
      AND Image_secondary_id        = :image_secondary_id';
    return $this->CUDRow($inputs);
  }

   /**
   * Récupération des images supplémentaires 
   * @return array tableau d'objets Auction
   */ 
  public function getImgs($stamp_id) {
    $this->sql = "
      SELECT *
      FROM Image_secondary
      WHERE Stamp_id = :stamp_id";
     return $this->getRows(['stamp_id' => $stamp_id]);
  }
}