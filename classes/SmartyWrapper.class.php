<?php

  class SmartyWrapper {
    const SMARTY_LIB  = '/lib/smarty/libs/Smarty.class.php';
    private static $instance = NULL;

    public static function getInstance() {
      // Don't have a Smarty instance? Build one.
      if (!self::$instance) {
        self::$instance = self::getNewInstance();
      }

      // Return our Smarty instance
      return self::$instance;
    }

    private static function getNewInstance() {
      // Require the library
      require_once APP_DIR . self::SMARTY_LIB;

      // Build a new Smarty instance, set up universal config options
      $smarty = new Smarty();
      $smarty->compile_dir  = COMPILE_DIR;
      $smarty->template_dir = CONTENT_DIR;

      // Return the instance
      return $smarty;
    }
  }

?>
