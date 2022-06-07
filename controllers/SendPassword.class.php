<?php

/**
 * Classe de gestion d'envoie de mot de passe
 *
 */
class SendPassword {

  /**
   * Envoyer un courriel à l'utilisateur pour lui communiquer
   * son identifiant de connexion et son mot de passe
   * @param object $oUser utilisateur destinataire
   *
   */
  public function sendPassword(User $oUser) {
    $date     = date("Y-m-d H-i-s");
    $recipient  = $oUser->user_email; 
    $message    = (new View)->generate('password-email',
                                        array(
                                          'title' => 'Information',
                                          'oUser' => $oUser
                                        ),
                                        'admin-temp-min', true);
    $nfile = fopen("mocks/emails/$date-$recipient.html", "w");
    fwrite($nfile, $message);
    fclose($nfile); 
    return true;
  }
}