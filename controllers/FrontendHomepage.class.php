<?php
/**
 * Classe qui gère les actions associées à la page d'accueil
 * 
 */
class FrontendHomepage extends Frontend {

    private $methods = [
    'display'           => 'displayHomepage',
    ];

     /**
     * Gére l'appel de la fonction correspondante à la demande d'action reçue
     */  
    public function manage($entity = "homepage") {
        $this->entity  = $entity;
        $this->action  = $_GET['action'] ?? 'display';
        if (isset($this->methods[$this->action])) {
            $method = $this->methods[$this->action];
            $this->$method();
        } 
        else throw new Exception("L'action $this->action de l'entité $this->entity n'existe pas.");
    }
    
    /**
     * Affiche la page d'accueil
     */  
    public function displayHomepage() {
        $oMemberConnected = null;
        if(isset($_SESSION['memberConnected'])) $oMemberConnected = $_SESSION['memberConnected'];
        $allAuctions = self::$oSQLRequests->getAllAuctions();
        $today = time();
        $auctions = [];
        foreach($allAuctions as $auction) {
            $startDateAuction = strtotime($auction['Auction_date_start']);
            $endDateAuction = strtotime($auction['Auction_date_end']);
            $auctionStatus = array('Auction_status' => '');
            if($startDateAuction > $today) $auctionStatus['Auction_status'] = 'À venir';
            else if($endDateAuction < $today) $auctionStatus['Auction_status'] = 'Vente clôturée';
            else $auctionStatus['Auction_status'] = 'En cours';
            if($auctionStatus['Auction_status'] === 'En cours') $auctions[] = $auction; 
        };
        $bidsNumber = array_column($auctions, 'Auction_bids_number');
        array_multisort($bidsNumber, SORT_DESC, $auctions);
        $auctionsToDisplay = [];
        for($i = 0; $i <= 7; $i++) $auctionsToDisplay[$i] = $auctions[$i];
        $date = array_column($auctionsToDisplay, 'Auction_date_end');
        array_multisort($date, SORT_ASC, $auctionsToDisplay);
        (new View)->generate("vHomepage",
                array(
                    'title'             => "Lord Stampee - Accueil",
                    'auctions'          => $auctionsToDisplay,
                    'oMemberConnected'  => $oMemberConnected
                ),
                "frontend-temp");
    }
}
?>