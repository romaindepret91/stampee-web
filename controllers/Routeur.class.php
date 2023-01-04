<?php

/**
 * Classe Routeur
 * analyse l'uri et exécute la méthode associée  
 *
 */
class Routeur {

  private $routes = [
    ["",                  "Frontend", "manage"],
    ["admin",             "Admin", "manage"]
  ];

  const ERROR_FORBIDDEN = "HTTP 403";
  const ERROR_NOT_FOUND = "HTTP 404";
  
  /**
   * Valide l'URI
   * et instancie la méthode du contrôleur correspondante
   *
   */
  public function router() {
    try {
      $baseUri = substr($_SERVER['PHP_SELF'], 0, -9);
      $uri =  $_SERVER['REQUEST_URI'];
      if (strpos($uri, '?')) $uri = strstr($uri, '?', true);
      foreach ($this->routes as $route) {
        $routeUri     =  $baseUri.$route[0];
        $routeClass  = $route[1];
        $routeMethod = $route[2];
        if ($routeUri ===  $uri) {
          $oRouteClasse = new $routeClass;
          $oRouteClasse->$routeMethod();  
          exit;
        }
      }
      throw new Exception(self::ERROR_NOT_FOUND);
    }
    catch (Error | Exception $e) {
      $this->error($e->getMessage(), $e->getFile(), $e->getLine());
    }
  }

  /**
   * Méthode qui envoie un compte-rendu d'erreur
   *
   */
  public static function error($error, $file, $ligne) {
    $message = '';
    if ($error == self::ERROR_FORBIDDEN) {
      header('HTTP/1.1 403 Forbidden');
    } else if ($error == self::ERROR_NOT_FOUND) {
      header('HTTP/1.1 404 Not Found');
      (new View)->generate('vErreur404', [], 'error-temp');
    } else {
      header('HTTP/1.1 500 Internal Server Error');
      $message = $error;
      (new View)->generate(
        "vError500",
        array('message' => $message, 'file' => $file, 'ligne' => $ligne),
        'error-temp'
      );
    }
    exit;
  }
}