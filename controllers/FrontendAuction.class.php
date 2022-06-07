<?php
/**
 * Classe qui gère les actions associées à la page fiche d'une enchère
 * 
 */
class FrontendAuction extends Frontend {

    private $methods = [
    'display'       => 'displayAuction',
    'bid'           => 'addBidAuction'               
    ];

    /**
     * Gére l'appel de la fonction correspondante à la demande d'action reçue
     */  
    public function manage($entity = "auction") {
        $this->entity  = $entity;
        $this->action  = $_GET['action'] ?? 'display';
        if (isset($this->methods[$this->action])) {
            $method = $this->methods[$this->action];
            $this->$method();
        } 
        else throw new Exception("L'action $this->action de l'entité $this->entity n'existe pas.");
    }

     /**
     * Affiche la page de fiche d'enchère 
     */  
    public function displayAuction() {
        $oMemberConnected = null;
        $auctionId = $_GET['auctionId'];
        if(isset($_SESSION['memberConnected'])) $oMemberConnected = $_SESSION['memberConnected'];
        if (!preg_match('/^\d+$/', $auctionId)) throw new Exception("Numéro d'enchère non valide.");
        $auction = self::$oSQLRequests->getSingleAuction($auctionId);
        $imgsSupp = self::$oSQLRequests->getImgs($auction['Stamp_id']);
        $imgSupp1 = null;
        $imgSupp2 = null;
        if(isset($imgsSupp[0])) $imgSupp1 = $imgsSupp[0];
        if(isset($imgsSupp[1])) $imgSupp2 = $imgsSupp[1];
        $today = time();
        $timeleft = 0;
        $daysLeft = 0;
        $hoursLeft = 0;
        $minLeft = 0;
        $startDateAuction = strtotime($auction['Auction_date_start']);
        $endDateAuction = strtotime($auction['Auction_date_end']);
        $auctionStatus = array('Auction_status' => '');
        if($startDateAuction > $today) {
            $auctionStatus['Auction_status'] = 'À venir';
            $timeleft = $startDateAuction - $today;
            $daysLeft = $timeleft / 86400;
            $hoursLeft = (($daysLeft - intval($daysLeft)) * 24);
            $minLeft = (($hoursLeft - intval($hoursLeft)) * 60);
        } 
        else if($startDateAuction <= $today && $endDateAuction >= $today) {
            $auctionStatus['Auction_status'] = 'En cours';
            $timeleft = $endDateAuction - $today;
            $daysLeft = $timeleft / 86400;
            $hoursLeft = (($daysLeft - intval($daysLeft)) * 24);
            $minLeft = (($hoursLeft - intval($hoursLeft)) * 60);
        }
        else $auctionStatus['Auction_status'] = 'Vente clôturée';
        $auction = array_merge($auction, $auctionStatus);
        $auctions = self::$oSQLRequests->getAllAuctions();
        $similarAuctions = [];
        foreach($auctions as $auctionToCompare) {
            if($auctionToCompare['Stamp_region_origin'] === $auction['Stamp_region_origin'] && strtotime($auctionToCompare['Auction_date_end']) > $today && $auctionToCompare['Auction_id'] !== $auction['Auction_id']) $similarAuctions[] = $auctionToCompare;
        }
        $similarAuctionsSelected = [];
        shuffle($similarAuctions);
        if(count($similarAuctions) >= 3) {
            for($i = 0; $i <= 2; $i++) {
                $similarAuctionsSelected[$i] = $similarAuctions[$i]; 
            }
        }
        elseif(count($similarAuctions) === 2) {
            for($i = 0; $i <= 1; $i++) {
                $similarAuctionsSelected[$i] = $similarAuctions[$i]; 
            }
            $similarAuctionsSelected[2] = '';
        }
        elseif(count($similarAuctions) === 1) {
         $similarAuctionsSelected[0] = $similarAuctions[0]; 
         $similarAuctionsSelected[1] = '';
         $similarAuctionsSelected[2] = '';
        }
        else {
            $similarAuctionsSelected[0] = ''; 
            $similarAuctionsSelected[1] = '';
            $similarAuctionsSelected[2] = '';
        }

            
        (new View)->generate("vAuction",
                array(
                    'title'             => "Lord Stampee - Auction",
                    'auction'           => $auction, 
                    'similarAuctions'   => $similarAuctionsSelected,
                    'imgSupp1'          => $imgSupp1,
                    'imgSupp2'          => $imgSupp2,
                    'daysLeft'          => intval($daysLeft),
                    'hoursLeft'         => intval($hoursLeft),
                    'minLeft'           => intval($minLeft),
                    'message'           => $this->resultActionMsg,
                    'resultClass'       => $this->resultClass,
                    'oMemberConnected'  => $oMemberConnected
                ),
                "frontend-temp");
    }

    /**
     * Ajoute une mise sur l'enchère
     */  
    function addBidAuction() {
        $oMemberConnected = null;
        $auctionId = $_GET['auctionId'];
        $bidAmount = $_GET['bidAmount'];
        if(isset($_SESSION['memberConnected'])) $oMemberConnected = $_SESSION['memberConnected'];
        if (!preg_match('/^\d+$/', $auctionId)) throw new Exception("Numéro d'enchère non valide.");
        if (!preg_match('/^\d+$/', $bidAmount)) throw new Exception("Montant entré non valide.");
        $auction = self::$oSQLRequests->getSingleAuction($auctionId);
        if($auction) {
            if($bidAmount > $auction['Auction_highest_bid']){

                $inputs = [
                    'bid_amount'    => $bidAmount,
                    'auction_id'    => $auctionId,
                    'user_id'       => $oMemberConnected->user_id
                ];
                $result = self::$oSQLRequests->addBid($inputs);
                if($result) {
                    if(!($auction['Auction_bids_number'] === null)) $auction['Auction_bids_number']++;
                    else $auction['Auction_bids_number'] = 1;
                    $inputs = [ 
                        'auction_date_start'        => $auction['Auction_date_start'],
                        'auction_date_end'          => $auction['Auction_date_end'],
                        'auction_starting_price'    => $auction['Auction_starting_price'],
                        'auction_highest_bid'       => $bidAmount,
                        'auction_bids_number'       => $auction['Auction_bids_number'],
                        'auction_favorite'          => $auction['Auction_favorite'],
                        'auction_id'                => $auction['Auction_id'],
                        'auction_user_id'           => $auction['Auction_user_id']
                    ];
                    $result = self::$oSQLRequests->updateAuction($inputs);
                    if($result) {
                        $this->resultActionMsg = 'Votre mise a bien été prise en compte.';
                        $this->resultClass = 'done';
                    }
                    else {
                        $this->resultActionMsg = 'L\'ajout de la mise n\'a pas fonctionné.';
                        $this->resultClass = 'failed';
                    }
                }
                else {
                    $this->resultActionMsg = 'L\'ajout de la mise n\'a pas fonctionné.';
                    $this->resultClass = 'failed';
                }
            }
            else {
                $this->resultActionMsg = 'L\'ajout de la mise n\'a pas fonctionné. Le montant de la mise doit être supérieur au montant de la mise gagnante.';
                $this->resultClass = 'failed';
            }
        }
        else throw new Exception("Une erreur est survenue lors de la requête au serveur.");
        $this->displayAuction();
    }
}
?>