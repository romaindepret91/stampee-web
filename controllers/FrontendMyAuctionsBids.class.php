<?php
/**
 * Classe qui gère les actions associées à la page des mises d'un membre
 * 
 */
class FrontendMyAuctionsBids extends Frontend {

    private $methods = [
    'display'           => 'displayMyAuctionsBids',
    ];

    /**
     * Gére l'appel de la fonction correspondante à la demande d'action reçue
     */  
    public function manage($entity = "myAuctionsBids") {
        $this->entity  = $entity;
        $this->action  = $_GET['action'] ?? 'display';
        if (isset($this->methods[$this->action])) {
            $method = $this->methods[$this->action];
            $this->$method();
        } 
        else throw new Exception("L'action $this->action de l'entité $this->entity n'existe pas.");
    }

    /**
     * Affiche la page des mises d'un membre
     */ 
    public function displayMyAuctionsBids() {
        $oMemberConnected = null;
        if(isset($_SESSION['memberConnected'])) {
            $oMemberConnected = $_SESSION['memberConnected'];
            $myAuctionsBids = self::$oSQLRequests->getMyAuctionsBids($oMemberConnected->user_id);
        }
        else {
            (new View)->generate("vLogInForm",
            array(
              'title' => "Page de connexion",
            ),
            "frontend-temp");
            exit;
        }
        $myAuctions = [];
        foreach($myAuctionsBids as $myAuctionsBid) {
            $myAuction = self::$oSQLRequests->getSingleAuction($myAuctionsBid['Auction_id']);
            if($myAuction) {
                $myAuction['bid_date'] = $myAuctionsBid['bids_on_date_created'];
                $myAuction['my_highest_bid'] = $myAuctionsBid['bids_on_bid_amount'];
                $myAuction['Auction_status'] = '';
                $myAuctions[] = $myAuction;
            }
        }
        $today = time();
        foreach($myAuctions as $index => $myAuction) {
            $endDateAuction = strtotime($myAuction['Auction_date_end']);
            if($endDateAuction < $today) $myAuctions[$index]['Auction_status'] = 'Vente clôturée';
            else $myAuctions[$index]['Auction_status'] = 'En cours';
        }
        $date = array_column($myAuctions, 'Auction_date_end');
        array_multisort($date, SORT_DESC, $myAuctions);
        (new View)->generate("vMyAuctionsBids",
                array(
                    'title'             => "Mes Mises",
                    'myAuctionsBids'    => $myAuctionsBids,
                    'myAuctions'        => $myAuctions,
                    'message'           => $this->resultActionMsg,
                    'oMemberConnected'  => $oMemberConnected
                ),
                "frontend-temp");
    }
}
?>