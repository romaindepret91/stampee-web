<?php 

function loadClass($class) {
  if (!stristr($class, "mocks")) { 
    $files = array('models/sql/', 'models/entities/', 'views/', 'controllers/'); 
    foreach ($files as $file) {
      if (file_exists('./'.$file.$class.'.class.php')) {
        require_once('./'.$file.$class.'.class.php');
      }
    }
  } else {
    $class = str_replace('\\', '/', $class);
    require_once($class . '.class.php');
  }
}
spl_autoload_register('loadClass');