<?php
/**
 * Classe qui gère les actions associées à la page catalogue
 * 
 */
class FrontendCatalogue extends Frontend {

    private $methods = [
    'display'           => 'displayCatalogue',
    ];

    /**
     * Gére l'appel de la fonction correspondante à la demande d'action reçue
     */  
    public function manage($entity = "catalogue") {
        $this->entity  = $entity;
        $this->action  = $_GET['action'] ?? 'display';
        if (isset($this->methods[$this->action])) {
            $method = $this->methods[$this->action];
            $this->$method();
        } 
        else throw new Exception("L'action $this->action de l'entité $this->entity n'existe pas.");
    }

    /**
     * Affiche la page de catalogue
     */  
    public function displayCatalogue() {
        $oMemberConnected = null;
        if(isset($_SESSION['memberConnected'])) $oMemberConnected = $_SESSION['memberConnected'];
        $auctions = self::$oSQLRequests->getAllAuctions();
        $today = time();
        $i = 0;
        foreach($auctions as $auction) {
            $startDateAuction = strtotime($auction['Auction_date_start']);
            $endDateAuction = strtotime($auction['Auction_date_end']);
            $auctionStatus = array('Auction_status' => '');
            if($startDateAuction > $today) $auctionStatus['Auction_status'] = 'À venir';
            else if($endDateAuction < $today) $auctionStatus['Auction_status'] = 'Vente clôturée';
            else $auctionStatus['Auction_status'] = 'En cours';
            $auction = array_merge($auction, $auctionStatus);
            $auctions[$i] = $auction;
            $i++;
        };
        $auctionsInProgress = [];
        $auctionsToCome = [];
        $auctionsDone = [];
        foreach($auctions as $auction) {
            switch($auction['Auction_status']){
                case 'En cours':
                    $auctionsInProgress[] = $auction;
                    break;
                case 'À venir':
                    $auctionsToCome[] = $auction;
                    break;
                case 'Vente clôturée':
                    $auctionsDone[] = $auction;
                    break;
            }
        }
        $date = array_column($auctionsInProgress, 'Auction_date_end');
        array_multisort($date, SORT_ASC, $auctionsInProgress);
        $date = array_column($auctionsToCome, 'Auction_date_start');
        array_multisort($date, SORT_ASC, $auctionsToCome);
        $date = array_column($auctionsDone, 'Auction_date_end');
        array_multisort($date, SORT_DESC, $auctionsDone);
        $auctions = array_merge($auctionsInProgress, $auctionsToCome, $auctionsDone);
        (new View)->generate("vCatalogue",
                array(
                    'title'             => "Lord Stampee - Catalogue",
                    'auctions'          => $auctions,
                    'oMemberConnected'  => $oMemberConnected
                ),
                "frontend-temp");
    }
}
?>