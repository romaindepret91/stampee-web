<?php
/**
 * Classe qui gère les actions associées à l'ajout d'une enchère par un membre
 * 
 */
class FrontendUpLoadAuction extends Frontend {

    private $methods = [
    'display'           => 'displayUpLoadAuctionForm',
    'upload'            => 'upLoadAuction'
    ];

    /**
     * Gére l'appel de la fonction correspondante à la demande d'action reçue
     */ 
    public function manage($entity = "upLoadAuction") {
        $this->entity  = $entity;
        $this->action  = $_GET['action'] ?? 'display';
        if (isset($this->methods[$this->action])) {
            $method = $this->methods[$this->action];
            $this->$method();
        } else throw new Exception("L'action $this->action de l'entité $this->entity n'existe pas.");
    }

    /**
     * Affiche la page d'ajout de l'enchère
     */ 
    public function displayUpLoadAuctionForm() {
        $oMemberConnected = null;
        if(isset($_SESSION['memberConnected'])) $oMemberConnected = $_SESSION['memberConnected'];
        (new View)->generate("vUpLoadAuctionForm",
                array(
                    'title' => "Formulaire de vente",
                    'oMemberConnected' => $oMemberConnected
                ),
                "frontend-temp");
    }

    /**
     * Ajoute une enchère 
     */ 
    public function upLoadAuction() {
        $oMemberConnected = null;
        if(!isset($_SESSION['memberConnected'])) throw new Exception(self::ERROR_FORBIDDEN);
        else {
            $oMemberConnected = $_SESSION['memberConnected'];
            $stamp = [];
            $auction = [];
            if (count($_POST) !== 0 && !isset($_GET['reload'])) {
                unset($_POST['stamp_id']);
                unset($_POST['auction_id']);
                unset($_POST['stamp_main_image']);
                foreach( $_POST as $inputName => $input) {
                    if(substr($inputName, 0, 5) === 'stamp') $stamp[$inputName] = $input;
                    else if(substr($inputName, 0, 7) === 'auction') $auction[$inputName] = $input;
                }
                $today = date('Y-m-d', time());
                if($auction['auction_date_start'] === $today && $auction['auction_date_end'] === $today && $auction['auction_date_start'] !== "" && $auction['auction_date_end'] !== "") {
                    $auction['auction_date_start'] = date('Y-m-d H:i:s', time());
                    $endToday = date_time_set(date_create($today), 23, 59, 59);
                    $auction['auction_date_end'] = date_format($endToday, 'Y-m-d H:i:s');
                }
                else if($auction['auction_date_start'] === $today && $auction['auction_date_end'] !== $today && $auction['auction_date_start'] !== "" && $auction['auction_date_end'] !== "") {
                    $auction['auction_date_start'] = date('Y-m-d H:i:s', time());
                    $auction['auction_date_end'] = date('Y-m-d H:i:s', strtotime($auction['auction_date_end']));
                    $auction['auction_date_end'] = date_time_set(date_create($auction['auction_date_end']), 23, 59, 59);
                    $auction['auction_date_end'] = date_format($auction['auction_date_end'], 'Y-m-d H:i:s');
                }
                else {
                    if($auction['auction_date_start'] !== "") $auction['auction_date_start'] = date('Y-m-d H:i:s', strtotime($auction['auction_date_start']));
                    if($auction['auction_date_end'] !== ""){
                        $auction['auction_date_end'] = date('Y-m-d H:i:s', strtotime($auction['auction_date_end']));
                        $auction['auction_date_end'] = date_time_set(date_create($auction['auction_date_end']), 23, 59, 59);
                        $auction['auction_date_end'] = date_format($auction['auction_date_end'], 'Y-m-d H:i:s');
                    }
                }
                $oStamp = new Stamp($stamp);
                $oAuction = new Auction($auction);
                $errorsStamp = $oStamp->errors;
                $errorsAuction = $oAuction->errors;
                $errorsImgSupp1 = '';
                $errorsImgSupp2 = '';
                if($_FILES['stamp_main_image']['error'] === 4) $errorsStamp['stamp_main_image'] = 'Une image est requise';
                else {
                    $oCheckFileMain = new CheckFile($_FILES['stamp_main_image'], $oMemberConnected->user_id);
                    if($oCheckFileMain->error) $errorsStamp['stamp_main_image'] = $oCheckFileMain->error;
                    else {
                        $oStamp->stamp_main_image = $oCheckFileMain->filePath;
                        if($_FILES['image_secondary_one']['error'] !== 4) {
                            $oCheckFileImgSupp1 =  new CheckFile($_FILES['image_secondary_one'], $oMemberConnected->user_id);
                            if($oCheckFileImgSupp1->error) $errorsImgSupp1 = $oCheckFileImgSupp1->error;
                        }
                        if($_FILES['image_secondary_two']['error'] !== 4) {
                            $oCheckFileImgSupp2 =  new CheckFile($_FILES['image_secondary_two'], $oMemberConnected->user_id);
                            if($oCheckFileImgSupp2->error) $errorsImgSupp2 = $oCheckFileImgSupp2->error;
                        }
                    }
                }
                if(count($errorsAuction) === 0 && count($errorsStamp) === 0 && $_FILES['image_secondary_one']['error'] === 4 && $_FILES['image_secondary_two']['error'] === 4) {
                    $resultAuction = self::$oSQLRequests->addAuction([
                        'auction_date_start'        => $oAuction->auction_date_start,
                        'auction_date_end'          => $oAuction->auction_date_end,
                        'auction_starting_price'    => $oAuction->auction_starting_price,
                        'auction_highest_bid'       => $oAuction->auction_highest_bid,
                        'auction_bids_number'       => $oAuction->auction_bids_number,
                        'auction_favorite'          => $oAuction->auction_favorite,
                        'auction_user_id'           => $oMemberConnected->user_id,
                    ]);
                    if($resultAuction) {
                        $resultStamp = self::$oSQLRequests->addStamp([
                        'stamp_name'                => $oStamp->stamp_name,
                        'stamp_year'                => $oStamp->stamp_year,
                        'stamp_year_end'            => $oStamp->stamp_year_end,
                        'stamp_region_origin'       => $oStamp->stamp_region_origin,
                        'stamp_country_origin'      => $oStamp->stamp_country_origin,
                        'stamp_main_image'          => $oStamp->stamp_main_image,
                        'stamp_condition'           => $oStamp->stamp_condition,
                        'stamp_color'               => $oStamp->stamp_color,
                        'stamp_prints_number'       => $oStamp->stamp_prints_number,
                        'stamp_dimensions'          => $oStamp->stamp_dimensions,
                        'stamp_number'              => $oStamp->stamp_number,
                        'stamp_creator'             => $oStamp->stamp_creator,
                        'stamp_certified'           => $oStamp->stamp_certified,
                        'stamp_auction_id'          => $resultAuction
                        ]);
                        if($resultStamp) (new View)->generate("vUpLoadAuctionResult",
                                                array(
                                                    'title'             => "Lot mis en vente avec succès",
                                                    'message'           => 'Résumé de la vente',
                                                    'oMemberConnected'  => $oMemberConnected,
                                                    'auction'           => $oAuction,
                                                    'stamp'             => $oStamp,
                                                ),
                                                "frontend-temp");
                    }
                }
                else if(count($errorsAuction) === 0 && count($errorsStamp) === 0 && $_FILES['image_secondary_one']['error'] === 0 && $errorsImgSupp1 === '' && $_FILES['image_secondary_two']['error'] === 4) {
                    $resultAuction = self::$oSQLRequests->addAuction([
                        'auction_date_start'        => $oAuction->auction_date_start,
                        'auction_date_end'          => $oAuction->auction_date_end,
                        'auction_starting_price'    => $oAuction->auction_starting_price,
                        'auction_highest_bid'       => $oAuction->auction_highest_bid,
                        'auction_bids_number'       => $oAuction->auction_bids_number,
                        'auction_favorite'          => $oAuction->auction_favorite,
                        'auction_user_id'           => $oMemberConnected->user_id,
                    ]);
                    if($resultAuction) {
                        $resultStamp = self::$oSQLRequests->addStamp([
                        'stamp_name'                => $oStamp->stamp_name,
                        'stamp_year'                => $oStamp->stamp_year,
                        'stamp_year_end'            => $oStamp->stamp_year_end,
                        'stamp_region_origin'       => $oStamp->stamp_region_origin,
                        'stamp_country_origin'      => $oStamp->stamp_country_origin,
                        'stamp_main_image'          => $oStamp->stamp_main_image,
                        'stamp_condition'           => $oStamp->stamp_condition,
                        'stamp_color'               => $oStamp->stamp_color,
                        'stamp_prints_number'       => $oStamp->stamp_prints_number,
                        'stamp_dimensions'          => $oStamp->stamp_dimensions,
                        'stamp_number'              => $oStamp->stamp_number,
                        'stamp_creator'             => $oStamp->stamp_creator,
                        'stamp_certified'           => $oStamp->stamp_certified,
                        'stamp_auction_id'          => $resultAuction
                        ]);
                        if($resultStamp) {
                            $resultImg = self::$oSQLRequests->addImg([
                                'image_secondary_path'      => $oCheckFileImgSupp1->filePath,
                                'stamp_id'                  => $resultStamp
                            ]);
                            if($resultImg) {
                                (new View)->generate("vUpLoadAuctionResult",
                                array(
                                    'title'             => "Lot mis en vente avec succès",
                                    'message'           => 'Résumé de la vente',
                                    'oMemberConnected'  => $oMemberConnected,
                                    'auction'           => $oAuction,
                                    'stamp'             => $oStamp,
                                ),
                                "frontend-temp");
                            }
                        }
                    }
                }
                else if(count($errorsAuction) === 0 && count($errorsStamp) === 0 && $_FILES['image_secondary_two']['error'] === 0 && $errorsImgSupp2 === '' && $_FILES['image_secondary_one']['error'] === 4) {
                    $resultAuction = self::$oSQLRequests->addAuction([
                        'auction_date_start'        => $oAuction->auction_date_start,
                        'auction_date_end'          => $oAuction->auction_date_end,
                        'auction_starting_price'    => $oAuction->auction_starting_price,
                        'auction_highest_bid'       => $oAuction->auction_highest_bid,
                        'auction_bids_number'       => $oAuction->auction_bids_number,
                        'auction_favorite'          => $oAuction->auction_favorite,
                        'auction_user_id'           => $oMemberConnected->user_id,
                    ]);
                    if($resultAuction) {
                        $resultStamp = self::$oSQLRequests->addStamp([
                        'stamp_name'                => $oStamp->stamp_name,
                        'stamp_year'                => $oStamp->stamp_year,
                        'stamp_year_end'            => $oStamp->stamp_year_end,
                        'stamp_region_origin'       => $oStamp->stamp_region_origin,
                        'stamp_country_origin'      => $oStamp->stamp_country_origin,
                        'stamp_main_image'          => $oStamp->stamp_main_image,
                        'stamp_condition'           => $oStamp->stamp_condition,
                        'stamp_color'               => $oStamp->stamp_color,
                        'stamp_prints_number'       => $oStamp->stamp_prints_number,
                        'stamp_dimensions'          => $oStamp->stamp_dimensions,
                        'stamp_number'              => $oStamp->stamp_number,
                        'stamp_creator'             => $oStamp->stamp_creator,
                        'stamp_certified'           => $oStamp->stamp_certified,
                        'stamp_auction_id'          => $resultAuction
                        ]);
                        if($resultStamp) {
                            $resultImg = self::$oSQLRequests->addImg([
                                'image_secondary_path'      => $oCheckFileImgSupp2->filePath,
                                'stamp_id'                  => $resultStamp
                            ]);
                            if($resultImg) {
                                (new View)->generate("vUpLoadAuctionResult",
                                array(
                                    'title'             => "Lot mis en vente avec succès",
                                    'message'           => 'Résumé de la vente',
                                    'oMemberConnected'  => $oMemberConnected,
                                    'auction'           => $oAuction,
                                    'stamp'             => $oStamp,
                                ),
                                "frontend-temp");
                            }
                        }
                    }
                }
                else if(count($errorsAuction) === 0 && count($errorsStamp) === 0 && $_FILES['image_secondary_one']['error'] === 0 && $errorsImgSupp1 === '' && $_FILES['image_secondary_two']['error'] === 0 && $errorsImgSupp2 === '') {
                    $resultAuction = self::$oSQLRequests->addAuction([
                        'auction_date_start'        => $oAuction->auction_date_start,
                        'auction_date_end'          => $oAuction->auction_date_end,
                        'auction_starting_price'    => $oAuction->auction_starting_price,
                        'auction_highest_bid'       => $oAuction->auction_highest_bid,
                        'auction_bids_number'       => $oAuction->auction_bids_number,
                        'auction_favorite'          => $oAuction->auction_favorite,
                        'auction_user_id'           => $oMemberConnected->user_id,
                    ]);
                    if($resultAuction) {
                        $resultStamp = self::$oSQLRequests->addStamp([
                        'stamp_name'                => $oStamp->stamp_name,
                        'stamp_year'                => $oStamp->stamp_year,
                        'stamp_year_end'            => $oStamp->stamp_year_end,
                        'stamp_region_origin'       => $oStamp->stamp_region_origin,
                        'stamp_country_origin'      => $oStamp->stamp_country_origin,
                        'stamp_main_image'          => $oStamp->stamp_main_image,
                        'stamp_condition'           => $oStamp->stamp_condition,
                        'stamp_color'               => $oStamp->stamp_color,
                        'stamp_prints_number'       => $oStamp->stamp_prints_number,
                        'stamp_dimensions'          => $oStamp->stamp_dimensions,
                        'stamp_number'              => $oStamp->stamp_number,
                        'stamp_creator'             => $oStamp->stamp_creator,
                        'stamp_certified'           => $oStamp->stamp_certified,
                        'stamp_auction_id'          => $resultAuction
                        ]);
                        if($resultStamp) {
                            $resultImg1 = self::$oSQLRequests->addImg([
                                'image_secondary_path'      => $oCheckFileImgSupp1->filePath,
                                'stamp_id'                  => $resultStamp
                            ]);
                            if($resultImg1) {
                                $resultImg2 = self::$oSQLRequests->addImg([
                                    'image_secondary_path'      => $oCheckFileImgSupp2->filePath,
                                    'stamp_id'                  => $resultStamp
                                ]);
                                if($resultImg2) {
                                    (new View)->generate("vUpLoadAuctionResult",
                                    array(
                                        'title'             => "Lot mis en vente avec succès",
                                        'message'           => 'Résumé de la vente',
                                        'oMemberConnected'  => $oMemberConnected,
                                        'auction'           => $oAuction,
                                        'stamp'             => $oStamp,
                                    ),
                                    "frontend-temp");
                                }
                            }
                        }
                    }
                }
                else {
                    if($auction['auction_date_end'] !== "") $auction['auction_date_end'] = date_format(date_create($auction['auction_date_end']), 'Y-m-d');
                    if($auction['auction_date_start'] !== "") $auction['auction_date_start'] = date_format(date_create($auction['auction_date_start']), 'Y-m-d');
                    (new View)->generate("vUpLoadAuctionForm",
                        array(
                            'title'             => "Formulaire de vente",
                            'oMemberConnected'  => $oMemberConnected,
                            'auction'           => $auction,
                            'stamp'             => $stamp,
                            'errorsAuction'     => $errorsAuction,
                            'errorsStamp'       => $errorsStamp,
                            'errorsImg1'        => $errorsImgSupp1,
                            'errorsImg2'        => $errorsImgSupp2   
                        ),
                        "frontend-temp");
                }
            }
            else (new View)->generate("vUpLoadAuctionResult",
            array(
                'title'                     => "Lot mis en vente avec succès",
                'message'                   => 'Résumé de la vente',
                'stamp_name'                => $_GET['stamp_name'],
                'stamp_country_origin'      => $_GET['stamp_country_origin'],
                'auction_date_start'        => $_GET['auction_date_start'],
                'auction_date_end'          => $_GET['auction_date_end'],
                'auction_starting_price'    => $_GET['auction_starting_price'],
                'stamp_main_image '         => $_GET['stamp_main_image'],
                'oMemberConnected'          => $oMemberConnected,
            ),
            "frontend-temp");
        }
    }
}
?>