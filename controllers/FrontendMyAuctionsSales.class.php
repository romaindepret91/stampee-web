<?php
/**
 * Classe qui gère les actions associées aux enchères d'un membre
 * 
 */
class FrontendMyAuctionsSales extends Frontend {

    private $methods = [
    'display'           => 'displayMyAuctionsSales',
    'delete'            => 'deleteMyAuctionSale',
    'update'            => 'updateMyAuctionSale'
    ];

    /**
     * Gére l'appel de la fonction correspondante à la demande d'action reçue
     */  
    public function manage($entity = "myAuctionsSales") {
        $this->entity  = $entity;
        $this->action  = $_GET['action'] ?? 'display';
        if (isset($this->methods[$this->action])) {
            $method = $this->methods[$this->action];
            $this->$method();
        } 
        else throw new Exception("L'action $this->action de l'entité $this->entity n'existe pas.");
    }
    /**
     * Affiche la page des enchères d'un membre
     */ 
    public function displayMyAuctionsSales() {
        $oMemberConnected = null;
        if(isset($_SESSION['memberConnected'])) {
            $oMemberConnected = $_SESSION['memberConnected'];
            $myAuctionsSales = self::$oSQLRequests->getMyAuctionsSales($oMemberConnected->user_id);
        }
        else {
            (new View)->generate("vLogInForm",
            array(
              'title' => "Page de connexion",
            ),
            "frontend-temp");
            exit;
        }
        $today = time();
        $i = 0;
        foreach($myAuctionsSales as $myAuctionsSale) {
            $startDateAuction = strtotime($myAuctionsSale['Auction_date_start']);
            $endDateAuction = strtotime($myAuctionsSale['Auction_date_end']);
            $myAuctionsSaleStatus = array('Auction_status' => '');
            if($startDateAuction > $today) $myAuctionsSaleStatus['Auction_status'] = 'À venir';
            else if($endDateAuction < $today) $myAuctionsSaleStatus['Auction_status'] = 'Vente clôturée';
            else $myAuctionsSaleStatus['Auction_status'] = 'En cours';
            $myAuctionsSale = array_merge($myAuctionsSale, $myAuctionsSaleStatus);
            $myAuctionsSales[$i] = $myAuctionsSale;
            $i++;
        };
        $auctionsInProgress = [];
        $auctionsToCome = [];
        $auctionsDone = [];
        foreach($myAuctionsSales as $myAuctionsSale) {
            switch($myAuctionsSale['Auction_status']){
                case 'En cours':
                    $auctionsInProgress[] = $myAuctionsSale;
                    break;
                case 'À venir':
                    $auctionsToCome[] = $myAuctionsSale;
                    break;
                case 'Vente clôturée':
                    $auctionsDone[] = $myAuctionsSale;
                    break;
            }
        }
        $date = array_column($auctionsInProgress, 'Auction_date_end');
        array_multisort($date, SORT_ASC, $auctionsInProgress);
        $date = array_column($auctionsToCome, 'Auction_date_start');
        array_multisort($date, SORT_ASC, $auctionsToCome);
        $date = array_column($auctionsDone, 'Auction_date_end');
        array_multisort($date, SORT_DESC, $auctionsDone);
        $myAuctionsSales = array_merge($auctionsToCome, $auctionsInProgress, $auctionsDone);
        (new View)->generate("vMyAuctionsSales",
                array(
                    'title'             => "Mes Ventes",
                    'myAuctionsSales'   => $myAuctionsSales,
                    'message'           => $this->resultActionMsg,
                    'oMemberConnected'  => $oMemberConnected
                ),
                "frontend-temp");
    }

