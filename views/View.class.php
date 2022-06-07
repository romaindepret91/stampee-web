<?php

class View {


  /**
   * Générer et afficher la page html complète associée à la vue
   * -----------------------------------------------------------
   * @param string $view 
   * @param array $data
   * @param string $template
   * @param boolean $email
   * @return string si $email true, void sinon 
   */
  public function generate($view, $data = array(), $template = "frontend-temp", $email = false) {

    require_once 'views/vendor/autoload.php';
    $loader = new \Twig\Loader\FilesystemLoader('views/templates');
    $twig = new \Twig\Environment($loader, [
      // 'cache' => 'app/vues/templates/cache',
    ]);

    $data ['templateMain'] = "$view.twig";
    $html = $twig->render("$template.twig", $data );
    if ($email) return $html;
    echo $html;
  }
}