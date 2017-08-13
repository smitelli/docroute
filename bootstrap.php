<?php

  // Set up constants that define the location of the app's files
  if (!defined('APP_DIR'))     define('APP_DIR',     realpath(dirname(__FILE__)));
  if (!defined('CLASS_DIR'))   define('CLASS_DIR',   APP_DIR . '/classes');
  if (!defined('CONTENT_DIR')) define('CONTENT_DIR', APP_DIR . '/content');

  // Support autoloading classes as they are needed
  function bootstrap_autoload($class_name) {
    $class_file = sprintf(CLASS_DIR . "/{$class_name}.class.php");
    if (is_readable($class_file)) {
      // File exists; load it
      require_once $class_file;
    }
  }
  spl_autoload_register('bootstrap_autoload');

  if (isset($_SERVER['REQUEST_URI'])) {
    // Called with a "pretty" .htaccess-like URL: /app/one/two/three
    $app = new Application($_SERVER['REQUEST_URI']);

  } else if (isset($_SERVER['PATH_INFO'])) {
    // Called with a PATH_INFO URL: /app/index.php/one/two/three
    $app = new Application($_SERVER['PATH_INFO']);

  } else {
    // Called directly...?
    $app = new Application();
  }

?>