    /**
     * Supprime une enchère d'un membre
     */ 
    public function deleteMyAuctionSale() {
        if(isset($_SESSION['memberConnected'])) {
            $auctionId = $_GET['auctionId'];
            if (!preg_match('/^\d+$/', $auctionId)) throw new Exception("Numéro d'enchère incorrect pour une suppression.");
            $result = self::$oSQLRequests->deleteAuction($auctionId);
            if ($result === false) $this->resultClass = "error";
            $this->resultActionMsg = "Suppression de l'enchère numéro $auctionId ".($result ? "" : "non ")."effectuée.";
            $this->displayMyAuctionsSales();
        }
        else {
            (new View)->generate("vLogInForm",
            array(
              'title' => "Page de connexion",
            ),
            "frontend-temp");
            exit;
        }
    }

    /**
     * Met à jour les propriétés d'une enchère d'un membre
     */ 
    public function updateMyAuctionSale() {
        $oMemberConnected = null;
        $auctionId = $_GET['auctionId'];
        if (!preg_match('/^\d+$/', $auctionId)) throw new Exception("Numéro d'enchère incorrect pour la modification.");
        if(isset($_SESSION['memberConnected'])) {
            $oMemberConnected = $_SESSION['memberConnected'];
            $auction = [];
            $stamp = [];
            $imageId = [];
            $resultAuction = self::$oSQLRequests->getSingleAuction($auctionId);
            foreach($resultAuction as $inputName => $input) {
                if(substr($inputName, 0, 5) === 'Stamp') $stamp[strtolower($inputName)] = $input;
                else if(substr($inputName, 0, 7) === 'Auction') $auction[strtolower($inputName)] = $input;
            }
            $resultImgs = self::$oSQLRequests->getImgs($stamp['stamp_id']);
            if(count($resultImgs) === 1) {
                $stamp['image_secondary_one'] = $resultImgs[0]['Image_secondary_path'];
                $imageId['image_secondary_one'] = $resultImgs[0]['Image_secondary_id'];
            }
            else if(count($resultImgs) === 2) {
                $stamp['image_secondary_one'] = $resultImgs[0]['Image_secondary_path'];
                $imageId['image_secondary_one'] = $resultImgs[0]['Image_secondary_id'];
                $stamp['image_secondary_two'] = $resultImgs[1]['Image_secondary_path'];
                $imageId['image_secondary_two'] = $resultImgs[1]['Image_secondary_id'];
            }
            if(!isset($_GET['update'])) {
                $auction['auction_date_end'] = date_format(date_create($auction['auction_date_end']), 'Y-m-d');
                $auction['auction_date_start'] = date_format(date_create($auction['auction_date_start']), 'Y-m-d');
                (new View)->generate("vUpLoadAuctionForm",
                array(
                  'title'               => "Modification de l'annonce",
                  'auction'             => $auction,
                  'stamp'               => $stamp,
                  'update'              => 'update',
                  'oMemberConnected'    => $oMemberConnected
                ),
                "frontend-temp");
            }
            else {
                $auction = [];
                $stamp = [];
                foreach( $_POST as $inputName => $input) {
                    if(substr($inputName, 0, 5) === 'stamp') $stamp[$inputName] = $input;
                    else if(substr($inputName, 0, 7) === 'auction') $auction[$inputName] = $input;
                }
                $today = date('Y-m-d', time());
                if($auction['auction_date_start'] === $today && $auction['auction_date_end'] === $today) {
                    $auction['auction_date_start'] = date('Y-m-d H:i:s', time());
                    $endToday = date_time_set(date_create($today), 23, 59, 59);
                    $auction['auction_date_end'] = date_format($endToday, 'Y-m-d H:i:s');
                }
                else if($auction['auction_date_start'] === $today && $auction['auction_date_end'] !== $today) {
                    $auction['auction_date_start'] = date('Y-m-d H:i:s', time());
                    $auction['auction_date_end'] = date('Y-m-d H:i:s', strtotime($auction['auction_date_end']));
                    $auction['auction_date_end'] = date_time_set(date_create($auction['auction_date_end']), 23, 59, 59);
                    $auction['auction_date_end'] = date_format($auction['auction_date_end'], 'Y-m-d H:i:s');
                }
                else {
                    $auction['auction_date_start'] = date('Y-m-d H:i:s', strtotime($auction['auction_date_start']));
                    $auction['auction_date_end'] = date('Y-m-d H:i:s', strtotime($auction['auction_date_end']));
                    $auction['auction_date_end'] = date_time_set(date_create($auction['auction_date_end']), 23, 59, 59);
                    $auction['auction_date_end'] = date_format($auction['auction_date_end'], 'Y-m-d H:i:s');
                    
                }
                $stamp['stamp_auction_id'] = $auctionId;
                $oStamp = new Stamp($stamp);
                $oAuction = new Auction($auction);
                $errorsStamp = $oStamp->errors;
                $errorsAuction = $oAuction->errors;
                $errorsImgSupp1 = [];
                $errorsImgSupp2 = [];
                if(isset($errorsAuction['auction_date_start']) && $errorsAuction['auction_date_start'] && count($errorsAuction) === 1) $errorsAuction = [];
                if($_FILES['stamp_main_image']['error'] !== 4 || $_FILES['image_secondary_one']['error'] !== 4 || $_FILES['image_secondary_two']['error'] !== 4) {
                    if($_FILES['stamp_main_image']['error'] !== 4) {
                        $oCheckFileMain = new CheckFile($_FILES['stamp_main_image'], $oMemberConnected->user_id);
                        if($oCheckFileMain->error) $errorsStamp['stamp_main_image'] = $oCheckFileMain->error;
                        else $oStamp->stamp_main_image = $oCheckFileMain->filePath;
                    }
                    if($_FILES['image_secondary_one']['error'] !== 4) {
                        $oCheckFileImgSupp1 =  new CheckFile($_FILES['image_secondary_one'], $oMemberConnected->user_id);
                        if($oCheckFileImgSupp1->error) $errorsImgSupp1['image_secondary_one'] = $oCheckFileImgSupp1->error;
                    }
                    if($_FILES['image_secondary_two']['error'] !== 4) {
                        $oCheckFileImgSupp2 =  new CheckFile($_FILES['image_secondary_two'], $oMemberConnected->user_id);
                        if($oCheckFileImgSupp2->error) $errorsImgSupp2['image_secondary_two'] = $oCheckFileImgSupp2->error;
                    }
                    if(count($errorsAuction) === 0 && count($errorsStamp) === 0 && $_FILES['image_secondary_one']['error'] === 4 && $_FILES['image_secondary_two']['error'] === 4) {
                        $inputs = [ 'auction_date_start'        => $oAuction->auction_date_start,
                                    'auction_date_end'          => $oAuction->auction_date_end,
                                    'auction_starting_price'    => $oAuction->auction_starting_price,
                                    'auction_highest_bid'       => $oAuction->auction_highest_bid,
                                    'auction_bids_number'       => $oAuction->auction_bids_number,
                                    'auction_favorite'          => $oAuction->auction_favorite,
                                    'auction_id'                => $auctionId,
                                    'auction_user_id'           => $oMemberConnected->user_id ];
                        $resultAuction = self::$oSQLRequests->updateAuction($inputs); 
                        if($resultAuction) {
                            $inputs = [ 'stamp_name'                => $oStamp->stamp_name,
                                        'stamp_year'                => $oStamp->stamp_year,
                                        'stamp_year_end'            => $oStamp->stamp_year_end,
                                        'stamp_region_origin'       => $oStamp->stamp_region_origin,
                                        'stamp_country_origin'      => $oStamp->stamp_country_origin,
                                        'stamp_main_image'          => $oStamp->stamp_main_image,
                                        'stamp_condition'           => $oStamp->stamp_condition,
                                        'stamp_color'               => $oStamp->stamp_color,
                                        'stamp_prints_number'       => intval($oStamp->stamp_prints_number),
                                        'stamp_dimensions'          => $oStamp->stamp_dimensions,
                                        'stamp_number'              => $oStamp->stamp_number,
                                        'stamp_creator'             => $oStamp->stamp_creator,
                                        'stamp_certified'           => intval($oStamp->stamp_certified),
                                        'stamp_auction_id'          => $oStamp->stamp_auction_id ];
                            $resultStamp = self::$oSQLRequests->updateStamp($inputs);
                            if($resultStamp) {
                                (new View)->generate("vUpLoadAuctionResult",
                                                array(
                                                    'title'             => "L'annonce a été mis à jour avec succès",
                                                    'message'           => 'Résumé de la vente',
                                                    'oMemberConnected'  => $oMemberConnected,
                                                    'auction'           => $oAuction,
                                                    'stamp'             => $oStamp,
                                                ),
                                                "frontend-temp");
                                exit;
                            }
                        };
                    }
                    else if(count($errorsAuction) === 0 && count($errorsStamp) === 0 && $_FILES['image_secondary_one']['error'] === 0 && count($errorsImgSupp1) === 0 && $_FILES['image_secondary_two']['error'] === 4) {
                        $inputs = [ 'auction_date_start'        => $oAuction->auction_date_start,
                                    'auction_date_end'          => $oAuction->auction_date_end,
                                    'auction_starting_price'    => $oAuction->auction_starting_price,
                                    'auction_highest_bid'       => $oAuction->auction_highest_bid,
                                    'auction_bids_number'       => $oAuction->auction_bids_number,
                                    'auction_favorite'          => $oAuction->auction_favorite,
                                    'auction_id'                => $auctionId,
                                    'auction_user_id'           => $oMemberConnected->user_id ];
                        $resultAuction = self::$oSQLRequests->updateAuction($inputs); 
                        if($resultAuction) {
                            $inputs = [ 'stamp_name'                => $oStamp->stamp_name,
                                        'stamp_year'                => $oStamp->stamp_year,
                                        'stamp_year_end'            => $oStamp->stamp_year_end,
                                        'stamp_region_origin'       => $oStamp->stamp_region_origin,
                                        'stamp_country_origin'      => $oStamp->stamp_country_origin,
                                        'stamp_main_image'          => $oStamp->stamp_main_image,
                                        'stamp_condition'           => $oStamp->stamp_condition,
                                        'stamp_color'               => $oStamp->stamp_color,
                                        'stamp_prints_number'       => intval($oStamp->stamp_prints_number),
                                        'stamp_dimensions'          => $oStamp->stamp_dimensions,
                                        'stamp_number'              => $oStamp->stamp_number,
                                        'stamp_creator'             => $oStamp->stamp_creator,
                                        'stamp_certified'           => intval($oStamp->stamp_certified),
                                        'stamp_auction_id'          => $oStamp->stamp_auction_id ];
                            $resultStamp = self::$oSQLRequests->updateStamp($inputs);
                            if($resultStamp) {
                                if(isset($imageId['image_secondary_one'])) {
                                    $resultImg = self::$oSQLRequests->updateImg([
                                        'image_secondary_path'      => $oCheckFileImgSupp1->filePath,
                                        'stamp_id'                  => $stamp['stamp_id'],
                                        'image_secondary_id'        => $imageId['image_secondary_one']
                                    ]);
                                }
                                else {
                                    $resultImg = self::$oSQLRequests->addImg([
                                        'image_secondary_path'      => $oCheckFileImgSupp1->filePath,
                                        'stamp_id'                  => $stamp['stamp_id']
                                    ]);
                                }
                                if($resultImg) {
                                    (new View)->generate("vUpLoadAuctionResult",
                                                        array(
                                                            'title'             => "L'annonce a été mis à jour avec succès",
                                                            'message'           => 'Résumé de la vente',
                                                            'oMemberConnected'  => $oMemberConnected,
                                                            'auction'           => $oAuction,
                                                            'stamp'             => $oStamp,
                                                        ),
                                                        "frontend-temp");
                                    exit;
                                }
                            }
                        };
                    }
                    else if(count($errorsAuction) === 0 && count($errorsStamp) === 0 && $_FILES['image_secondary_two']['error'] === 0 && count($errorsImgSupp2) === 0 && $_FILES['image_secondary_one']['error'] === 4) {
                        $inputs = [ 'auction_date_start'        => $oAuction->auction_date_start,
                                    'auction_date_end'          => $oAuction->auction_date_end,
                                    'auction_starting_price'    => $oAuction->auction_starting_price,
                                    'auction_highest_bid'       => $oAuction->auction_highest_bid,
                                    'auction_bids_number'       => $oAuction->auction_bids_number,
                                    'auction_favorite'          => $oAuction->auction_favorite,
                                    'auction_id'                => $auctionId,
                                    'auction_user_id'           => $oMemberConnected->user_id ];
                        $resultAuction = self::$oSQLRequests->updateAuction($inputs); 
                        if($resultAuction) {
                            $inputs = [ 'stamp_name'                => $oStamp->stamp_name,
                                        'stamp_year'                => $oStamp->stamp_year,
                                        'stamp_year_end'            => $oStamp->stamp_year_end,
                                        'stamp_region_origin'       => $oStamp->stamp_region_origin,
                                        'stamp_country_origin'      => $oStamp->stamp_country_origin,
                                        'stamp_main_image'          => $oStamp->stamp_main_image,
                                        'stamp_condition'           => $oStamp->stamp_condition,
                                        'stamp_color'               => $oStamp->stamp_color,
                                        'stamp_prints_number'       => intval($oStamp->stamp_prints_number),
                                        'stamp_dimensions'          => $oStamp->stamp_dimensions,
                                        'stamp_number'              => $oStamp->stamp_number,
                                        'stamp_creator'             => $oStamp->stamp_creator,
                                        'stamp_certified'           => intval($oStamp->stamp_certified),
                                        'stamp_auction_id'          => $oStamp->stamp_auction_id ];
                            $resultStamp = self::$oSQLRequests->updateStamp($inputs);
                            if($resultStamp) {
                                if(isset($imageId['image_secondary_two'])) {
                                    $resultImg = self::$oSQLRequests->updateImg([
                                        'image_secondary_path'      => $oCheckFileImgSupp2->filePath,
                                        'stamp_id'                  => $stamp['stamp_id'],
                                        'image_secondary_id'        => $imageId['image_secondary_two']
                                    ]);
                                }
                                else {
                                    $resultImg = self::$oSQLRequests->addImg([
                                        'image_secondary_path'      => $oCheckFileImgSupp2->filePath,
                                        'stamp_id'                  => $stamp['stamp_id']
                                    ]);
                                }
                                if($resultImg) {
                                    (new View)->generate("vUpLoadAuctionResult",
                                                        array(
                                                            'title'             => "L'annonce a été mis à jour avec succès",
                                                            'message'           => 'Résumé de la vente',
                                                            'oMemberConnected'  => $oMemberConnected,
                                                            'auction'           => $oAuction,
                                                            'stamp'             => $oStamp,
                                                        ),
                                                        "frontend-temp");
                                    exit;
                                }
                            }
                        };
                    }
                    else if(count($errorsAuction) === 0 && count($errorsStamp) === 0 && $_FILES['image_secondary_one']['error'] === 0 && count($errorsImgSupp1) === 0 && $_FILES['image_secondary_two']['error'] === 0 && count($errorsImgSupp2) === 0) {
                        $inputs = [ 'auction_date_start'        => $oAuction->auction_date_start,
                                    'auction_date_end'          => $oAuction->auction_date_end,
                                    'auction_starting_price'    => $oAuction->auction_starting_price,
                                    'auction_highest_bid'       => $oAuction->auction_highest_bid,
                                    'auction_bids_number'       => $oAuction->auction_bids_number,
                                    'auction_favorite'          => $oAuction->auction_favorite,
                                    'auction_id'                => $auctionId,
                                    'auction_user_id'           => $oMemberConnected->user_id ];
                        $resultAuction = self::$oSQLRequests->updateAuction($inputs); 
                        if($resultAuction) {
                            $inputs = [ 'stamp_name'                => $oStamp->stamp_name,
                                        'stamp_year'                => $oStamp->stamp_year,
                                        'stamp_year_end'            => $oStamp->stamp_year_end,
                                        'stamp_region_origin'       => $oStamp->stamp_region_origin,
                                        'stamp_country_origin'      => $oStamp->stamp_country_origin,
                                        'stamp_main_image'          => $oStamp->stamp_main_image,
                                        'stamp_condition'           => $oStamp->stamp_condition,
                                        'stamp_color'               => $oStamp->stamp_color,
                                        'stamp_prints_number'       => intval($oStamp->stamp_prints_number),
                                        'stamp_dimensions'          => $oStamp->stamp_dimensions,
                                        'stamp_number'              => $oStamp->stamp_number,
                                        'stamp_creator'             => $oStamp->stamp_creator,
                                        'stamp_certified'           => intval($oStamp->stamp_certified),
                                        'stamp_auction_id'          => $oStamp->stamp_auction_id ];
                            $resultStamp = self::$oSQLRequests->updateStamp($inputs);
                            if($resultStamp) {
                                if(isset($imageId['image_secondary_one'])) {
                                    $resultImg = self::$oSQLRequests->updateImg([
                                        'image_secondary_path'      => $oCheckFileImgSupp1->filePath,
                                        'stamp_id'                  => $stamp['stamp_id'],
                                        'image_secondary_id'        => $imageId['image_secondary_one']
                                    ]);
                                }
                                else {
                                    $resultImg = self::$oSQLRequests->addImg([
                                        'image_secondary_path'      => $oCheckFileImgSupp1->filePath,
                                        'stamp_id'                  => $stamp['stamp_id']
                                    ]);
                                }
                                if($resultImg) {
                                    if(isset($imageId['image_secondary_two'])) {
                                        $resultImg = self::$oSQLRequests->updateImg([
                                            'image_secondary_path'      => $oCheckFileImgSupp2->filePath,
                                            'stamp_id'                  => $stamp['stamp_id'],
                                            'image_secondary_id'        => $imageId['image_secondary_two']
                                        ]);
                                    }
                                    else {
                                        $resultImg = self::$oSQLRequests->addImg([
                                            'image_secondary_path'      => $oCheckFileImgSupp2->filePath,
                                            'stamp_id'                  => $stamp['stamp_id']
                                        ]);
                                    }
                                    if($resultImg) {
                                        (new View)->generate("vUpLoadAuctionResult",
                                                            array(
                                                                'title'             => "L'annonce a été mis à jour avec succès",
                                                                'message'           => 'Résumé de la vente',
                                                                'oMemberConnected'  => $oMemberConnected,
                                                                'auction'           => $oAuction,
                                                                'stamp'             => $oStamp,
                                                            ),
                                                            "frontend-temp");
                                        exit;
                                    };
                                };
                            };
                        };
                    }
                    else {
                        if($_POST['image_secondary_one'] !== '') $stamp['image_secondary_one'] = $_POST['image_secondary_one'];
                        if($_POST['image_secondary_two'] !== '') $stamp['image_secondary_two'] = $_POST['image_secondary_two'];
                        $auction['auction_date_end'] = date_format(date_create($auction['auction_date_end']), 'Y-m-d');
                        $auction['auction_date_start'] = date_format(date_create($auction['auction_date_start']), 'Y-m-d');
                        (new View)->generate("vUpLoadAuctionForm",
                                            array(
                                                'title'             => "Modification de l'annonce",
                                                'oMemberConnected'  => $oMemberConnected,
                                                'auction'           => $auction,
                                                'stamp'             => $stamp,
                                                'errorsAuction'     => $errorsAuction,
                                                'errorsStamp'       => $errorsStamp,
                                                'errorsImg1'        => $oCheckFileImgSupp1->error,
                                                'errorsImg2'        => $oCheckFileImgSupp2->error, 
                                                'update'            => 'update'
                                            ),
                                            "frontend-temp");
                        exit;
                    }
                }
                else if(count($errorsAuction) === 0 && count($errorsStamp) === 0) {
                    $inputs = [ 'stamp_name'                => $oStamp->stamp_name,
                                'stamp_year'                => $oStamp->stamp_year,
                                'stamp_year_end'            => $oStamp->stamp_year_end,
                                'stamp_region_origin'       => $oStamp->stamp_region_origin,
                                'stamp_country_origin'      => $oStamp->stamp_country_origin,
                                'stamp_main_image'          => $oStamp->stamp_main_image,
                                'stamp_condition'           => $oStamp->stamp_condition,
                                'stamp_color'               => $oStamp->stamp_color,
                                'stamp_prints_number'       => intval($oStamp->stamp_prints_number),
                                'stamp_dimensions'          => $oStamp->stamp_dimensions,
                                'stamp_number'              => $oStamp->stamp_number,
                                'stamp_creator'             => $oStamp->stamp_creator,
                                'stamp_certified'           => intval($oStamp->stamp_certified),
                                'stamp_auction_id'          => $oStamp->stamp_auction_id];
                    $resultStamp = self::$oSQLRequests->updateStamp($inputs);
                    if($resultStamp) {
                        $inputs = [
                            'auction_date_start'        => $oAuction->auction_date_start,
                            'auction_date_end'          => $oAuction->auction_date_end,
                            'auction_starting_price'    => $oAuction->auction_starting_price,
                            'auction_highest_bid'       => $oAuction->auction_highest_bid,
                            'auction_bids_number'       => $oAuction->auction_bids_number,
                            'auction_favorite'          => $oAuction->auction_favorite,
                            'auction_id'                => $auctionId,
                            'auction_user_id'           => $oMemberConnected->user_id
                        ];
                        $resultAuction = self::$oSQLRequests->updateAuction($inputs);
                        if($resultAuction) {
                            (new View)->generate("vUpLoadAuctionResult",
                                                array(
                                                    'title'             => "L'annonce a été mis à jour avec succès",
                                                    'message'           => 'Résumé de la vente',
                                                    'oMemberConnected'  => $oMemberConnected,
                                                    'auction'           => $oAuction,
                                                    'stamp'             => $oStamp,
                                                ),
                                                "frontend-temp");
                            exit;
                        }
                    }
                }
                else {
                    if($_POST['image_secondary_one'] !== '') $stamp['image_secondary_one'] = $_POST['image_secondary_one'];
                    if($_POST['image_secondary_two'] !== '') $stamp['image_secondary_two'] = $_POST['image_secondary_two'];
                    $auction['auction_date_end'] = date_format(date_create($auction['auction_date_end']), 'Y-m-d');
                    $auction['auction_date_start'] = date_format(date_create($auction['auction_date_start']), 'Y-m-d');
                    (new View)->generate("vUpLoadAuctionForm",
                                        array(
                                            'title'             => "Modification de l'annonce",
                                            'oMemberConnected'  => $oMemberConnected,
                                            'auction'           => $auction,
                                            'stamp'             => $stamp,
                                            'errorsAuction'     => $errorsAuction,
                                            'errorsStamp'       => $errorsStamp,
                                            'update'            => 'update'
                                        ),
                                        "frontend-temp");
                    exit;
                }
            }
        }
        else {
            (new View)->generate("vLogInForm",
                                array(
                                'title' => "Page de connexion",
                                ),
                                "frontend-temp");
            exit;
        }
    }
}
?>